<?php
$fullTitle = ($title ?? '') ? $title . ' · Where To Spot Dive' : 'Where To Spot Dive';
$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$nav = [
  '/en/match' => 'Find my match',
  '/en/freediving' => 'Schools & coaches',
  '/en/spots' => 'Spots',
  '/en/today' => 'Today',
  '/en/prices' => 'Prices',
];
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($fullTitle) ?></title>
<meta name="description" content="<?= h($desc ?? '') ?>">
<?php if (!empty($noindex)): ?><meta name="robots" content="noindex"><?php else: ?>
<link rel="canonical" href="<?= h(abs_url($reqPath)) ?>">
<link rel="alternate" hreflang="en" href="<?= h(abs_url($reqPath)) ?>">
<link rel="alternate" hreflang="x-default" href="<?= h(abs_url($reqPath)) ?>">
<?php endif; ?>
<meta property="og:title" content="<?= h($title ?? 'Where To Spot Dive') ?>">
<meta property="og:description" content="<?= h($desc ?? '') ?>">
<meta property="og:type" content="website">
<meta name="theme-color" content="#0b2545">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/site.css?v=<?= @filemtime(ROOT_DIR . '/assets/site.css') ?>">
<?php if (!empty($head)) echo $head; ?>
<?php if (!empty($jsonld)): ?><script type="application/ld+json"><?= json_encode($jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script><?php endif; ?>
</head>
<body class="<?= h($bodyClass ?? '') ?>">
<a class="skip" href="#main">Skip to content</a>
<header class="topbar">
  <div class="wrap topbar-in">
    <a class="brand" href="/en/" aria-label="Where To Spot Dive, home">
      <svg viewBox="0 0 32 32" width="28" height="28" aria-hidden="true"><circle cx="16" cy="16" r="15" fill="#0e7490"/><path d="M16 6v20" stroke="#a5f3fc" stroke-width="2.4" stroke-linecap="round"/><circle cx="16" cy="19" r="3.6" fill="#ecfeff"/><path d="M7 11c3-2.4 6-2.4 9 0s6 2.4 9 0" fill="none" stroke="#a5f3fc" stroke-width="2.2" stroke-linecap="round"/></svg>
      <span><b>Where To Spot</b> <i>Dive · Dahab</i></span>
    </a>
    <nav class="mainnav" aria-label="Main">
      <?php foreach ($nav as $href => $label): ?>
        <a href="<?= $href ?>"<?= str_starts_with((string)$reqPath, $href) ? ' aria-current="page"' : '' ?>><?= h($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <a class="btn btn-small btn-ghost join-link" href="/en/join">List your school</a>
  </div>
</header>
<main id="main">
<?= $content ?>
</main>
<footer class="footer">
  <div class="wrap footer-in">
    <div>
      <p class="footer-brand"><b>Where To Spot</b> Dive · Dahab</p>
      <p class="muted small">An independent guide to freediving in Dahab. We are a directory, not a dive operator: always check your instructor's qualifications, never dive alone, and train within your limits.</p>
    </div>
    <div class="footer-links">
      <a href="/en/freediving">Schools & coaches</a>
      <a href="/en/prices">Course prices</a>
      <a href="/en/today">Today in Dahab</a>
      <a href="/en/spots">Spots map</a>
      <a href="/en/join">List your school (free)</a>
      <a href="/en/privacy">Privacy & terms</a>
    </div>
  </div>
</footer>
<script src="/assets/app.js?v=<?= @filemtime(ROOT_DIR . '/assets/app.js') ?>" defer></script>
<?php if (!empty($foot)) echo $foot; ?>
</body>
</html>
