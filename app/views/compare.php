<section class="pagehead">
  <div class="wrap">
    <p class="eyebrow"><a href="/en/freediving">← All schools & coaches</a></p>
    <h1>Side by side</h1>
    <?php if (count($list) < 2): ?>
      <p class="lead">Pick at least two schools or coaches to compare. <a href="/en/freediving">Go to the list</a> and tick "Compare" on the ones you like.</p>
    <?php else: ?>
      <p class="lead">The best value in each row is highlighted. Prices in euros, as each provider told us.</p>
    <?php endif; ?>
  </div>
</section>
<?php if (count($list) >= 1):
  // Build rows: [label, fn(p) => [display, sortValue|null, lowerIsBetter]]
  $rows = [
    ['Type', fn($p) => [h(PROVIDER_TYPES[$p['type']] ?? ''), null]],
    ['Status', fn($p) => [h(LICENCE[$p['licence']] ?? '') . ($p['works_with'] ? '<br><span class="muted small">with ' . h($p['works_with']) . '</span>' : ''), null]],
    ['Verified certificates', fn($p) => [$p['verified'] ? '<span class="yes">✓ Yes</span>' : '<span class="muted">Not yet</span>', $p['verified'] ? 0 : 1]],
    ['Agencies', fn($p) => [h(str_replace(',', ' · ', $p['agencies'])), null]],
    ['Languages', fn($p) => [h(str_replace(',', ', ', $p['languages'])), null]],
  ];
  foreach ([0, 1, 2, 3] as $lvl) {
    $rows[] = [LEVELS[$lvl][0] . ' course', function ($p) use ($lvl) {
      $c = course_price($p['_svc'], $lvl);
      return $c ? ['<b>' . money((float)$c['price_eur']) . '</b><br><span class="muted small">' . h($c['title']) . ' · ' . h(rtrim(rtrim(number_format((float)$c['days'], 1), '0'), '.')) . ' d</span>', (float)$c['price_eur']] : ['<span class="muted">—</span>', null];
    }];
  }
  $rows[] = ['Coached session', function ($p) {
    $best = null; foreach ($p['_svc'] as $s) if ($s['category'] === 'coaching' && (float)$s['days'] <= 1 && (!$best || $s['price_eur'] < $best['price_eur'])) $best = $s;
    return $best ? ['<b>' . money((float)$best['price_eur']) . '</b><br><span class="muted small">' . h($best['title']) . '</span>', (float)$best['price_eur']] : ['<span class="muted">—</span>', null];
  }];
  $rows[] = ['Best for', fn($p) => [h(implode(', ', array_map(fn($k) => SPECIALTIES[$k][0] ?? $k, csv_list($p['specialties'])))) ?: '—', null]];
  $rows[] = ['Teaching style', fn($p) => [h(implode(' · ', array_map(fn($k) => STYLES[$k] ?? $k, csv_list($p['styles'])))) ?: '—', null]];
  $rows[] = ['Teaches', fn($p) => [h(implode(' · ', array_map(fn($k) => FORMATS[$k] ?? $k, csv_list($p['formats'])))) ?: '—', null]];
  $rows[] = ['In Dahab', fn($p) => [h(months_label($p['months'])), null]];
  $rows[] = ['Max students per instructor', fn($p) => [$p['max_ratio'] ? (int)$p['max_ratio'] : '—', $p['max_ratio'] ? (int)$p['max_ratio'] : null]];
  $rows[] = ['Training pool', fn($p) => [$p['pool'] ? 'Yes' : 'No', null]];
  $rows[] = ['Gear included in courses', fn($p) => [$p['gear_included'] ? 'Yes' : 'Ask', null]];
  $rows[] = ['Spots', fn($p) => [h(implode(', ', array_map(fn($s) => spot($s)['name'] ?? $s, csv_list($p['spots'])))), null]];
  $rows[] = ['Years teaching', fn($p) => [$p['years_teaching'] ?: '—', null]];
  $rows[] = ['Prices checked', fn($p) => [$p['checked_on'] ? h(date('j M Y', strtotime($p['checked_on']))) : '—', null]];
?>
<div class="wrap section">
  <div class="cmp-scroll" tabindex="0" aria-label="Comparison table, scroll sideways on small screens">
  <table class="cmp-table">
    <thead>
      <tr><th scope="col"><span class="sr-only">Detail</span></th>
        <?php foreach ($list as $p): ?>
          <th scope="col">
            <div class="avatar sm" style="--h:<?= avatar_hue($p['name']) ?>" aria-hidden="true"><?= h(initials($p['name'])) ?></div>
            <a href="/en/p/<?= h($p['slug']) ?>"><?= h($p['name']) ?></a>
            <?php if ($p['is_sample']): ?><span class="badge b-sample">Sample</span><?php endif; ?>
          </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as [$label, $fn]):
        $cells = array_map($fn, $list);
        $vals = array_filter(array_map(fn($c) => $c[1] ?? null, $cells), fn($v) => $v !== null);
        $best = count($vals) > 1 && count(array_unique($vals)) > 1 ? min($vals) : null; ?>
        <tr><th scope="row"><?= h($label) ?></th>
          <?php foreach ($cells as $cell): ?><td<?= $best !== null && ($cell[1] ?? null) === $best ? ' class="best"' : '' ?>><?= $cell[0] ?></td><?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
      <tr><th scope="row">Contact</th>
        <?php foreach ($list as $p): ?><td><a class="btn btn-small btn-wa" href="/go/whatsapp/<?= h($p['slug']) ?>" rel="nofollow">WhatsApp</a> <a class="btn btn-small btn-ghost" href="/en/p/<?= h($p['slug']) ?>#inquire">Ask</a></td><?php endforeach; ?>
      </tr>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>
