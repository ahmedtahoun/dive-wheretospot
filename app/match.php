<?php
// "Find my match": a diver says what they can do and what they need; we score every provider
// and explain each score in plain words. No account: the answers live in the URL (and the
// browser remembers them for next time).

const MATCH_EXP = [
    'never' => ['Never freedived', 'I can swim but haven\'t tried'],
    'tried' => ['Tried it', 'A taster or snorkelling dives'],
    'l1'    => ['Beginner certificate', 'AIDA 2 · Wave 1 · SSI Level 1'],
    'l2'    => ['Intermediate', 'AIDA 3 · Wave 2 · SSI Level 2'],
    'l3'    => ['Advanced / training', '40 m+ or competing'],
];
const MATCH_GOALS = [
    'try'     => ['Just try it', 'One day, no pressure'],
    'cert'    => ['Get certified', 'The next course for my level'],
    'fun'     => ['Explore the reefs', 'Fun dives and guided trips'],
    'deeper'  => ['Go deeper', 'Coaching to beat my depth'],
    'fix'     => ['Fix my ears', 'Equalisation is stopping me'],
    'compete' => ['Train for competition', 'Serious depth training'],
];
const MATCH_NEEDS = [
    'nervous' => 'I\'m nervous in the water',
    'private' => 'Private lessons',
    'small'   => 'Small group (max 3)',
    'women'   => 'A female instructor',
    'kids'    => 'Diving with kids',
    'pool'    => 'A pool before the sea',
    'video'   => 'Video of my dives',
    'pickup'  => 'Hotel pickup',
    'card'    => 'Pay by card',
];
const MATCH_BUDGETS = [0 => 'Any budget', 200 => 'Under €200', 250 => 'Under €250', 300 => 'Under €300', 400 => 'Under €400'];

function match_input(array $g): array {
    $one = fn($k, $allowed) => in_array($g[$k] ?? '', $allowed, true) ? $g[$k] : '';
    return [
        'exp'    => $one('exp', array_keys(MATCH_EXP)),
        'goal'   => $one('goal', array_keys(MATCH_GOALS)),
        'needs'  => array_values(array_intersect((array)($g['needs'] ?? []), array_keys(MATCH_NEEDS))),
        'lang'   => $one('lang', LANGS),
        'month'  => isset(MONTHS[(int)($g['month'] ?? 0)]) ? (int)$g['month'] : 0,
        'budget' => isset(MATCH_BUDGETS[(int)($g['budget'] ?? 0)]) ? (int)($g['budget'] ?? 0) : 0,
    ];
}

// The course level a diver should book next (null = coaching rather than a course).
function match_target_level(array $in): ?int {
    if ($in['goal'] === 'try') return 0;
    return match ($in['exp']) { 'never', 'tried', '' => 1, 'l1' => 2, 'l2' => 3, default => null };
}

// Score one provider for one diver. Every question the diver answered is worth some points; the score is the
// share of those points the provider earns, so a missing need always shows. Returns [score 1–99, reasons, blocked].
function match_score(array $p, array $svc, array $in): array {
    $has = fn($field, $k) => in_array($k, csv_list($p[$field]), true);
    $why = []; $got = 0.0; $max = 0.0; $blocked = false;
    $add = function (float $w, float $earned, ?bool $good = null, string $text = '') use (&$why, &$got, &$max) {
        $max += $w; $got += $earned;
        if ($text !== '') $why[] = [(bool)$good, $text];
    };
    $lvl = match_target_level($in);
    $course = $lvl !== null ? course_price($svc, $lvl) : null;
    $coaching = (bool)array_filter($svc, fn($s) => $s['category'] === 'coaching');
    $fun = (bool)array_filter($svc, fn($s) => in_array($s['category'], ['fun', 'guiding'], true));

    // 1. Do they offer what the diver is here for? (biggest weight)
    $W = 30;
    switch ($in['goal']) {
        case 'fun':
            $fun ? $add($W, $W, true, 'Runs fun dives and reef trips') : $add($W, 0, false, 'Doesn\'t list fun dives');
            break;
        case 'fix':
            if ($has('specialties', 'equalisation')) $add($W, $W, true, 'Specialises in equalisation');
            elseif ($coaching) $add($W, $W * .4, true, 'Offers coaching sessions');
            else $add($W, 0, false, 'No equalisation coaching listed');
            break;
        case 'compete':
            if ($has('specialties', 'competition')) $add($W, $W, true, 'Coaches competition athletes');
            elseif ($has('specialties', 'depth')) $add($W, $W * .6, true, 'Coaches deep dives');
            else $add($W, 0, false, 'Not focused on competition training');
            break;
        case 'deeper':
            if ($has('specialties', 'depth') || (int)$p['coach_depth'] >= 40) $add($W, $W, true, 'Coaches you to ' . max(40, (int)$p['coach_depth']) . ' m');
            elseif ($course) $add($W, $W * .7, true, 'Teaches the next course: ' . $course['title']);
            elseif ($coaching) $add($W, $W * .5, true, 'Offers coached line sessions');
            else $add($W, 0, false, 'No depth coaching listed');
            break;
        default: // try / cert / not chosen
            if ($lvl === null) {
                $coaching ? $add($W, $W, true, 'Offers coaching for your level') : $add($W, 0, false, 'No coaching for advanced divers');
            } elseif ($course) {
                $add($W, $W, true, 'Teaches ' . $course['title'] . ' (' . money((float)$course['price_eur']) . ')');
            } else {
                $blocked = true; $add($W, 0, false, 'Doesn\'t list a ' . mb_strtolower(LEVELS[$lvl][0]) . ' course');
            }
    }

    // 2. Language: a hard need.
    if ($in['lang'] !== '') {
        if ($has('languages', $in['lang'])) $add(15, 15, true, 'Teaches in ' . $in['lang']);
        else { $blocked = true; $add(15, 0, false, 'Doesn\'t teach in ' . $in['lang']); }
    }

    // 3. Personal needs.
    $ratio = (int)$p['max_ratio'];
    foreach ($in['needs'] as $n) {
        switch ($n) {
            case 'nervous':
                if ($has('specialties', 'first_timers')) $add(10, 10, true, 'Great with nervous beginners');
                elseif ($has('styles', 'calm')) $add(10, 7, true, 'Calm, patient teaching style');
                else $add(10, 2, false, 'Not listed as beginner-friendly');
                break;
            case 'private':
                $has('formats', 'private') ? $add(8, 8, true, 'Teaches private lessons') : $add(8, 0, false, 'No private lessons listed');
                break;
            case 'small':
                if ($has('formats', 'private') || ($ratio > 0 && $ratio <= 3)) $add(6, 6, true, 'Small groups' . ($ratio > 0 && $ratio <= 3 ? ' (max ' . $ratio . ')' : ' or private'));
                elseif ($has('formats', 'small')) $add(6, 4, true, 'Small groups on request' . ($ratio ? ' (usually up to ' . $ratio . ')' : ''));
                else $add(6, 0, false, 'Groups of ' . ($ratio ?: 4) . ' or more');
                break;
            case 'women':
                $has('specialties', 'women') ? $add(12, 12, true, 'Female instructor available') : $add(12, 0, false, 'No female instructor listed');
                break;
            case 'kids':
                if ($has('specialties', 'kids')) $add(12, 12, true, 'Teaches kids' . ((int)$p['min_age'] ? ' from age ' . (int)$p['min_age'] : ''));
                else { $blocked = (int)$p['min_age'] >= 16; $add(12, 0, false, (int)$p['min_age'] ? 'Teaches ages ' . (int)$p['min_age'] . '+' : 'Doesn\'t list kids\' lessons'); }
                break;
            case 'pool':
                ((int)$p['pool'] || $has('specialties', 'pool')) ? $add(6, 6, true, 'Has a training pool') : $add(6, 2, false, 'No pool: first session in calm shallow sea');
                break;
            case 'video':
                ($has('extras', 'video') || $has('specialties', 'photo')) ? $add(4, 4, true, 'Films your dives') : $add(4, 0, false, 'Video not listed');
                break;
            case 'pickup':
                $has('extras', 'pickup') ? $add(3, 3, true, 'Hotel pickup') : $add(3, 0, false, 'No hotel pickup listed');
                break;
            case 'card':
                $has('extras', 'card') ? $add(3, 3, true, 'Takes card payments') : $add(3, 0, false, 'Cash only (as far as we know)');
                break;
        }
    }

    // 4. Dates and budget.
    if ($in['month']) {
        $months = array_map('intval', csv_list($p['months']));
        if (!$months || in_array($in['month'], $months, true)) $add(10, 10, true, 'In Dahab in ' . MONTHS[$in['month']]);
        else { $blocked = true; $add(10, 0, false, 'Not in Dahab in ' . MONTHS[$in['month']] . ' (' . months_label($p['months']) . ')'); }
    }
    if ($in['budget'] && $course) {
        (float)$course['price_eur'] <= $in['budget'] ? $add(6, 6, true, 'Within your budget') : $add(6, 1, false, 'Above your budget (' . money((float)$course['price_eur']) . ')');
    }

    // 5. Trust: small bonus inside the same scale.
    (int)$p['verified'] ? $add(4, 4, true, 'Certificate verified') : $add(4, 1);
    if ((int)$p['teaching_week'] && (!$in['month'] || $in['month'] === (int)date('n'))) $add(2, 2, true, 'Has space this week'); else $add(2, 0);

    usort($why, fn($a, $b) => $b[0] <=> $a[0]); // good news first
    $score = $max > 0 ? (int)round($got / $max * 100) : 50;
    return [max(1, min(99, $score)), $why, $blocked];
}

function match_results(array $in): array {
    $svc = services_by_provider();
    $out = [];
    foreach (providers_approved() as $p) {
        [$score, $why, $blocked] = match_score($p, $svc[(int)$p['id']] ?? [], $in);
        $out[] = ['p' => $p, 'svc' => $svc[(int)$p['id']] ?? [], 'score' => $blocked ? min($score, 40) : $score, 'why' => $why, 'blocked' => $blocked];
    }
    usort($out, fn($a, $b) => [$b['score'], (int)$b['p']['featured']] <=> [$a['score'], (int)$a['p']['featured']]);
    return $out;
}

// One-line summary of the diver, used in the inquiry message sent to providers.
function match_summary(array $in): string {
    $bits = [];
    if ($in['exp']) $bits[] = 'Level: ' . MATCH_EXP[$in['exp']][0];
    if ($in['goal']) $bits[] = 'Goal: ' . MATCH_GOALS[$in['goal']][0];
    if ($in['needs']) $bits[] = 'Needs: ' . implode(', ', array_map(fn($n) => MATCH_NEEDS[$n], $in['needs']));
    if ($in['lang']) $bits[] = 'Language: ' . $in['lang'];
    if ($in['month']) $bits[] = 'Month: ' . MONTHS[$in['month']];
    if ($in['budget']) $bits[] = 'Budget: ' . MATCH_BUDGETS[$in['budget']];
    return implode(' · ', $bits);
}

function page_match(): void {
    $in = match_input($_GET);
    $asked = ($_GET['go'] ?? '') === '1';
    render('match', [
        'title' => 'Find your freediving instructor in Dahab: 1-minute match',
        'desc'  => 'Answer 4 quick questions about your level, goal and needs, and see which Dahab freediving schools and coaches fit you best, and why.',
        'in' => $in, 'results' => $asked ? match_results($in) : [], 'asked' => $asked,
        'noindex' => $asked,
    ]);
}

// Send one request to the top matches at once.
function post_match_ask(): void {
    csrf_check();
    if (!empty($_POST['website_url'])) redirect('/en/match');
    $in = match_input($_POST);
    $name = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 80);
    $email = trim((string)($_POST['email'] ?? ''));
    $wa = mb_substr(trim((string)($_POST['whatsapp'] ?? '')), 0, 30);
    $slugs = array_slice(array_values(array_filter((array)($_POST['to'] ?? []), fn($s) => is_string($s) && preg_match('/^[a-z0-9-]+$/', $s))), 0, 3);
    if ($name === '' || ($email === '' && $wa === '') || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) || !$slugs) {
        http_response_code(400);
        render('match_sent', ['title' => 'Something is missing', 'noindex' => true, 'error' => true, 'sent' => [], 'name' => $name]);
        return;
    }
    if (rate_limited('match', 4, 3600)) { http_response_code(429); exit('Too many requests. Please try again in an hour.'); }
    $msg = mb_substr(trim((string)($_POST['message'] ?? '')), 0, 1500);
    $full = trim(match_summary($in) . ($msg !== '' ? "\n\n" . $msg : ''));
    $group = 'M' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
    $sent = [];
    foreach ($slugs as $i => $slug) {
        $p = q("SELECT * FROM providers WHERE slug = ? AND status = 'approved'", [$slug])->fetch();
        if (!$p) continue;
        $ref = $group . ($i + 1);
        q('INSERT INTO inquiries (ref, provider_id, name, email, whatsapp, dates, level, people, message) VALUES (?,?,?,?,?,?,?,?,?)', [
            $ref, $p['id'], $name, $email, $wa, mb_substr(trim((string)($_POST['dates'] ?? '')), 0, 80),
            $in['exp'] ? MATCH_EXP[$in['exp']][0] : '', max(1, min(20, (int)($_POST['people'] ?? 1))), $full,
        ]);
        notify_inquiry($p, $ref, $name, $email, $wa);
        $sent[] = ['p' => $p, 'ref' => $ref];
    }
    render('match_sent', ['title' => 'Sent to ' . count($sent) . ' instructors', 'noindex' => true, 'sent' => $sent, 'name' => $name, 'error' => false]);
}
