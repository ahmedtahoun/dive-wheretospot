<?php
// Public page handlers.

function page_home(): void {
    $providers = providers_approved();
    $svc = services_by_provider();
    $c = conditions();
    render('home', [
        'title' => 'Freediving in Dahab: every school, coach and spot',
        'desc'  => 'Compare Dahab freediving schools, freelance instructors and coaches by price, depth, language and spot. Live sea conditions, who is teaching this week, and interactive dive-spot guides.',
        'providers' => $providers, 'svc' => $svc, 'c' => $c,
        'events' => upcoming_events(4),
        'teaching' => array_values(array_filter($providers, fn($p) => (int)$p['teaching_week'] === 1)),
    ]);
}

function page_directory(): void {
    render('directory', [
        'title' => 'Freediving schools & instructors in Dahab: compare all',
        'desc'  => 'Every freediving school, freelance instructor and coach in Dahab in one list. Filter by agency (AIDA, Molchanovs, SSI), language, spot and price, then compare side by side.',
        'providers' => providers_approved(), 'svc' => services_by_provider(),
    ]);
}

function page_compare(): void {
    $slugs = array_slice(array_filter(explode(',', (string)($_GET['p'] ?? '')), fn($s) => preg_match('/^[a-z0-9-]+$/', $s)), 0, 4);
    $list = [];
    foreach ($slugs as $s) {
        $p = q("SELECT * FROM providers WHERE slug = ? AND status = 'approved'", [$s])->fetch();
        if ($p) { $p['_svc'] = services_for((int)$p['id']); $list[] = $p; }
    }
    render('compare', ['title' => 'Compare freediving schools in Dahab', 'desc' => 'Side-by-side comparison of Dahab freediving schools and instructors.', 'list' => $list, 'noindex' => true]);
}

function page_prices(): void {
    $rows = q("SELECT s.*, p.name, p.slug, p.type, p.max_ratio, p.pool, p.gear_included, p.checked_on, p.verified, p.is_sample
               FROM services s JOIN providers p ON p.id = s.provider_id
               WHERE p.status = 'approved' AND s.category IN ('course','coaching') AND s.price_eur > 0
               ORDER BY s.category = 'coaching', s.level, s.price_eur")->fetchAll();
    $byLevel = [];
    foreach ($rows as $r) if ($r['category'] === 'course') $byLevel[(int)$r['level']][] = (float)$r['price_eur'];
    $stats = [];
    foreach ($byLevel as $lvl => $prices) {
        sort($prices);
        $n = count($prices);
        $stats[$lvl] = ['min' => $prices[0], 'max' => $prices[$n - 1], 'median' => $n % 2 ? $prices[intdiv($n, 2)] : ($prices[$n / 2 - 1] + $prices[$n / 2]) / 2, 'n' => $n];
    }
    render('prices', [
        'title' => 'Every freediving course in Dahab compared (' . date('M Y') . ')',
        'desc'  => 'Freediving course prices in Dahab, school by school: AIDA, Molchanovs and SSI, beginner to instructor. Group size, pool, gear and the date each price was checked.',
        'rows' => $rows, 'stats' => $stats,
    ]);
}

function page_today(): void {
    $c = conditions();
    $providers = providers_approved();
    render('today', [
        'title' => 'Today in Dahab: wind, waves and where to freedive',
        'desc'  => 'Live wind, waves and water temperature for Dahab, the calmest freediving spots right now, who is teaching this week and upcoming camps and workshops.',
        'c' => $c,
        'teaching' => array_values(array_filter($providers, fn($p) => (int)$p['teaching_week'] === 1)),
        'events' => upcoming_events(12),
    ]);
}

function page_spots(): void {
    render('spots', [
        'title' => 'Dahab dive and freediving spots: map and guide',
        'desc'  => 'Every main Dahab spot on one map: Blue Hole, Lighthouse, Canyon, Eel Garden and more, with depth, level, entry and who teaches there.',
        'c' => conditions(),
    ]);
}

function page_spot(string $slug): void {
    $s = spot($slug);
    if (!$s) not_found();
    $here = [];
    $svc = services_by_provider();
    foreach (providers_approved() as $p) {
        if (in_array($slug, csv_list($p['spots']), true)) { $p['_svc'] = $svc[(int)$p['id']] ?? []; $here[] = $p; }
    }
    $photos = q("SELECT m.*, p.name, p.slug FROM media m JOIN providers p ON p.id = m.provider_id
                 WHERE m.spot = ? AND m.status = 'approved' AND p.status = 'approved' ORDER BY m.id DESC LIMIT 12", [$slug])->fetchAll();
    render('spot', [
        'title' => $s['name'] . ' Dahab: ' . ($s['freedive'] ? 'freediving' : 'diving') . ' depth, entry and who teaches there',
        'desc'  => $s['summary'] . ' Depth ' . $s['depth'] . '. ' . $s['level'] . '.',
        'slug' => $slug, 's' => $s, 'here' => $here, 'photos' => $photos, 'c' => conditions(),
    ]);
}

function page_provider(string $slug): void {
    $p = q('SELECT * FROM providers WHERE slug = ?', [$slug])->fetch();
    if (!$p || ($p['status'] !== 'approved' && !admin_logged_in())) not_found();
    if ($p['status'] === 'approved' && !admin_logged_in() && !preg_match('/bot|crawl|spider|preview|facebookexternalhit|whatsapp/i', $_SERVER['HTTP_USER_AGENT'] ?? '')) {
        q("INSERT INTO clicks (provider_id, kind) VALUES (?, 'view')", [$p['id']]);
    }
    render('provider', [
        'title' => $p['name'] . ' · ' . (PROVIDER_TYPES[$p['type']] ?? 'Freediving') . ' in Dahab',
        'desc'  => ($p['tagline'] ?: $p['name'] . ' in Dahab') . ' Courses, prices, languages and contact.',
        'p' => $p, 'svc' => services_for((int)$p['id']), 'media' => media_for((int)$p['id']),
        'events' => q('SELECT * FROM events WHERE provider_id = ? AND COALESCE(NULLIF(ends_on,\'\'), starts_on) >= date(\'now\') ORDER BY starts_on', [$p['id']])->fetchAll(),
        'sent' => $_GET['sent'] ?? null,
        'noindex' => $p['status'] !== 'approved',
    ]);
}

function admin_logged_in(): bool {
    if (empty($_COOKIE['dive_sess'])) return false; // don't start a session for every visitor
    start_session();
    return !empty($_SESSION['admin']);
}

function post_inquiry(string $slug): void {
    csrf_check();
    $p = q("SELECT * FROM providers WHERE slug = ? AND status = 'approved'", [$slug])->fetch();
    if (!$p) not_found();
    if (!empty($_POST['website_url'])) redirect('/en/p/' . $slug); // honeypot
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $wa = trim((string)($_POST['whatsapp'] ?? ''));
    if ($name === '' || ($email === '' && $wa === '') || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
        redirect('/en/p/' . $slug . '?err=1#inquire');
    }
    if (rate_limited('inq', 6, 3600)) { http_response_code(429); exit('Too many requests. Please try again in an hour, or message the school on WhatsApp.'); }
    $ref = 'D' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    q('INSERT INTO inquiries (ref, provider_id, name, email, whatsapp, dates, level, people, message) VALUES (?,?,?,?,?,?,?,?,?)', [
        $ref, $p['id'], mb_substr($name, 0, 80), mb_substr($email, 0, 120), mb_substr($wa, 0, 30),
        mb_substr(trim((string)($_POST['dates'] ?? '')), 0, 80), mb_substr(trim((string)($_POST['level'] ?? '')), 0, 60),
        max(1, min(20, (int)($_POST['people'] ?? 1))), mb_substr(trim((string)($_POST['message'] ?? '')), 0, 2000),
    ]);
    notify_inquiry($p, $ref, $name, $email, $wa);
    redirect('/en/p/' . $slug . '?sent=' . $ref . '#inquire');
}

function notify_inquiry(array $p, string $ref, string $name, string $email, string $wa): void {
    $to = array_filter([cfg('admin_email'), $p['email']]);
    if (!$to || is_local()) return;
    $body = "New inquiry $ref for {$p['name']} on dive.wheretospot.com\n\nName: $name\nEmail: $email\nWhatsApp: $wa\n\nDetails: " . abs_url('/admin/inquiries') . "\n";
    @mail(implode(',', $to), "New inquiry $ref · {$p['name']}", $body, 'From: ' . cfg('mail_from'));
}

function page_kit(string $slug): void {
    $p = q("SELECT * FROM providers WHERE slug = ? AND status = 'approved'", [$slug])->fetch();
    if (!$p) not_found();
    render('kit', [
        'title' => 'Share kit · ' . $p['name'], 'desc' => 'Badge, live conditions widget and Instagram story card for ' . $p['name'] . '.',
        'p' => $p, 'svc' => services_for((int)$p['id']), 'noindex' => true,
    ]);
}

function page_join(array $errors = [], array $old = []): void {
    render('join', [
        'title' => 'List your freediving school or coaching in Dahab (free)',
        'desc'  => 'Freediving schools, freelance instructors and coaches in Dahab: get listed for free. Inquiries go straight to your WhatsApp. No commission.',
        'errors' => $errors, 'old' => $old,
    ]);
}

function post_join(): void {
    csrf_check();
    if (!empty($_POST['website_url'])) redirect('/en/join');
    $f = fn($k, $len = 200) => mb_substr(trim((string)($_POST[$k] ?? '')), 0, $len);
    $old = $_POST;
    $errors = [];
    $name = $f('name', 80);
    $wa = $f('whatsapp', 30);
    $type = array_key_exists($_POST['type'] ?? '', PROVIDER_TYPES) ? $_POST['type'] : 'school';
    if ($name === '') $errors['name'] = 'Please add your name or your school\'s name.';
    if (strlen(preg_replace('/\D/', '', $wa)) < 8) $errors['whatsapp'] = 'Please add a WhatsApp number with the country code, e.g. +20 100 000 0000.';
    if (!array_key_exists($_POST['licence'] ?? '', LICENCE)) $errors['licence'] = 'Please choose your status.';
    if (empty($_POST['consent'])) $errors['consent'] = 'Please confirm you agree to be listed.';
    if ($errors) { page_join($errors, $old); return; }
    if (rate_limited('join', 5, 3600)) { http_response_code(429); exit('Too many requests. Please try again later.'); }

    $slug = slugify($name);
    if (q('SELECT 1 FROM providers WHERE slug = ?', [$slug])->fetchColumn()) $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 3);
    $email = filter_var($f('email', 120), FILTER_VALIDATE_EMAIL) ?: '';
    $token = new_token();
    q("INSERT INTO providers (slug, name, type, licence, whatsapp, email, agencies, edit_token, status) VALUES (?,?,?,?,?,?,?,?,'pending')", [
        $slug, $name, $type, $_POST['licence'], $wa, $email,
        implode(',', array_values(array_intersect((array)($_POST['agencies'] ?? []), AGENCIES))), $token,
    ]);
    $link = abs_url('/me/' . $token);
    $emailed = false;
    if ($email && !is_local()) {
        $emailed = @mail($email, 'Your Where To Spot Dive page', "Hi $name,\n\nThis is your private page to edit your profile, add courses, photos and set your weekly status. Keep it to yourself:\n$link\n\nWhere To Spot Dive", 'From: ' . cfg('mail_from'));
    }
    if (cfg('admin_email') && !is_local()) @mail(cfg('admin_email'), 'New listing request: ' . $name, "Review it: " . abs_url('/admin/'), 'From: ' . cfg('mail_from'));
    render('joined', ['title' => 'Your private page', 'name' => $name, 'link' => $link, 'whatsapp' => $wa, 'emailed' => $emailed, 'noindex' => true]);
}

function go_click(string $kind, string $slug): void {
    $p = q("SELECT * FROM providers WHERE slug = ? AND status = 'approved'", [$slug])->fetch();
    if (!$p) not_found();
    $target = '';
    if ($kind === 'whatsapp' && $p['whatsapp']) {
        $target = wa_link($p['whatsapp'], "Hi {$p['name']}, I found you on dive.wheretospot.com and I'd like to ask about freediving in Dahab.");
    } elseif (isset(SOCIALS[$kind]) && preg_match('#^https?://#', (string)$p[$kind])) {
        $target = $p[$kind];
    }
    if ($target === '') not_found();
    $bot = preg_match('/bot|crawl|spider|preview/i', $_SERVER['HTTP_USER_AGENT'] ?? '');
    if (!$bot) q('INSERT INTO clicks (provider_id, kind, page) VALUES (?,?,?)', [$p['id'], $kind, mb_substr((string)($_SERVER['HTTP_REFERER'] ?? ''), 0, 200)]);
    header('X-Robots-Tag: noindex');
    redirect($target);
}

function badge_svg(string $slug): void {
    $p = q("SELECT name, verified FROM providers WHERE slug = ? AND status = 'approved'", [$slug])->fetch();
    header('Content-Type: image/svg+xml');
    header('Cache-Control: public, max-age=3600');
    $line2 = $p ? ($p['verified'] ? 'Verified on Where To Spot' : 'Listed on Where To Spot') : 'Where To Spot';
    $name = $p ? h(mb_strimwidth($p['name'], 0, 28, '…')) : 'Dive Dahab';
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="240" height="64" viewBox="0 0 240 64" role="img" aria-label="' . $name . ' – ' . h($line2) . '">'
       . '<defs><linearGradient id="g" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#0e7490"/><stop offset="1" stop-color="#0b2545"/></linearGradient></defs>'
       . '<rect width="240" height="64" rx="12" fill="url(#g)"/>'
       . '<g transform="translate(14 14)" fill="none" stroke="#a5f3fc" stroke-width="2.4" stroke-linecap="round"><path d="M18 0v36"/><circle cx="18" cy="24" r="5" fill="#a5f3fc" stroke="none"/><path d="M4 8c4-3 8-3 12 0s8 3 12 0"/></g>'
       . '<text x="56" y="28" font-family="Inter,Arial,sans-serif" font-size="14" font-weight="700" fill="#fff">' . $name . '</text>'
       . '<text x="56" y="46" font-family="Inter,Arial,sans-serif" font-size="11" fill="#a5f3fc">' . ($p && $p['verified'] ? '✓ ' : '') . h($line2) . '</text></svg>';
}

function widget_today(): void {
    $c = conditions();
    $slug = preg_match('/^[a-z0-9-]+$/', (string)($_GET['p'] ?? '')) ? $_GET['p'] : '';
    $p = $slug ? q("SELECT name, slug FROM providers WHERE slug = ? AND status = 'approved'", [$slug])->fetch() : null;
    header('Content-Security-Policy: frame-ancestors *');
    require APP_DIR . '/views/widget.php';
}

function upcoming_events(int $limit): array {
    return q("SELECT e.*, p.name AS pname, p.slug AS pslug FROM events e LEFT JOIN providers p ON p.id = e.provider_id
              WHERE COALESCE(NULLIF(e.ends_on,''), e.starts_on) >= date('now') AND (p.id IS NULL OR p.status = 'approved')
              ORDER BY e.starts_on LIMIT " . (int)$limit)->fetchAll();
}

function sitemap(): void {
    header('Content-Type: application/xml; charset=utf-8');
    $urls = ['/en/', '/en/freediving', '/en/prices', '/en/today', '/en/spots', '/en/join'];
    foreach (array_keys(spots()) as $s) $urls[] = '/en/spots/' . $s;
    foreach (q("SELECT slug FROM providers WHERE status = 'approved' AND is_sample = 0")->fetchAll() as $p) $urls[] = '/en/p/' . $p['slug'];
    echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $u) echo '<url><loc>' . h(abs_url($u)) . '</loc></url>';
    echo '</urlset>';
}

function llms_txt(): void {
    header('Content-Type: text/plain; charset=utf-8');
    echo "# Where To Spot · Dive (dive.wheretospot.com)\n\n";
    echo "> An independent guide to freediving in Dahab, Egypt: every school, freelance instructor and coach, compared by price, depth, language and spot, plus live sea conditions.\n\n";
    echo "## Key pages\n";
    echo "- [All schools and instructors](" . abs_url('/en/freediving') . ")\n- [Course prices compared](" . abs_url('/en/prices') . ")\n- [Today in Dahab: conditions](" . abs_url('/en/today') . ")\n- [Spots map](" . abs_url('/en/spots') . ")\n";
    foreach (spots() as $slug => $s) echo "- [{$s['name']}](" . abs_url('/en/spots/' . $slug) . "): {$s['summary']}\n";
}
