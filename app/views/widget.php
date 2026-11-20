<?php
// Embeddable widget (iframe). Self-contained: no site CSS.
$st = overall_state($c);
$calm = [];
foreach (spots() as $k => $s) { if (!$s['freedive']) continue; $x = spot_state($s, $c); if ($x[0] === 'calm') $calm[] = $s['name']; }
$col = ['calm' => '#10b981', 'ok' => '#f59e0b', 'rough' => '#ef4444', 'unknown' => '#94a3b8'][$st[0]];
$link = $p ? '/en/p/' . $p['slug'] : '/en/today';
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Today in Dahab</title>
<style>
*{box-sizing:border-box}body{margin:0;font:14px/1.4 Inter,system-ui,-apple-system,Segoe UI,Arial,sans-serif;color:#fff;background:linear-gradient(170deg,#0e7490,#0b2545);min-height:100vh}
.w{padding:14px 16px}.t{display:flex;justify-content:space-between;align-items:center;font-size:12px;opacity:.85}
.s{display:flex;align-items:center;gap:8px;font-weight:600;margin:8px 0 10px}.d{width:10px;height:10px;border-radius:50%;background:<?= $col ?>;box-shadow:0 0 0 4px <?= $col ?>33}
.n{display:flex;gap:16px;margin-bottom:10px}.n b{display:block;font-size:22px}.n span{font-size:11px;opacity:.75;text-transform:uppercase;letter-spacing:.06em}
.c{font-size:12px;opacity:.9;margin-bottom:12px;min-height:17px}a.b{display:block;text-align:center;background:#ecfeff;color:#0b2545;font-weight:700;text-decoration:none;padding:9px;border-radius:10px}
a.l{color:#a5f3fc;font-size:11px;text-decoration:none}
</style></head><body><div class="w">
<div class="t"><span>Today in Dahab</span><a class="l" href="<?= h(abs_url('/en/today')) ?>" target="_blank" rel="noopener">Where To Spot ↗</a></div>
<div class="s"><span class="d"></span><?= h($st[1]) ?></div>
<?php if ($c): ?><div class="n"><div><span>Wind</span><b><?= (int)$c['wind'] ?> kn</b></div><?php if ($c['water'] !== null): ?><div><span>Water</span><b><?= (int)$c['water'] ?>°C</b></div><?php endif; ?><?php if ($c['wave'] !== null): ?><div><span>Waves</span><b><?= h($c['wave']) ?> m</b></div><?php endif; ?></div><?php endif; ?>
<div class="c"><?= $calm ? 'Calm now: ' . h(implode(', ', array_slice($calm, 0, 3))) : 'No spot looks calm right now.' ?></div>
<a class="b" href="<?= h(abs_url($link)) ?>" target="_blank" rel="noopener"><?= $p ? 'Dive with ' . h($p['name']) : 'See all spots' ?></a>
</div></body></html>
