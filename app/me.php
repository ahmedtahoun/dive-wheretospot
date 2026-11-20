<?php
// The provider's private page (/me/{token}): edit profile and courses, photos, weekly status, events, stats.
require_once APP_DIR . '/profile.php';

function me_route(string $token, string $sub, string $method): void {
    header('X-Robots-Tag: noindex, nofollow');
    header('Referrer-Policy: no-referrer');
    header('Cache-Control: no-store');
    $p = provider_by_token($token);
    if (!$p) {
        if (rate_limited('badtoken', 20, 3600)) { http_response_code(429); exit('Too many attempts.'); }
        http_response_code(404);
        render('me_gone', ['title' => 'Link not valid', 'noindex' => true]);
        return;
    }
    $id = (int)$p['id'];
    $base = '/me/' . $token;
    $m = [];
    if ($method === 'POST') csrf_check();

    switch (true) {
        case $sub === '' && $method === 'GET':
            render('me', [
                'title' => 'Your profile · ' . $p['name'], 'noindex' => true, 'bodyClass' => 'is-me',
                'p' => $p, 'base' => $base, 'svc' => services_for($id), 'media' => media_for($id, false),
                'stats' => provider_stats($id),
                'inq' => q('SELECT * FROM inquiries WHERE provider_id = ? ORDER BY id DESC LIMIT 30', [$id])->fetchAll(),
                'events' => q("SELECT * FROM events WHERE provider_id = ? AND COALESCE(NULLIF(ends_on,''), starts_on) >= date('now') ORDER BY starts_on", [$id])->fetchAll(),
                'msg' => (string)($_GET['msg'] ?? ''), 'err' => (string)($_GET['err'] ?? ''),
            ]);
            return;
        case $sub === '/save' && $method === 'POST':
            save_profile($id, $_POST, false);
            redirect($base . '?msg=saved#profile');
        case $sub === '/week' && $method === 'GET':
            render('me_week', ['title' => 'Teaching this week?', 'noindex' => true, 'p' => $p, 'base' => $base, 'done' => $_GET['done'] ?? '']);
            return;
        case $sub === '/week' && $method === 'POST':
            $on = ($_POST['on'] ?? '') === '1' ? 1 : 0;
            q("UPDATE providers SET teaching_week = ?, week_note = ?, week_updated = date('now') WHERE id = ?", [$on, mb_substr(trim((string)($_POST['week_note'] ?? $p['week_note'])), 0, 120), $id]);
            redirect(($_POST['from'] ?? '') === 'dash' ? $base . '?msg=week#week' : $base . '/week?done=' . $on);
        case $sub === '/media' && $method === 'POST':
            // Verified, approved providers publish straight away; new ones wait for a quick check.
            $status = ($p['verified'] && $p['status'] === 'approved') ? 'approved' : 'pending';
            $err = save_upload($id, $_FILES['file'] ?? [], (string)($_POST['spot'] ?? ''), (string)($_POST['caption'] ?? ''), $status);
            redirect($base . ($err ? '?err=' . rawurlencode($err) : '?msg=' . ($status === 'approved' ? 'photo' : 'photo_pending')) . '#media');
        case (bool)preg_match('#^/media/(\d+)/delete$#', $sub, $m) && $method === 'POST':
            $md = q('SELECT * FROM media WHERE id = ? AND provider_id = ?', [(int)$m[1], $id])->fetch();
            if ($md) delete_media($md);
            redirect($base . '?msg=deleted#media');
        case $sub === '/cert' && $method === 'POST':
            $err = save_cert($id, $_FILES['card'] ?? []);
            redirect($base . ($err ? '?err=' . rawurlencode($err) : '?msg=cert') . '#cert');
        case $sub === '/event' && $method === 'POST':
            $start = (string)($_POST['starts_on'] ?? '');
            $title = trim((string)($_POST['title'] ?? ''));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || $title === '') redirect($base . '?err=' . rawurlencode('Add a title and a start date.') . '#events');
            if ((int)q("SELECT COUNT(*) FROM events WHERE provider_id = ? AND starts_on >= date('now')", [$id])->fetchColumn() >= 10) redirect($base . '?err=' . rawurlencode('You can have up to 10 upcoming events.') . '#events');
            $end = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_POST['ends_on'] ?? '')) && $_POST['ends_on'] >= $start ? $_POST['ends_on'] : '';
            q('INSERT INTO events (provider_id, title, kind, starts_on, ends_on, description) VALUES (?,?,?,?,?,?)', [
                $id, mb_substr($title, 0, 100), in_array($_POST['kind'] ?? '', ['camp', 'course', 'workshop', 'competition', 'social', 'trip'], true) ? $_POST['kind'] : 'camp',
                $start, $end, mb_substr(trim((string)($_POST['description'] ?? '')), 0, 300),
            ]);
            redirect($base . '?msg=event#events');
        case (bool)preg_match('#^/event/(\d+)/delete$#', $sub, $m) && $method === 'POST':
            q('DELETE FROM events WHERE id = ? AND provider_id = ?', [(int)$m[1], $id]);
            redirect($base . '?msg=event_deleted#events');
        default:
            not_found();
    }
}
