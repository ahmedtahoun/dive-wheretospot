<?php
// Cross-section of a spot. $s = spot array. Optional: $id (for JS), $labels (bool), $marker (bool).
$max = (int)$s['profile']['max'];
$top = -6; $hgt = $max + 12;
$pct = fn($d) => round(($d - $top) / $hgt * 100, 3);
$uid = 'g' . substr(md5($s['name'] . ($id ?? '')), 0, 6);
?>
<div class="xsec" <?= !empty($id) ? 'id="' . h($id) . '"' : '' ?> data-max="<?= $max ?>" data-top="<?= $top ?>" data-h="<?= $hgt ?>">
  <svg viewBox="0 <?= $top ?> 100 <?= $hgt ?>" preserveAspectRatio="none" aria-hidden="true">
    <defs>
      <linearGradient id="<?= $uid ?>w" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0" stop-color="#5eead4"/><stop offset=".25" stop-color="#0891b2"/><stop offset=".65" stop-color="#0b3b66"/><stop offset="1" stop-color="#06162b"/>
      </linearGradient>
      <linearGradient id="<?= $uid ?>r" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0" stop-color="#e9d8b4"/><stop offset=".3" stop-color="#9c8a6c"/><stop offset="1" stop-color="#2d2a26"/>
      </linearGradient>
    </defs>
    <rect x="0" y="0" width="100" height="<?= $max + 6 ?>" fill="url(#<?= $uid ?>w)"/>
    <rect x="0" y="<?= $top ?>" width="100" height="6" fill="#e0f2fe"/>
    <?php foreach ($s['profile']['rock'] as $poly): ?>
      <polygon points="<?= h(implode(' ', array_map(fn($pt) => $pt[0] . ',' . $pt[1], $poly))) ?>" fill="url(#<?= $uid ?>r)" stroke="#f5ecd7" stroke-width="1.2" vector-effect="non-scaling-stroke"/>
    <?php endforeach; ?>
    <?php for ($d = 10; $d < $max; $d += 10): ?>
      <line x1="0" x2="100" y1="<?= $d ?>" y2="<?= $d ?>" stroke="#ffffff" stroke-opacity=".12" stroke-dasharray="1 1.4" vector-effect="non-scaling-stroke"/>
    <?php endfor; ?>
    <?php if (!empty($line)): ?>
      <line x1="<?= (float)$line ?>" x2="<?= (float)$line ?>" y1="0" y2="<?= $max ?>" stroke="#fde68a" stroke-width="1.5" vector-effect="non-scaling-stroke"/>
    <?php endif; ?>
  </svg>
  <?php if (!empty($labels)): ?>
    <?php for ($d = 0; $d <= $max; $d += ($max > 60 ? 20 : 10)): ?>
      <span class="xsec-lbl" style="top:<?= $pct($d) ?>%"><?= $d ?> m</span>
    <?php endfor; ?>
  <?php endif; ?>
  <?php if (!empty($marker)): ?>
    <span class="xsec-diver" style="left:<?= (float)($line ?? 30) ?>%;top:<?= $pct(0) ?>%" aria-hidden="true"></span>
  <?php endif; ?>
</div>
