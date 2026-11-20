<?php
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
        (SELECT COUNT(*) FROM clicks c WHERE c.provider_id = p.id AND c.created_at > datetime(\'now\',\'-30 days\')) AS clicks30,
        (SELECT COUNT(*) FROM clicks c WHERE c.provider_id = p.id AND c.kind = \'whatsapp\' AND c.created_at > datetime(\'now\',\'-30 days\')) AS wa30,
        (SELECT COUNT(*) FROM inquiries i WHERE i.provider_id = p.id) AS inq,
        (SELECT COUNT(*) FROM media m WHERE m.provider_id = p.id AND m.status = \'pending\') AS media_pending
        FROM providers p' . ($status ? ' WHERE p.status = ?' : '') . " ORDER BY p.status = 'pending' DESC, p.name", $status ? [$status] : [])->fetchAll();
    $counts = [];
    foreach (q('SELECT status, COUNT(*) n FROM providers GROUP BY status')->fetchAll() as $r) $counts[$r['status']] = (int)$r['n'];
    admin_view('dashboard', [
        'providers' => $providers, 'counts' => $counts, 'status' => $status,
        'newInq' => (int)q("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn(),
        'clicks30' => (int)q("SELECT COUNT(*) FROM clicks WHERE created_at > datetime('now','-30 days')")->fetchColumn(),
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
    $p = q('SELECT * FROM providers WHERE id = ?', [$id])->fetch();
    if (!$p) not_found();
    $f = fn($k, $len = 200) => mb_substr(trim((string)($_POST[$k] ?? '')), 0, $len);
    $pick = fn($k, $allowed) => implode(',', array_values(array_intersect((array)($_POST[$k] ?? []), $allowed)));
    $slug = slugify($f('slug', 80) ?: $f('name', 80));
    if (q('SELECT 1 FROM providers WHERE slug = ? AND id <> ?', [$slug, $id])->fetchColumn()) $slug = $p['slug'];
    $url = fn($k) => ($v = $f($k)) !== '' && preg_match('#^https?://#', $v) && filter_var($v, FILTER_VALIDATE_URL) ? $v : '';
    $week = !empty($_POST['teaching_week']) ? 1 : 0;
    q('UPDATE providers SET slug=?, name=?, type=?, tagline=?, bio=?, languages=?, agencies=?, licence=?, works_with=?, whatsapp=?, email=?,
        website=?, instagram=?, tiktok=?, youtube=?, facebook=?, base_area=?, spots=?, pool=?, max_ratio=?, gear_included=?, years_teaching=?,
        students_certified=?, personal_best=?, achievements=?, teaching_week=?, week_note=?, week_updated=CASE WHEN ? = 1 AND teaching_week = 0 THEN date(\'now\') ELSE week_updated END,
        verified_note=?, checked_on=?, updated_at=datetime(\'now\') WHERE id=?', [
        $slug, $f('name', 80) ?: $p['name'], array_key_exists($_POST['type'] ?? '', PROVIDER_TYPES) ? $_POST['type'] : 'school',
        $f('tagline', 140), $f('bio', 4000), $pick('languages', LANGS), $pick('agencies', AGENCIES),
        array_key_exists($_POST['licence'] ?? '', LICENCE) ? $_POST['licence'] : 'freelance', $f('works_with', 80), $f('whatsapp', 30),
        filter_var($f('email', 120), FILTER_VALIDATE_EMAIL) ?: '', $url('website'), $url('instagram'), $url('tiktok'), $url('youtube'), $url('facebook'),
        $f('base_area', 60), $pick('spots', array_keys(spots())), !empty($_POST['pool']) ? 1 : 0, max(0, min(20, (int)($_POST['max_ratio'] ?? 0))),
        !empty($_POST['gear_included']) ? 1 : 0, max(0, (int)($_POST['years_teaching'] ?? 0)), max(0, (int)($_POST['students_certified'] ?? 0)),
        $f('personal_best', 60), $f('achievements', 2000), $week, $f('week_note', 120), $week, $f('verified_note', 200),
        preg_match('/^\d{4}-\d{2}-\d{2}$/', $f('checked_on', 10)) ? $f('checked_on', 10) : '', $id,
    ]);
    // Services: rows of fields; empty title = skip; "del" ticked = remove.
    q('DELETE FROM services WHERE provider_id = ?', [$id]);
    foreach ((array)($_POST['svc'] ?? []) as $s) {
        if (!is_array($s) || !empty($s['del']) || trim((string)($s['title'] ?? '')) === '') continue;
        q('INSERT INTO services (provider_id, category, level, agency, title, days, price_eur, notes) VALUES (?,?,?,?,?,?,?,?)', [
            $id, array_key_exists($s['category'] ?? '', SERVICE_CATS) ? $s['category'] : 'course', max(0, min(4, (int)($s['level'] ?? 1))),
            in_array($s['agency'] ?? '', AGENCIES, true) ? $s['agency'] : '', mb_substr(trim((string)$s['title']), 0, 100),
            max(0, (float)($s['days'] ?? 0)), max(0, (float)($s['price_eur'] ?? 0)), mb_substr(trim((string)($s['notes'] ?? '')), 0, 120),
        ]);
    }
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
    $f = $_FILES['file'] ?? null;
    if (!$f || $f['error'] !== UPLOAD_ERR_OK) { admin_edit($id, 'Upload failed. Check the file size (photos up to 12 MB, videos up to 40 MB).'); return; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $dir = cfg('uploads_dir');
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $base = 'p' . $id . '-' . bin2hex(random_bytes(6));
    if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) && $f['size'] <= 12 * 1024 * 1024) {
        $img = @imagecreatefromstring((string)file_get_contents($f['tmp_name']));
        if (!$img) { admin_edit($id, 'That image could not be read.'); return; }
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $o = @exif_read_data($f['tmp_name'])['Orientation'] ?? 1;
            if ($o == 3) $img = imagerotate($img, 180, 0); elseif ($o == 6) $img = imagerotate($img, -90, 0); elseif ($o == 8) $img = imagerotate($img, 90, 0);
        }
        $w = imagesx($img); $hgt = imagesy($img); $max = 1600;
        if ($w > $max || $hgt > $max) {
            $r = min($max / $w, $max / $hgt);
            $nw = (int)round($w * $r); $nh = (int)round($hgt * $r);
            $dst = imagecreatetruecolor($nw, $nh);
            imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $hgt);
            $img = $dst;
        }
        $name = $base . (function_exists('imagewebp') ? '.webp' : '.jpg');
        function_exists('imagewebp') ? imagewebp($img, "$dir/$name", 82) : imagejpeg($img, "$dir/$name", 84);
        $kind = 'photo';
    } elseif ($mime === 'video/mp4' && $f['size'] <= 40 * 1024 * 1024) {
        $name = $base . '.mp4';
        move_uploaded_file($f['tmp_name'], "$dir/$name");
        $kind = 'video';
    } else {
        admin_edit($id, 'Only JPG, PNG or WebP photos (up to 12 MB) and MP4 videos (up to 40 MB).');
        return;
    }
    $spot = array_key_exists($_POST['spot'] ?? '', spots()) ? $_POST['spot'] : '';
    $status = !empty($_POST['approve_now']) ? 'approved' : 'pending';
    q('INSERT INTO media (provider_id, file, kind, caption, spot, status) VALUES (?,?,?,?,?,?)', [$id, $name, $kind, mb_substr(trim((string)($_POST['caption'] ?? '')), 0, 140), $spot, $status]);
    redirect('/admin/p/' . $id . '?msg=Uploaded#media');
}

function admin_media(int $mid): void {
    $m = q('SELECT * FROM media WHERE id = ?', [$mid])->fetch();
    if (!$m) not_found();
    $a = $_POST['a'] ?? '';
    if ($a === 'approve') q("UPDATE media SET status = 'approved' WHERE id = ?", [$mid]);
    if ($a === 'hide') q("UPDATE media SET status = 'pending' WHERE id = ?", [$mid]);
    if ($a === 'delete') {
        q('DELETE FROM media WHERE id = ?', [$mid]);
        $file = cfg('uploads_dir') . '/' . basename($m['file']);
        if (is_file($file)) unlink($file);
    }
    redirect('/admin/p/' . $m['provider_id'] . '#media');
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
