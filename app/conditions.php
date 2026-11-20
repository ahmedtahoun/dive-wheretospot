<?php
// Live sea conditions for Dahab from Open-Meteo (free, no key). Cached for 30 minutes in the data folder.
// If the API is unreachable the last cached copy is used; if there is none, null is returned.

function conditions(): ?array {
    $cacheFile = cfg('data_dir') . '/conditions.json';
    $cached = is_file($cacheFile) ? json_decode((string)file_get_contents($cacheFile), true) : null;
    if ($cached && (time() - ($cached['fetched'] ?? 0)) < 1800) return $cached;

    $lat = cfg('lat'); $lon = cfg('lon');
    $ctx = stream_context_create(['http' => ['timeout' => 4, 'ignore_errors' => true, 'header' => "User-Agent: dive-wheretospot/1.0\r\n"]]);
    $wx = @file_get_contents("https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&current=temperature_2m,wind_speed_10m,wind_direction_10m,wind_gusts_10m&hourly=wind_speed_10m,wind_gusts_10m&wind_speed_unit=kn&timezone=Africa%2FCairo&forecast_days=2", false, $ctx);
    $sea = @file_get_contents("https://marine-api.open-meteo.com/v1/marine?latitude=$lat&longitude=" . ($lon + 0.03) . "&current=wave_height,sea_surface_temperature&timezone=Africa%2FCairo", false, $ctx);
    $w = $wx ? json_decode($wx, true) : null;
    $m = $sea ? json_decode($sea, true) : null;

    if (!isset($w['current']['wind_speed_10m'])) return $cached ?: null;

    $now = $w['current']['time'] ?? date('Y-m-d\TH:00');
    $hours = [];
    foreach (($w['hourly']['time'] ?? []) as $i => $t) {
        if ($t < substr($now, 0, 13)) continue;
        $hours[] = ['t' => substr($t, 11, 5), 'wind' => round((float)$w['hourly']['wind_speed_10m'][$i]), 'gust' => round((float)$w['hourly']['wind_gusts_10m'][$i])];
        if (count($hours) >= 12) break;
    }
    $data = [
        'fetched'   => time(),
        'time'      => $now,
        'air'       => round((float)$w['current']['temperature_2m']),
        'wind'      => round((float)$w['current']['wind_speed_10m']),
        'gust'      => round((float)$w['current']['wind_gusts_10m']),
        'dir'       => (int)$w['current']['wind_direction_10m'],
        'wave'      => isset($m['current']['wave_height']) ? round((float)$m['current']['wave_height'], 1) : null,
        'water'     => isset($m['current']['sea_surface_temperature']) ? round((float)$m['current']['sea_surface_temperature']) : null,
        'hours'     => $hours,
    ];
    @file_put_contents($cacheFile, json_encode($data));
    return $data;
}

function compass(int $deg): string {
    $d = ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'];
    return $d[(int)round($deg / 22.5) % 16];
}

// Estimated surface state at a spot: the wind matters more where the spot is exposed.
// Returns [key, label]. This is a guide, not a forecast; the page says so.
function spot_state(array $spot, ?array $c): array {
    if (!$c) return ['unknown', 'No data'];
    $exp = (int)($spot['exposure'] ?? 1);
    $northish = in_array(compass($c['dir']), ['NW','NNW','N','NNE','NE'], true);
    $factor = [0 => 0.45, 1 => 0.75, 2 => 1.0][$exp];
    if (!$northish) $factor = min(1.0, $factor + 0.2); // unusual wind directions reach sheltered bays too
    $eff = $c['wind'] * $factor + max(0, $c['gust'] - $c['wind']) * 0.3;
    if ($eff < 9)  return ['calm', 'Calm'];
    if ($eff < 15) return ['ok', 'Some chop'];
    return ['rough', 'Rough'];
}

function overall_state(?array $c): array {
    if (!$c) return ['unknown', 'Conditions unavailable right now'];
    if ($c['wind'] < 10) return ['calm', 'A calm day: good for the Blue Hole and the north'];
    if ($c['wind'] < 18) return ['ok', 'Breezy: sheltered bays like Lighthouse are your friend'];
    return ['rough', 'Windy: stick to sheltered spots, or make it a pool/theory day'];
}
