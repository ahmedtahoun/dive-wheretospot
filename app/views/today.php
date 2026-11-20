<?php
$st = overall_state($c);
$spotStates = [];
foreach (spots() as $k => $s) { $spotStates[$k] = spot_state($s, $c); }
$order = ['calm' => 0, 'ok' => 1, 'rough' => 2, 'unknown' => 3];
uksort($spotStates, fn($a, $b) => [$order[$spotStates[$a][0]], (int)!spots()[$a]['freedive']] <=> [$order[$spotStates[$b][0]], (int)!spots()[$b]['freedive']]);
$maxWind = $c ? max(20, max(array_map(fn($x) => $x['gust'], $c['hours'] ?: [['gust' => 20]]))) : 20;
?>
<section class="today-hero state-<?= h($st[0]) ?>">
  <div class="wrap">
    <p class="eyebrow">Today in Dahab · <?= $c ? h(date('D j M, H:i', strtotime($c['time']))) : h(date('D j M')) ?></p>
    <h1><?= h($st[1]) ?></h1>
    <?php if ($c): ?>
    <ul class="bignums">
      <li><span>Wind</span><b><?= (int)$c['wind'] ?><small> kn</small></b><em><?= h(compass($c['dir'])) ?>, gusts <?= (int)$c['gust'] ?> kn</em></li>
      <li><span>Water</span><b><?= $c['water'] !== null ? (int)$c['water'] . '<small>°C</small>' : '—' ?></b><em><?= $c['water'] !== null && $c['water'] < 24 ? '5 mm wetsuit weather' : ($c['water'] !== null ? '3 mm is plenty' : '') ?></em></li>
      <li><span>Waves</span><b><?= $c['wave'] !== null ? h($c['wave']) . '<small> m</small>' : '—' ?></b><em>offshore estimate</em></li>
      <li><span>Air</span><b><?= (int)$c['air'] ?><small>°C</small></b><em>&nbsp;</em></li>
    </ul>
    <?php else: ?>
      <p class="lead">We couldn't load live conditions just now. Try again in a few minutes.</p>
    <?php endif; ?>
  </div>
</section>

<?php if ($c && $c['hours']): ?>
<section class="wrap section">
  <h2>Wind, next 12 hours</h2>
  <div class="windchart" role="img" aria-label="Wind forecast for the next 12 hours, from <?= (int)$c['hours'][0]['wind'] ?> to <?= (int)end($c['hours'])['wind'] ?> knots">
    <?php foreach ($c['hours'] as $hr): $cls = $hr['wind'] < 10 ? 'calm' : ($hr['wind'] < 18 ? 'ok' : 'rough'); ?>
      <div class="wc-col">
        <span class="wc-val"><?= (int)$hr['wind'] ?></span>
        <span class="wc-bar"><i class="wc-gust" style="height:<?= round($hr['gust'] / $maxWind * 100) ?>%"></i><i class="wc-wind <?= $cls ?>" style="height:<?= round($hr['wind'] / $maxWind * 100) ?>%"></i></span>
        <span class="wc-t"><?= h($hr['t']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
  <p class="muted small">Knots. Solid bar = average wind, light bar = gusts. Mornings are usually calmest in Dahab.</p>
</section>
<?php endif; ?>

<section class="wrap section">
  <div class="sec-head"><h2>Spots right now</h2><a href="/en/spots">Map →</a></div>
  <div class="grid spotstate">
    <?php foreach ($spotStates as $k => [$key, $label]): $s = spots()[$k]; ?>
      <a class="card ss state-<?= h($key) ?>" href="/en/spots/<?= h($k) ?>">
        <span class="ss-state"><span class="dot" aria-hidden="true"></span><?= h($label) ?></span>
        <b><?= h($s['name']) ?></b>
        <span class="muted small"><?= h($s['area']) ?> · <?= h($s['depth']) ?> · <?= $s['freedive'] ? 'Freediving' : 'Scuba' ?></span>
      </a>
    <?php endforeach; ?>
  </div>
  <p class="muted small">Our estimate from the wind and how sheltered each spot is. It is a guide, not a safety call: your instructor decides on the day.</p>
</section>

<section class="band">
  <div class="wrap section two-col">
    <div>
      <h2>Teaching this week</h2>
      <ul class="weeklist">
        <?php foreach ($teaching as $p): ?>
          <li><a href="/en/p/<?= h($p['slug']) ?>"><b><?= h($p['name']) ?></b></a><span class="muted"><?= h($p['week_note'] ?: (PROVIDER_TYPES[$p['type']] ?? '')) ?></span></li>
        <?php endforeach; ?>
        <?php if (!$teaching): ?><li class="muted">Nobody has updated this week yet.</li><?php endif; ?>
      </ul>
    </div>
    <div id="events">
      <h2>Events & camps</h2>
      <?php partial('events', ['events' => $events, 'long' => true]); ?>
    </div>
  </div>
</section>
<p class="wrap muted small source">Weather data: <a href="https://open-meteo.com/" rel="noopener">Open-Meteo</a>, updated every 30 minutes.</p>
