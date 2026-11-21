<?php
// Shared setup: settings, database, helpers. Every page starts here (via index.php).
declare(strict_types=1);

define('APP_DIR', __DIR__);
define('ROOT_DIR', dirname(__DIR__));

// ---------- settings ----------
// Secrets live only in config.local.php on the server (never in git). See config.local.example.php.
function cfg(string $key, $default = null) {
    static $c = null;
    if ($c === null) {
        $c = [
            'site_name'      => 'Where To Spot · Dive',
            'base_url'       => 'https://dive.wheretospot.com',
            'data_dir'       => getenv('DIVE_DATA_DIR') ?: dirname(ROOT_DIR) . '/dive-data',
            'uploads_dir'    => ROOT_DIR . '/uploads',
            'uploads_url'    => '/uploads',
            'admin_password_hash' => '',
            'admin_email'    => '',
            'mail_from'      => 'no-reply@wheretospot.com',
            'whatsapp_admin' => '',
            'lat'            => 28.4936,
            'lon'            => 34.5133,
        ];
        $local = ROOT_DIR . '/config.local.php';
        if (is_file($local)) {
            $over = require $local;
            if (is_array($over)) $c = array_merge($c, $over);
        }
        if (getenv('DIVE_DATA_DIR')) $c['data_dir'] = getenv('DIVE_DATA_DIR');
        if (getenv('DIVE_UPLOADS_DIR')) $c['uploads_dir'] = getenv('DIVE_UPLOADS_DIR');
    }
    return $c[$key] ?? $default;
}

function is_local(): bool {
    $h = $_SERVER['HTTP_HOST'] ?? '';
    return PHP_SAPI === 'cli-server' || preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $h) === 1;
}

// ---------- database ----------
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $dir = cfg('data_dir');
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $file = $dir . '/dive.sqlite';
    $fresh = !is_file($file);
    $pdo = new PDO('sqlite:' . $file, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL; PRAGMA busy_timeout = 4000;');
    $pdo->exec(file_get_contents(APP_DIR . '/schema.sql'));
    migrate($pdo);
    if ($fresh) {
        require_once APP_DIR . '/seed.php';
        seed_sample_data($pdo);
    }
    // Backfill private edit links, and switch "teaching this week" off after 7 days without a tap.
    foreach ($pdo->query("SELECT id FROM providers WHERE edit_token = ''")->fetchAll() as $r) {
        $pdo->prepare('UPDATE providers SET edit_token = ? WHERE id = ?')->execute([new_token(), $r['id']]);
    }
    $pdo->exec("UPDATE providers SET teaching_week = 0 WHERE teaching_week = 1 AND week_updated < date('now', '-7 days')");
    return $pdo;
}

// Adds columns that older databases don't have yet. Safe to run on every request.
function migrate(PDO $pdo): void {
    $cols = array_column($pdo->query('PRAGMA table_info(providers)')->fetchAll(), 'name');
    $add = [
        'edit_token' => "TEXT DEFAULT ''",
        'cert_agency' => "TEXT DEFAULT ''",
        'cert_number' => "TEXT DEFAULT ''",
        'cert_file' => "TEXT DEFAULT ''",
        'work_links' => "TEXT DEFAULT ''",
        'specialties' => "TEXT DEFAULT ''",
        'styles' => "TEXT DEFAULT ''",
        'formats' => "TEXT DEFAULT ''",
        'months' => "TEXT DEFAULT ''",
        'extras' => "TEXT DEFAULT ''",
        'min_age' => "INTEGER DEFAULT 0",
        'coach_depth' => "INTEGER DEFAULT 0",
    ];
    foreach ($add as $c => $def) if (!in_array($c, $cols, true)) $pdo->exec("ALTER TABLE providers ADD COLUMN $c $def");
    $mcols = array_column($pdo->query('PRAGMA table_info(media)')->fetchAll(), 'name');
    if (!in_array('credit', $mcols, true)) $pdo->exec("ALTER TABLE media ADD COLUMN credit TEXT DEFAULT ''");
    $pdo->exec('CREATE INDEX IF NOT EXISTS providers_token ON providers (edit_token)');
}

function new_token(): string { return bin2hex(random_bytes(16)); }

// The provider's private page. Anyone with the link can edit that one profile, so it is only ever
// sent to the provider (WhatsApp or email) and can be replaced from the admin.
function me_url(array $p, string $sub = ''): string { return abs_url('/me/' . $p['edit_token'] . $sub); }

function provider_by_token(string $token): ?array {
    if (!preg_match('/^[a-f0-9]{32}$/', $token)) return null;
    $p = q('SELECT * FROM providers WHERE edit_token = ?', [$token])->fetch();
    return ($p && hash_equals($p['edit_token'], $token)) ? $p : null;
}

function q(string $sql, array $args = []): PDOStatement {
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st;
}

// ---------- helpers ----------
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function url(string $path = ''): string { return '/en/' . ltrim($path, '/'); }

function abs_url(string $path): string { return rtrim(is_local() ? ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')) : cfg('base_url'), '/') . $path; }

function slugify(string $s): string {
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $s), '-'));
    return $s !== '' ? $s : 'provider-' . bin2hex(random_bytes(3));
}

function csv_list(?string $s): array {
    return array_values(array_filter(array_map('trim', explode(',', (string)$s)), 'strlen'));
}

function money(?float $eur): string {
    if ($eur === null || $eur <= 0) return '—';
    return '€' . number_format($eur, $eur == floor($eur) ? 0 : 2);
}

function redirect(string $to, int $code = 302): void { header('Location: ' . $to, true, $code); exit; }

function not_found(): void {
    http_response_code(404);
    render('notfound', ['title' => 'Not found', 'noindex' => true]);
    exit;
}

function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) start_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) start_session();
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $t)) {
        http_response_code(400);
        exit('The form expired. Go back, reload the page and try again.');
    }
}

function start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !is_local(), 'path' => '/']);
    session_name('dive_sess');
    session_start();
}

function client_ip(): string { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }

// Simple per-IP rate limit stored in the database.
function rate_limited(string $bucket, int $max, int $seconds): bool {
    $key = $bucket . ':' . hash('sha256', client_ip());
    q('DELETE FROM hits WHERE at < ?', [time() - 86400]);
    $n = (int)q('SELECT COUNT(*) FROM hits WHERE k = ? AND at > ?', [$key, time() - $seconds])->fetchColumn();
    if ($n >= $max) return true;
    q('INSERT INTO hits (k, at) VALUES (?, ?)', [$key, time()]);
    return false;
}

function wa_link(string $number, string $text = ''): string {
    $n = preg_replace('/\D+/', '', $number);
    return 'https://wa.me/' . $n . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

// ---------- domain data ----------
const LEVELS = [
    0 => ['Try / intro', 'One-day taster'],
    1 => ['Beginner', 'AIDA 2 · Molchanovs Wave 1 · SSI Level 1'],
    2 => ['Intermediate', 'AIDA 3 · Molchanovs Wave 2 · SSI Level 2'],
    3 => ['Advanced', 'AIDA 4 · Molchanovs Wave 3 · SSI Level 3'],
    4 => ['Instructor', 'Instructor programs'],
];
// Typical max course depth for each level (used to match "who teaches to this depth").
const LEVEL_DEPTH = [0 => 10, 1 => 20, 2 => 30, 3 => 40, 4 => 60];

const PROVIDER_TYPES = [
    'school'    => 'Freediving school',
    'centre'    => 'Dive centre',
    'freelance' => 'Freelance instructor',
    'coach'     => 'Coach / guide',
];
const LICENCE = [
    'centre'     => 'Licensed centre',
    'works_with' => 'Works with a centre',
    'freelance'  => 'Freelance',
];
const SERVICE_CATS = [
    'course'   => 'Course',
    'coaching' => 'Coaching / training',
    'fun'      => 'Fun dive',
    'guiding'  => 'Guiding',
    'rental'   => 'Equipment rental',
    'scuba'    => 'Scuba',
];
const AGENCIES = ['AIDA', 'Molchanovs', 'SSI', 'PADI', 'CMAS'];
const LANGS = ['English', 'Arabic', 'Russian', 'German', 'French', 'Italian', 'Spanish', 'Ukrainian', 'Polish'];
const SOCIALS = ['instagram' => 'Instagram', 'tiktok' => 'TikTok', 'youtube' => 'YouTube', 'facebook' => 'Facebook', 'website' => 'Website'];

// What a provider is good at, and the needs a diver can have. Used on profiles, the directory and the matcher.
const SPECIALTIES = [
    'first_timers' => ['Nervous beginners', 'Patient with people who are new to the water or a bit scared.'],
    'equalisation' => ['Equalisation', 'Fixes ear problems: Frenzel, hands-free, mouthfill.'],
    'depth'        => ['Depth 40 m+', 'Coaches deep dives on the line, past 40 m.'],
    'competition'  => ['Competition prep', 'Trains athletes for competitions and records.'],
    'pool'         => ['Pool: static & dynamic', 'Breath-hold and distance training in the pool.'],
    'relaxation'   => ['Relaxation & breathing', 'Breathwork, relaxation and the mental side.'],
    'safety'       => ['Safety & rescue', 'Safety-diver and rescue skills.'],
    'photo'        => ['Underwater photo & video', 'Films or photographs your dives.'],
    'kids'         => ['Kids & teens', 'Teaches children and teenagers.'],
    'women'        => ['Female instructor', 'A female instructor can teach you.'],
    'yoga'         => ['Yoga & stretching', 'Stretching and yoga made for freedivers.'],
    'scuba_cross'  => ['Scuba divers', 'Helps scuba divers switch to freediving.'],
];
const STYLES = ['calm' => 'Calm & patient', 'technical' => 'Technical & detailed', 'fun' => 'Fun & social', 'performance' => 'Performance-focused'];
const FORMATS = ['private' => 'Private (1 student)', 'small' => 'Small group (2–3)', 'group' => 'Group (4+)'];
const EXTRAS = [
    'video' => 'Video of your dives', 'flex' => 'Start any day', 'online_theory' => 'Theory online before you arrive',
    'pickup' => 'Hotel pickup', 'card' => 'Pay by card', 'gear_sizes' => 'Gear in all sizes', 'adaptive' => 'Adaptive freediving (disabilities)',
];
const MONTHS = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];

// "Oct–May" style summary of the months someone teaches in Dahab. Empty = all year.
function months_label(string $csv): string {
    $m = array_map('intval', csv_list($csv));
    if (!$m || count($m) === 12) return 'All year';
    sort($m);
    // find the longest wrap-around run to print as a range
    $set = array_flip($m); $runs = [];
    foreach ($m as $start) {
        if (isset($set[$start === 1 ? 12 : $start - 1])) continue;
        $len = 0; $x = $start;
        while (isset($set[$x])) { $len++; $x = $x % 12 + 1; if ($len > 12) break; }
        $runs[] = [$start, $len];
    }
    if (!$runs) return 'All year';
    return implode(', ', array_map(fn($r) => $r[1] === 1 ? MONTHS[$r[0]] : MONTHS[$r[0]] . '–' . MONTHS[($r[0] + $r[1] - 2) % 12 + 1], $runs));
}

function spots(): array {
    static $s = null;
    if ($s === null) $s = require APP_DIR . '/spots.php';
    return $s;
}
function spot(string $slug): ?array { return spots()[$slug] ?? null; }

function providers_approved(): array {
    return q("SELECT * FROM providers WHERE status = 'approved' ORDER BY featured DESC, founding DESC, name")->fetchAll();
}

function services_for(int $pid): array {
    return q('SELECT * FROM services WHERE provider_id = ? ORDER BY category = \'course\' DESC, level, price_eur', [$pid])->fetchAll();
}

function services_by_provider(): array {
    $out = [];
    foreach (q('SELECT * FROM services ORDER BY level, price_eur')->fetchAll() as $s) $out[(int)$s['provider_id']][] = $s;
    return $out;
}

function media_for(int $pid, bool $approvedOnly = true): array {
    return q('SELECT * FROM media WHERE provider_id = ?' . ($approvedOnly ? " AND status = 'approved'" : '') . ' ORDER BY id DESC', [$pid])->fetchAll();
}

// The lowest price per level for a provider (courses only).
function course_price(array $services, int $level): ?array {
    $best = null;
    foreach ($services as $s) {
        if ($s['category'] !== 'course' || (int)$s['level'] !== $level || (float)$s['price_eur'] <= 0) continue;
        if (!$best || (float)$s['price_eur'] < (float)$best['price_eur']) $best = $s;
    }
    return $best;
}

function max_depth_taught(array $services): int {
    $d = 0;
    foreach ($services as $s) if ($s['category'] === 'course') $d = max($d, LEVEL_DEPTH[(int)$s['level']] ?? 0);
    foreach ($services as $s) if ($s['category'] === 'coaching') $d = max($d, 60);
    return $d;
}

function initials(string $name): string {
    $w = preg_split('/\s+/', trim(preg_replace('/\(.*?\)/', '', $name)));
    return strtoupper(substr($w[0] ?? '', 0, 1) . substr($w[1] ?? '', 0, 1));
}

function avatar_hue(string $s): int { return hexdec(substr(md5($s), 0, 2)) % 360; }

// ---------- images ----------
// A media file is either an upload (file name) or, for sample data, an Unsplash photo id.
function img_src(string $file, int $w = 800): string {
    if (str_starts_with($file, 'photo-')) return 'https://images.unsplash.com/' . rawurlencode($file) . '?auto=format&fit=crop&q=70&w=' . $w;
    return cfg('uploads_url') . '/' . rawurlencode($file);
}
function unsplash_credit(string $user): string {
    return $user === '' ? '' : 'Photo: <a href="https://unsplash.com/@' . h($user) . '?utm_source=wheretospot&utm_medium=referral" rel="noopener" target="_blank">@' . h($user) . '</a> / Unsplash';
}
// The first approved photo of each provider: their cover on cards and profiles.
function provider_covers(): array {
    static $c = null;
    if ($c === null) {
        $c = [];
        foreach (q("SELECT provider_id, file FROM media WHERE status = 'approved' AND kind = 'photo' ORDER BY id")->fetchAll() as $r) $c[(int)$r['provider_id']] ??= $r['file'];
    }
    return $c;
}
function short_langs(string $csv): string {
    $map = ['English' => 'EN', 'Arabic' => 'AR', 'Russian' => 'RU', 'German' => 'DE', 'French' => 'FR', 'Italian' => 'IT', 'Spanish' => 'ES', 'Ukrainian' => 'UA', 'Polish' => 'PL'];
    return implode(' · ', array_map(fn($l) => $map[$l] ?? $l, csv_list($csv)));
}

// ---------- views ----------
function render(string $view, array $vars = []): void {
    extract($vars, EXTR_SKIP);
    ob_start();
    require APP_DIR . '/views/' . $view . '.php';
    $content = ob_get_clean();
    require APP_DIR . '/views/layout.php';
}

function partial(string $name, array $vars = []): void {
    extract($vars, EXTR_SKIP);
    require APP_DIR . '/views/_' . $name . '.php';
}
