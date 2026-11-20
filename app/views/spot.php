<?php
$max = (int)$s['profile']['max'];
$px = (int)max(26, min(90, round(3000 / $max)));
$state = spot_state($s, $c);
$features = $s['profile']['features'];
if ($s['freedive']) {
  foreach (LEVELS as $lvl => [$name, $ag]) {
    if ($lvl === 4) continue;
    $d = LEVEL_DEPTH[$lvl];
    if ($d <= $max) $features[] = [$d, 'course', $name . ' course depth', $ag . ' usually reach about ' . $d . ' m.'];
  }
}
usort($features, fn($a, $b) => $a[0] <=> $b[0]);
// Data for the "who teaches to this depth" panel.
$teach = array_map(fn($p) => ['n' => $p['name'], 's' => $p['slug'], 'd' => max_depth_taught($p['_svc'])], $here);
$others = array_filter(spots(), fn($x, $k) => $k !== $slug && $x['area'] === $s['area'], ARRAY_FILTER_USE_BOTH);
$jsonld = [
  '@context' => 'https://schema.org', '@type' => 'TouristAttraction',
  'name' => $s['name'] . ', Dahab', 'description' => $s['summary'],
  'geo' => ['@type' => 'GeoCoordinates', 'latitude' => $s['lat'], 'longitude' => $s['lon']],
  'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Dahab', 'addressCountry' => 'EG'],
  'touristType' => array_values(array_filter([$s['freedive'] ? 'Freedivers' : null, $s['scuba'] ? 'Scuba divers' : null])),
];
$foot = '<script src="/assets/dive.js?v=' . @filemtime(ROOT_DIR . '/assets/dive.js') . '" defer></script>';
$bodyClass = 'is-spot';
?>
<section class="spot-hero" style="--hero:url('<?= h(img_src($s['photo'], 1800)) ?>')">
  <div class="wrap">
    <p class="eyebrow"><a href="/en/spots">Spots</a> · <?= h($s['area']) ?></p>
    <h1><?= h($s['name']) ?></h1>
    <p class="lead"><?= h($s['summary']) ?></p>
  </div>
  <p class="photo-credit"><?= h($s['photo_alt']) ?> · <?= unsplash_credit($s['credit']) ?></p>
</section>
<section class="spot-head">
  <div class="wrap">
    <dl class="spotfacts">
      <div><dt>Depth</dt><dd><?= h($s['depth']) ?></dd></div>
      <div><dt>Level</dt><dd><?= h($s['level']) ?></dd></div>
      <div><dt>Good for</dt><dd><?= h(implode(' & ', array_filter([$s['freedive'] ? 'Freediving' : '', $s['scuba'] ? 'Scuba' : '']))) ?></dd></div>
      <div><dt>Right now</dt><dd class="state-<?= h($state[0]) ?>"><span class="dot" aria-hidden="true"></span> <?= h($state[1]) ?> <a class="small" href="/en/today">details</a></dd></div>
    </dl>
    <a class="btn btn-primary dive-start" href="#dive">Start the dive ↓</a>
  </div>
</section>

<section class="dive" id="dive" data-max="<?= $max ?>" data-px="<?= $px ?>" style="--dive-h: calc(<?= $max * $px ?>px + 100vh)" aria-label="Interactive dive down the <?= h($s['name']) ?>. Scroll to descend.">
  <div class="dive-sticky">
    <div class="dive-bg" aria-hidden="true"></div>
    <div class="dive-right">
    <div class="dive-hud">
      <div class="gauge" aria-hidden="true">
        <span class="gauge-n" data-depth>0</span><span class="gauge-u">m</span>
        <span class="gauge-sub" data-level>Surface</span>
        <span class="gauge-time" data-time>Take a breath. Scroll to dive.</span>
      </div>
      <?php if ($teach): ?>
      <div class="dive-teach">
        <p class="small"><b>Who can take you here</b></p>
        <ul data-teach></ul>
      </div>
      <?php endif; ?>
    </div>
    <div class="dive-xsec">
      <?php partial('profile', ['s' => $s, 'id' => 'xsec', 'labels' => true, 'marker' => true, 'line' => $slug === 'blue-hole' ? 30 : 60]); ?>
    </div>
    </div>
    <div class="dive-slot" aria-live="polite"></div>
    <p class="dive-next" aria-hidden="true"></p>
    <div class="dive-progress" aria-hidden="true"><i data-progress></i></div>
  </div>
  <div class="dive-track">
    <?php foreach ($features as [$d, $kind, $ft, $txt]): ?>
      <article class="dive-card k-<?= h($kind) ?>" data-d="<?= (int)$d ?>" style="--dt: <?= (int)$d * $px ?>px">
        <span class="dc-depth"><?= (int)$d ?> m</span>
        <h3><?= h($ft) ?></h3>
        <p><?= h($txt) ?></p>
      </article>
    <?php endforeach; ?>
  </div>
  <script type="application/json" id="teach-data"><?= json_encode($teach, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?></script>
</section>

<div class="wrap spot-body">
  <div class="spot-main">
    <section class="block">
      <h2>About <?= h($s['name']) ?></h2>
      <?php foreach ($s['about'] as $para): ?><p><?= h($para) ?></p><?php endforeach; ?>
      <p><b>Getting in:</b> <?= h($s['entry']) ?></p>
    </section>

    <section class="block safety">
      <h2>Stay safe here</h2>
      <ul><?php foreach ($s['safety'] as $x): ?><li><?= h($x) ?></li><?php endforeach; ?></ul>
    </section>

    <section class="block">
      <div class="sec-head"><h2>Who teaches at <?= h($s['name']) ?></h2><a href="/en/freediving?spot=<?= h($slug) ?>">Filter the list →</a></div>
      <?php if ($here): ?>
        <div class="grid cards">
          <?php foreach ($here as $p) partial('card', ['p' => $p, 'services' => $p['_svc'], 'compact' => true]); ?>
        </div>
      <?php else: ?>
        <p class="muted">No one listed here yet. <a href="/en/join">Teach here? List for free.</a></p>
      <?php endif; ?>
    </section>

    <?php if ($photos): ?>
    <section class="block">
      <h2><?= h($s['name']) ?> through their lenses</h2>
      <div class="gallery">
        <?php foreach ($photos as $m): $src = img_src($m['file'], 900); ?>
          <figure>
            <?php if ($m['kind'] === 'video'): ?><video src="<?= h($src) ?>" controls preload="none" playsinline></video>
            <?php else: ?><img src="<?= h($src) ?>" alt="<?= h($m['caption'] ?: $s['name'] . ', Dahab') ?>" loading="lazy"><?php endif; ?>
            <figcaption><?= h($m['caption']) ?> <a href="/en/p/<?= h($m['slug']) ?>">with <?= h($m['name']) ?></a><?php if ($m['credit']): ?><span class="credit"><?= unsplash_credit($m['credit']) ?></span><?php endif; ?></figcaption>
          </figure>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
  </div>
  <aside class="spot-side">
    <div class="card">
      <h3>Best for</h3>
      <ul class="ticks"><?php foreach ($s['best_for'] as $b): ?><li><?= h($b) ?></li><?php endforeach; ?></ul>
      <p class="muted small">Pin: <?= h(number_format($s['lat'], 4)) ?>, <?= h(number_format($s['lon'], 4)) ?> (approximate) · <a href="https://www.google.com/maps/search/?api=1&query=<?= h($s['lat'] . ',' . $s['lon']) ?>" rel="noopener" target="_blank">Open in Maps</a></p>
    </div>
    <?php if ($others): ?>
    <div class="card">
      <h3>Nearby</h3>
      <ul class="plain">
        <?php foreach ($others as $k => $o): ?><li><a href="/en/spots/<?= h($k) ?>"><?= h($o['name']) ?></a> <span class="muted small"><?= h($o['depth']) ?></span></li><?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </aside>
</div>
