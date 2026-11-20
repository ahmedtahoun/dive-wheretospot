<?php
$pins = [];
foreach (spots() as $k => $s) {
  $st = spot_state($s, $c);
  $pins[] = ['k' => $k, 'n' => $s['name'], 'lat' => $s['lat'], 'lon' => $s['lon'], 'd' => $s['depth'], 'st' => $st[0], 'stl' => $st[1], 'fd' => $s['freedive']];
}
$head = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">';
$foot = '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>'
      . '<script type="application/json" id="pins">' . json_encode($pins, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) . '</script>'
      . '<script src="/assets/map.js?v=' . @filemtime(ROOT_DIR . '/assets/map.js') . '" defer></script>';
?>
<section class="pagehead">
  <div class="wrap">
    <p class="eyebrow">Dahab coast, north to south</p>
    <h1>Dive & freediving spots</h1>
    <p class="lead">Tap a spot to see its depth, how it feels right now and who teaches there. Every spot page has an interactive dive.</p>
  </div>
</section>
<div class="wrap spots-layout">
  <div id="map" class="map" aria-label="Map of Dahab spots"></div>
  <div class="spotlist">
    <?php foreach (spots() as $k => $s): $st = spot_state($s, $c); ?>
      <a class="card spotrow" href="/en/spots/<?= h($k) ?>" data-spot="<?= h($k) ?>">
        <div class="spotrow-x"><img src="<?= h(img_src($s['photo'], 400)) ?>" alt="<?= h($s['photo_alt']) ?>" loading="lazy"></div>
        <div class="spotrow-t">
          <b><?= h($s['name']) ?></b>
          <span class="muted small"><?= h($s['area']) ?> · <?= h($s['depth']) ?></span>
          <span class="small"><?= h($s['summary']) ?></span>
          <span class="spotrow-tags">
            <?php if ($s['freedive']): ?><span class="badge b-plain">Freediving</span><?php endif; ?>
            <?php if ($s['scuba']): ?><span class="badge b-plain">Scuba</span><?php endif; ?>
            <span class="badge state-<?= h($st[0]) ?>"><span class="dot" aria-hidden="true"></span><?= h($st[1]) ?></span>
          </span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<p class="wrap muted small source">Map pins are approximate. Map data © OpenStreetMap contributors.</p>
