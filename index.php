<?php
// Front controller: every request that is not a real file comes here (see .htaccess).
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
require APP_DIR . '/conditions.php';
require APP_DIR . '/pages.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . trim(rawurldecode($path), '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$m = [];
switch (true) {
    case $path === '/':                          redirect('/en/', 301);
    case $path === '/en':                        page_home(); break;
    case $path === '/en/freediving':             page_directory(); break;
    case $path === '/en/compare':                page_compare(); break;
    case $path === '/en/prices':                 page_prices(); break;
    case $path === '/en/today':                  page_today(); break;
    case $path === '/en/spots':                  page_spots(); break;
    case $path === '/en/join':                   $method === 'POST' ? post_join() : page_join(); break;
    case $path === '/en/privacy':                render('privacy', ['title' => 'Privacy & terms', 'desc' => 'How Where To Spot Dive handles your data, and what we are (and are not) responsible for.']); break;
    case (bool)preg_match('#^/en/spots/([a-z0-9-]+)$#', $path, $m):            page_spot($m[1]); break;
    case (bool)preg_match('#^/en/p/([a-z0-9-]+)$#', $path, $m):                page_provider($m[1]); break;
    case (bool)preg_match('#^/en/p/([a-z0-9-]+)/inquire$#', $path, $m):        $method === 'POST' ? post_inquiry($m[1]) : redirect('/en/p/' . $m[1]); break;
    case (bool)preg_match('#^/en/p/([a-z0-9-]+)/kit$#', $path, $m):            page_kit($m[1]); break;
    case (bool)preg_match('#^/go/([a-z]+)/([a-z0-9-]+)$#', $path, $m):         go_click($m[1], $m[2]); break;
    case (bool)preg_match('#^/badge/([a-z0-9-]+)\.svg$#', $path, $m):          badge_svg($m[1]); break;
    case $path === '/widget/today':              widget_today(); break;
    case $path === '/sitemap.xml':               sitemap(); break;
    case $path === '/robots.txt':                header('Content-Type: text/plain'); echo "User-agent: *\nDisallow: /admin\nDisallow: /go/\nDisallow: /me/\nSitemap: " . abs_url('/sitemap.xml') . "\n"; break;
    case $path === '/llms.txt':                  llms_txt(); break;
    case (bool)preg_match('#^/me/([a-f0-9]{32})(/.*)?$#', $path, $m):
        require APP_DIR . '/me.php';
        me_route($m[1], $m[2] ?? '', $method);
        break;
    case $path === '/admin' || str_starts_with($path, '/admin/'):
        require APP_DIR . '/admin.php';
        admin_route(substr($path, 6) ?: '/', $method);
        break;
    default: not_found();
}
