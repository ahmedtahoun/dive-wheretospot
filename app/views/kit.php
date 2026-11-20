<?php
$profile = abs_url('/en/p/' . $p['slug']);
$badge = abs_url('/badge/' . $p['slug'] . '.svg');
$widget = abs_url('/widget/today?p=' . $p['slug']);
$badgeCode = '<a href="' . $profile . '"><img src="' . $badge . '" width="240" height="64" alt="' . h($p['name']) . ' on Where To Spot Dive"></a>';
$widgetCode = '<iframe src="' . $widget . '" width="320" height="230" style="border:0;border-radius:14px" title="Today in Dahab" loading="lazy"></iframe>';
$cardData = [
  'name' => $p['name'], 'type' => PROVIDER_TYPES[$p['type']] ?? '', 'note' => $p['week_note'] ?: 'Teaching in Dahab this week',
  'url' => preg_replace('#^https?://#', '', $profile), 'verified' => (bool)$p['verified'],
  'items' => array_map(fn($s) => [$s['title'], money((float)$s['price_eur'])], array_slice(array_values(array_filter($svc, fn($s) => (float)$s['price_eur'] > 0 && $s['category'] !== 'rental')), 0, 4)),
  'spots' => array_map(fn($k) => spot($k)['name'] ?? $k, array_slice(csv_list($p['spots']), 0, 3)),
];
$foot = '<script type="application/json" id="card-data">' . json_encode($cardData, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) . '</script>'
      . '<script src="/assets/kit.js?v=' . @filemtime(ROOT_DIR . '/assets/kit.js') . '" defer></script>';
?>
<section class="pagehead">
  <div class="wrap">
    <p class="eyebrow"><a href="/en/p/<?= h($p['slug']) ?>">← <?= h($p['name']) ?></a></p>
    <h1>Your share kit</h1>
    <p class="lead">Free tools to bring students to your profile. Every click is counted, so you can see what Where To Spot sends you.</p>
  </div>
</section>
<div class="wrap kit">
  <section class="card kit-item">
    <h2>1 · Instagram story card</h2>
    <p class="muted">A 1080×1920 card of your week, ready to post. Add the link sticker with your profile address.</p>
    <canvas id="story" width="1080" height="1920" class="story-canvas" aria-label="Story card preview"></canvas>
    <p><button class="btn btn-primary" type="button" data-download>Download PNG</button> <button class="btn btn-ghost" type="button" data-copy="<?= h($profile) ?>">Copy profile link</button></p>
  </section>
  <section class="card kit-item">
    <h2>2 · Badge for your website</h2>
    <p><img src="/badge/<?= h($p['slug']) ?>.svg" width="240" height="64" alt="Badge preview"></p>
    <label class="code">Paste this into your site <textarea readonly rows="3"><?= h($badgeCode) ?></textarea></label>
    <button class="btn btn-ghost btn-small" type="button" data-copyfrom>Copy code</button>
  </section>
  <section class="card kit-item">
    <h2>3 · Live "Today in Dahab" widget</h2>
    <p class="muted">Wind, water and the calmest spots, updated every 30 minutes, with a button to book with you.</p>
    <iframe src="/widget/today?p=<?= h($p['slug']) ?>" width="320" height="230" style="border:0;border-radius:14px" title="Widget preview" loading="lazy"></iframe>
    <label class="code">Paste this into your site <textarea readonly rows="3"><?= h($widgetCode) ?></textarea></label>
    <button class="btn btn-ghost btn-small" type="button" data-copyfrom>Copy code</button>
  </section>
</div>
