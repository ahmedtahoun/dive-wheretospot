<?php
// Profile editing shared by the admin and the provider's own private page.

// Saves profile fields and courses from a form post. Admin-only fields are ignored for providers.
function save_profile(int $id, array $post, bool $admin): void {
    $p = q('SELECT * FROM providers WHERE id = ?', [$id])->fetch();
    if (!$p) return;
    $f = fn($k, $len = 200) => mb_substr(trim((string)($post[$k] ?? '')), 0, $len);
    $pick = fn($k, $allowed) => implode(',', array_values(array_intersect((array)($post[$k] ?? []), $allowed)));
    $url = fn($k) => ($v = $f($k)) !== '' && preg_match('#^https?://#', $v) && filter_var($v, FILTER_VALIDATE_URL) ? $v : '';

    $links = [];
    foreach (preg_split('/\s+/', $f('work_links', 2000)) as $l) {
        if (preg_match('#^https?://#', $l) && filter_var($l, FILTER_VALIDATE_URL)) $links[] = $l;
    }

    $set = [
        'name' => $f('name', 80) ?: $p['name'],
        'type' => array_key_exists($post['type'] ?? '', PROVIDER_TYPES) ? $post['type'] : $p['type'],
        'tagline' => $f('tagline', 140), 'bio' => $f('bio', 4000),
        'languages' => $pick('languages', LANGS), 'agencies' => $pick('agencies', AGENCIES),
        'licence' => array_key_exists($post['licence'] ?? '', LICENCE) ? $post['licence'] : $p['licence'],
        'works_with' => $f('works_with', 80), 'whatsapp' => $f('whatsapp', 30) ?: $p['whatsapp'],
        'email' => filter_var($f('email', 120), FILTER_VALIDATE_EMAIL) ?: '',
        'website' => $url('website'), 'instagram' => $url('instagram'), 'tiktok' => $url('tiktok'),
        'youtube' => $url('youtube'), 'facebook' => $url('facebook'),
        'base_area' => $f('base_area', 60), 'spots' => $pick('spots', array_keys(spots())),
        'pool' => !empty($post['pool']) ? 1 : 0, 'max_ratio' => max(0, min(20, (int)($post['max_ratio'] ?? 0))),
        'gear_included' => !empty($post['gear_included']) ? 1 : 0,
        'years_teaching' => max(0, min(60, (int)($post['years_teaching'] ?? 0))),
        'students_certified' => max(0, (int)($post['students_certified'] ?? 0)),
        'personal_best' => $f('personal_best', 60), 'achievements' => $f('achievements', 2000),
        'work_links' => implode("\n", array_slice(array_unique($links), 0, 6)),
        'cert_agency' => in_array($post['cert_agency'] ?? '', AGENCIES, true) ? $post['cert_agency'] : '',
        'cert_number' => $f('cert_number', 60),
        'specialties' => implode(',', array_slice(array_values(array_intersect((array)($post['specialties'] ?? []), array_keys(SPECIALTIES))), 0, 6)),
        'styles' => implode(',', array_slice(array_values(array_intersect((array)($post['styles'] ?? []), array_keys(STYLES))), 0, 2)),
        'formats' => $pick('formats', array_keys(FORMATS)),
        'extras' => $pick('extras', array_keys(EXTRAS)),
        'months' => implode(',', array_values(array_intersect(array_map('intval', (array)($post['months'] ?? [])), array_keys(MONTHS)))),
        'min_age' => max(0, min(18, (int)($post['min_age'] ?? 0))),
        'coach_depth' => max(0, min(150, (int)($post['coach_depth'] ?? 0))),
    ];
    if ($admin) {
        $slug = slugify($f('slug', 80) ?: $set['name']);
        if (q('SELECT 1 FROM providers WHERE slug = ? AND id <> ?', [$slug, $id])->fetchColumn()) $slug = $p['slug'];
        $set['slug'] = $slug;
        $set['verified_note'] = $f('verified_note', 200);
        $set['checked_on'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $f('checked_on', 10)) ? $f('checked_on', 10) : '';
        $set['week_note'] = $f('week_note', 120);
        $week = !empty($post['teaching_week']) ? 1 : 0;
        if ($week && !$p['teaching_week']) $set['week_updated'] = date('Y-m-d');
        $set['teaching_week'] = $week;
    } else {
        $set['checked_on'] = date('Y-m-d'); // the provider just confirmed their prices
        // If they change the certificate they claim, the badge must be checked again.
        if ($p['verified'] && ($set['cert_number'] !== $p['cert_number'] || $set['cert_agency'] !== $p['cert_agency'])) $set['verified'] = 0;
    }
    $cols = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($set)));
    q("UPDATE providers SET $cols, updated_at = datetime('now') WHERE id = :id", $set + ['id' => $id]);

    q('DELETE FROM services WHERE provider_id = ?', [$id]);
    foreach ((array)($post['svc'] ?? []) as $s) {
        if (!is_array($s) || !empty($s['del']) || trim((string)($s['title'] ?? '')) === '') continue;
        q('INSERT INTO services (provider_id, category, level, agency, title, days, price_eur, notes) VALUES (?,?,?,?,?,?,?,?)', [
            $id, array_key_exists($s['category'] ?? '', SERVICE_CATS) ? $s['category'] : 'course', max(0, min(4, (int)($s['level'] ?? 1))),
            in_array($s['agency'] ?? '', AGENCIES, true) ? $s['agency'] : '', mb_substr(trim((string)$s['title']), 0, 100),
            max(0, min(60, (float)($s['days'] ?? 0))), max(0, min(20000, (float)($s['price_eur'] ?? 0))), mb_substr(trim((string)($s['notes'] ?? '')), 0, 120),
        ]);
    }
}

// Stores an uploaded photo (resized to max 1600 px, WebP) or MP4 video. Returns an error message or ''.
function save_upload(int $pid, array $f, string $spot, string $caption, string $status): string {
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return 'Upload failed. Photos can be up to 12 MB, videos up to 40 MB.';
    if ((int)q("SELECT COUNT(*) FROM media WHERE provider_id = ?", [$pid])->fetchColumn() >= 40) return 'You have reached 40 photos and videos. Delete some first.';
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $dir = cfg('uploads_dir');
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $base = 'p' . $pid . '-' . bin2hex(random_bytes(6));
    if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) && $f['size'] <= 12 * 1024 * 1024) {
        $name = $base . (function_exists('imagewebp') ? '.webp' : '.jpg');
        if (!resize_image($f['tmp_name'], $mime, "$dir/$name", 1600)) return 'That image could not be read.';
        $kind = 'photo';
    } elseif ($mime === 'video/mp4' && $f['size'] <= 40 * 1024 * 1024) {
        $name = $base . '.mp4';
        if (!move_uploaded_file($f['tmp_name'], "$dir/$name")) return 'Upload failed.';
        $kind = 'video';
    } else {
        return 'Only JPG, PNG or WebP photos (up to 12 MB) and MP4 videos (up to 40 MB).';
    }
    q('INSERT INTO media (provider_id, file, kind, caption, spot, status) VALUES (?,?,?,?,?,?)', [
        $pid, $name, $kind, mb_substr(trim($caption), 0, 140), array_key_exists($spot, spots()) ? $spot : '', $status,
    ]);
    return '';
}

function resize_image(string $src, string $mime, string $dest, int $max): bool {
    $img = @imagecreatefromstring((string)file_get_contents($src));
    if (!$img) return false;
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $o = @exif_read_data($src)['Orientation'] ?? 1;
        if ($o == 3) $img = imagerotate($img, 180, 0); elseif ($o == 6) $img = imagerotate($img, -90, 0); elseif ($o == 8) $img = imagerotate($img, 90, 0);
    }
    $w = imagesx($img); $hgt = imagesy($img);
    if ($w > $max || $hgt > $max) {
        $r = min($max / $w, $max / $hgt);
        $nw = (int)round($w * $r); $nh = (int)round($hgt * $r);
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $hgt);
        $img = $dst;
    }
    return str_ends_with($dest, '.webp') ? imagewebp($img, $dest, 82) : imagejpeg($img, $dest, 84);
}

function delete_media(array $m): void {
    q('DELETE FROM media WHERE id = ?', [$m['id']]);
    $file = cfg('uploads_dir') . '/' . basename($m['file']);
    if (is_file($file)) unlink($file);
}

// Certificate card photos are private: stored in the data folder, shown only in the admin.
function save_cert(int $pid, array $f): string {
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return 'Upload failed.';
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) || $f['size'] > 12 * 1024 * 1024) return 'Please send a JPG, PNG or WebP photo of your card.';
    $dir = cfg('data_dir') . '/certs';
    if (!is_dir($dir)) mkdir($dir, 0700, true);
    $name = 'c' . $pid . '-' . bin2hex(random_bytes(6)) . (function_exists('imagewebp') ? '.webp' : '.jpg');
    if (!resize_image($f['tmp_name'], $mime, "$dir/$name", 1400)) return 'That image could not be read.';
    $old = q('SELECT cert_file FROM providers WHERE id = ?', [$pid])->fetchColumn();
    if ($old && is_file("$dir/" . basename($old))) unlink("$dir/" . basename($old));
    q('UPDATE providers SET cert_file = ? WHERE id = ?', [$name, $pid]);
    return '';
}

// Activity numbers for the last 30 days, for the provider's page and the admin.
function provider_stats(int $pid): array {
    $out = ['view' => 0, 'whatsapp' => 0, 'social' => 0, 'inquiries' => 0];
    foreach (q("SELECT kind, COUNT(*) n FROM clicks WHERE provider_id = ? AND created_at > datetime('now','-30 days') GROUP BY kind", [$pid])->fetchAll() as $r) {
        if ($r['kind'] === 'view' || $r['kind'] === 'whatsapp') $out[$r['kind']] += (int)$r['n'];
        else $out['social'] += (int)$r['n'];
    }
    $out['inquiries'] = (int)q("SELECT COUNT(*) FROM inquiries WHERE provider_id = ? AND created_at > datetime('now','-30 days')", [$pid])->fetchColumn();
    return $out;
}

// Quick-add course presets shown on the course editor.
const COURSE_PRESETS = [
    ['course', 0, '', 'Discover freediving', 1],
    ['course', 1, 'AIDA', 'AIDA 2 Freediver', 3],
    ['course', 2, 'AIDA', 'AIDA 3 Advanced Freediver', 3],
    ['course', 3, 'AIDA', 'AIDA 4 Master Freediver', 4],
    ['course', 1, 'Molchanovs', 'Molchanovs Wave 1', 3],
    ['course', 2, 'Molchanovs', 'Molchanovs Wave 2', 3],
    ['course', 3, 'Molchanovs', 'Molchanovs Wave 3', 4],
    ['course', 1, 'SSI', 'SSI Freediver Level 1', 3],
    ['course', 2, 'SSI', 'SSI Freediver Level 2', 3],
    ['coaching', 0, '', 'Coached line session', 0.5],
    ['coaching', 0, '', 'Private coaching', 0.5],
    ['fun', 0, '', 'Guided fun freedive', 0.5],
    ['rental', 0, '', 'Freediving kit rental', 1],
];
