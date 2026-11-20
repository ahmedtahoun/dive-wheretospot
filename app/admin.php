<?php
require_once APP_DIR . '/profile.php';

// Admin: approve providers, edit profiles, verify certificates, photos, inquiries, events.

function admin_password_ok(string $pw): bool {
    $hash = (string)cfg('admin_password_hash');
    if ($hash !== '') return password_verify($pw, $hash);
    return is_local() && hash_equals('dive-local', $pw); // local testing only
}

function admin_route(string $sub, string $method): void {
    start_session();
    header('X-Robots-Tag: noindex');
    header('Cache-Control: no-store');
    $sub = '/' . trim($sub, '/');

    if ($sub === '/login' && $method === 'POST') {
        csrf_check();
        if (rate_limited('login', 8, 900)) { http_response_code(429); exit('Too many attempts. Wait 15 minutes.'); }
        if (admin_password_ok((string)($_POST['password'] ?? ''))) {
            session_regenerate_id(true);
            $_SESSION['admin'] = 1;
            redirect('/admin/');
        }
        admin_view('login', ['error' => 'Wrong password.']);
        return;
    }
    if ($sub === '/logout') { $_SESSION = []; session_destroy(); redirect('/admin/'); }
    if (empty($_SESSION['admin'])) { admin_view('login', []); return; }

    $m = [];
    if ($method === 'POST') csrf_check();
    switch (true) {
        case $sub === '/':                admin_dashboard(); break;
        case $sub === '/inquiries':       admin_view('inquiries', ['rows' => q('SELECT i.*, p.name AS pname, p.slug FROM inquiries i LEFT JOIN providers p ON p.id = i.provider_id ORDER BY i.id DESC LIMIT 300')->fetchAll()]); break;
        case $sub === '/events' && $method === 'POST': admin_event_add(); break;
        case $sub === '/events':          admin_view('events', ['rows' => q('SELECT e.*, p.name AS pname FROM events e LEFT JOIN providers p ON p.id = e.provider_id ORDER BY e.starts_on DESC')->fetchAll(), 'providers' => q("SELECT id, name FROM providers WHERE status='approved' ORDER BY name")->fetchAll()]); break;
        case (bool)preg_match('#^/events/(\d+)/delete$#', $sub, $m) && $method === 'POST': q('DELETE FROM events WHERE id = ?', [(int)$m[1]]); redirect('/admin/events'); break;
        case $sub === '/samples/delete' && $method === 'POST':
            q('DELETE FROM events WHERE is_sample = 1'); q('DELETE FROM providers WHERE is_sample = 1'); redirect('/admin/?msg=Sample+data+removed'); break;
        case $sub === '/new' && $method === 'POST':
            $name = trim((string)($_POST['name'] ?? '')) ?: 'New provider';
            $slug = slugify($name); if (q('SELECT 1 FROM providers WHERE slug=?', [$slug])->fetchColumn()) $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 3);
            q("INSERT INTO providers (slug, name, status) VALUES (?, ?, 'pending')", [$slug, $name]);
            redirect('/admin/p/' . db()->lastInsertId());
        case (bool)preg_match('#^/p/(\d+)$#', $sub, $m):
            $method === 'POST' ? admin_save((int)$m[1]) : admin_edit((int)$m[1]); break;
        case (bool)preg_match('#^/p/(\d+)/action$#', $sub, $m) && $method === 'POST': admin_action((int)$m[1]); break;
        case (bool)preg_match('#^/p/(\d+)/media$#', $sub, $m) && $method === 'POST': admin_upload((int)$m[1]); break;
        case (bool)preg_match('#^/media/(\d+)$#', $sub, $m) && $method === 'POST': admin_media((int)$m[1]); break;
        case (bool)preg_match('#^/p/(\d+)/token$#', $sub, $m) && $method === 'POST':
            q('UPDATE providers SET edit_token = ? WHERE id = ?', [new_token(), (int)$m[1]]); redirect('/admin/p/' . (int)$m[1] . '?msg=New+private+link+made.+The+old+one+no+longer+works.#link'); break;
        case (bool)preg_match('#^/cert/(\d+)$#', $sub, $m): admin_cert((int)$m[1]); break;
        case $sub === '/weekly': admin_view('weekly', ['rows' => q("SELECT * FROM providers WHERE status = 'approved' AND whatsapp <> '' ORDER BY teaching_week, name")->fetchAll()]); break;
        case $sub === '/photos': admin_view('photos', ['rows' => q("SELECT m.*, p.name, p.slug FROM media m JOIN providers p ON p.id = m.provider_id WHERE m.status = 'pending' ORDER BY m.id")->fetchAll()]); break;
        case (bool)preg_match('#^/inquiries/(\d+)$#', $sub, $m) && $method === 'POST':
            q('UPDATE inquiries SET status = ? WHERE id = ?', [in_array($_POST['status'] ?? '', ['new', 'sent', 'booked', 'lost'], true) ? $_POST['status'] : 'new', (int)$m[1]]);
            redirect('/admin/inquiries'); break;
        default: not_found();
    }
}

function admin_view(string $view, array $vars): void {
    $vars += ['title' => 'Admin', 'noindex' => true, 'bodyClass' => 'is-admin'];
    render('admin_' . $view, $vars);
}

function admin_dashboard(): void {
    $status = in_array($_GET['status'] ?? '', ['pending', 'approved', 'rejected'], true) ? $_GET['status'] : '';
    $providers = q('SELECT p.*,
        (SELECT COUNT(*) FROM clicks c WHERE c.provider_id = p.id AND c.kind <> \'view\' AND c.created_at > datetime(\'now\',\'-30 days\')) AS clicks30,
        (SELECT COUNT(*) FROM clicks c WHERE c.provider_id = p.id AND c.kind = \'view\' AND c.created_at > datetime(\'now\',\'-30 days\')) AS views30,
        (SELECT COUNT(*) FROM clicks c WHERE c.provider_id = p.id AND c.kind = \'whatsapp\' AND c.created_at > datetime(\'now\',\'-30 days\')) AS wa30,
        (SELECT COUNT(*) FROM inquiries i WHERE i.provider_id = p.id) AS inq,
        (SELECT COUNT(*) FROM media m WHERE m.provider_id = p.id AND m.status = \'pending\') AS media_pending
        FROM providers p' . ($status ? ' WHERE p.status = ?' : '') . " ORDER BY p.status = 'pending' DESC, p.name", $status ? [$status] : [])->fetchAll();
    $counts = [];
    foreach (q('SELECT status, COUNT(*) n FROM providers GROUP BY status')->fetchAll() as $r) $counts[$r['status']] = (int)$r['n'];
    admin_view('dashboard', [
        'providers' => $providers, 'counts' => $counts, 'status' => $status,
        'newInq' => (int)q("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn(),
        'clicks30' => (int)q("SELECT COUNT(*) FROM clicks WHERE kind <> 'view' AND created_at > datetime('now','-30 days')")->fetchColumn(),
        'photosPending' => (int)q("SELECT COUNT(*) FROM media WHERE status = 'pending'")->fetchColumn(),
        'samples' => (int)q('SELECT COUNT(*) FROM providers WHERE is_sample = 1')->fetchColumn(),
        'msg' => $_GET['msg'] ?? '',
    ]);
}

function admin_edit(int $id, string $msg = ''): void {
    $p = q('SELECT * FROM providers WHERE id = ?', [$id])->fetch();
    if (!$p) not_found();
    admin_view('edit', ['title' => 'Edit ' . $p['name'], 'p' => $p, 'svc' => services_for($id), 'media' => media_for($id, false), 'msg' => $msg ?: ($_GET['msg'] ?? '')]);
}

function admin_save(int $id): void {
    if (!q('SELECT 1 FROM providers WHERE id = ?', [$id])->fetchColumn()) not_found();
    save_profile($id, $_POST, true);
    redirect('/admin/p/' . $id . '?msg=Saved');
}

function admin_action(int $id): void {
    $a = (string)($_POST['a'] ?? '');
    $map = [
        'approve' => "status = 'approved', checked_on = CASE WHEN checked_on = '' THEN date('now') ELSE checked_on END",
        'reject' => "status = 'rejected'", 'pending' => "status = 'pending'",
        'verify' => 'verified = 1', 'unverify' => 'verified = 0',
        'feature' => 'featured = 1 - featured', 'founding' => 'founding = 1 - founding',
        'week' => "teaching_week = 1 - teaching_week, week_updated = date('now')",
    ];
    if ($a === 'delete') { q('DELETE FROM providers WHERE id = ?', [$id]); redirect('/admin/?msg=Deleted'); }
    if (isset($map[$a])) q('UPDATE providers SET ' . $map[$a] . ", updated_at = datetime('now') WHERE id = ?", [$id]);
    $back = (string)($_POST['back'] ?? '');
    redirect(str_starts_with($back, '/admin') ? $back : '/admin/');
}

function admin_upload(int $id): void {
    $err = save_upload($id, $_FILES['file'] ?? [], (string)($_POST['spot'] ?? ''), (string)($_POST['caption'] ?? ''), !empty($_POST['approve_now']) ? 'approved' : 'pending');
    if ($err) { admin_edit($id, $err); return; }
    redirect('/admin/p/' . $id . '?msg=Uploaded#media');
}

function admin_media(int $mid): void {
    $m = q('SELECT * FROM media WHERE id = ?', [$mid])->fetch();
    if (!$m) not_found();
    $a = $_POST['a'] ?? '';
    if ($a === 'approve') q("UPDATE media SET status = 'approved' WHERE id = ?", [$mid]);
    if ($a === 'hide') q("UPDATE media SET status = 'pending' WHERE id = ?", [$mid]);
    if ($a === 'delete') delete_media($m);
    $back = (string)($_POST['back'] ?? '');
    redirect(str_starts_with($back, '/admin') ? $back : '/admin/p/' . $m['provider_id'] . '#media');
}

function admin_cert(int $id): void {
    $f = (string)q('SELECT cert_file FROM providers WHERE id = ?', [$id])->fetchColumn();
    $path = cfg('data_dir') . '/certs/' . basename($f);
    if ($f === '' || !is_file($path)) not_found();
    header('Content-Type: ' . (str_ends_with($path, '.webp') ? 'image/webp' : 'image/jpeg'));
    header('Cache-Control: private, no-store');
    readfile($path);
}

function admin_event_add(): void {
    $start = (string)($_POST['starts_on'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || trim((string)($_POST['title'] ?? '')) === '') redirect('/admin/events?err=1');
    $end = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_POST['ends_on'] ?? '')) ? $_POST['ends_on'] : '';
    $pid = (int)($_POST['provider_id'] ?? 0) ?: null;
    q('INSERT INTO events (provider_id, title, kind, starts_on, ends_on, description, link) VALUES (?,?,?,?,?,?,?)', [
        $pid, mb_substr(trim((string)$_POST['title']), 0, 100), in_array($_POST['kind'] ?? '', ['camp', 'course', 'workshop', 'competition', 'social', 'trip'], true) ? $_POST['kind'] : 'camp',
        $start, $end, mb_substr(trim((string)($_POST['description'] ?? '')), 0, 300), '',
    ]);
    redirect('/admin/events');
}
