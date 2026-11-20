<?php
$jsonld = ['@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => 'Freediving courses in Dahab', 'numberOfItems' => count($rows)];
$hasSample = (bool)array_filter($rows, fn($r) => $r['is_sample']);
?>
<section class="pagehead">
  <div class="wrap">
    <p class="eyebrow">Updated <?= h(date('F Y')) ?></p>
    <h1>Every freediving course in Dahab, compared</h1>
    <p class="lead">One table, every school and coach: what each course costs, how many days it takes, how many students share an instructor, and when we last checked the price.</p>
    <?php if ($hasSample): ?><p class="notice warn small">Prototype: these prices come from sample profiles, not real schools yet.</p><?php endif; ?>
  </div>
</section>

<section class="wrap section">
  <div class="statgrid">
    <?php foreach ([1, 2, 3, 0] as $lvl): if (empty($stats[$lvl])) continue; $x = $stats[$lvl]; ?>
      <div class="card stat">
        <p class="muted small"><?= h(LEVELS[$lvl][0]) ?> · ~<?= LEVEL_DEPTH[$lvl] ?> m</p>
        <p class="stat-big"><?= money(round($x['median'])) ?></p>
        <p class="small">typical price · range <?= money($x['min']) ?>–<?= money($x['max']) ?></p>
        <p class="muted small"><?= h(LEVELS[$lvl][1]) ?> · <?= (int)$x['n'] ?> options</p>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="tabs" role="tablist" aria-label="Course level">
    <button role="tab" aria-selected="true" data-lvl="all">All</button>
    <?php foreach (LEVELS as $lvl => [$name]): ?><button role="tab" aria-selected="false" data-lvl="<?= $lvl ?>"><?= h($name) ?></button><?php endforeach; ?>
    <button role="tab" aria-selected="false" data-lvl="coach">Coaching</button>
  </div>

  <div class="cmp-scroll" tabindex="0" aria-label="Price table, scroll sideways on small screens">
  <table class="ptable" data-sortable>
    <thead>
      <tr>
        <th scope="col"><button data-sort="text">School / coach</button></th>
        <th scope="col"><button data-sort="text">Course</button></th>
        <th scope="col"><button data-sort="num">Level</button></th>
        <th scope="col"><button data-sort="num">Days</button></th>
        <th scope="col" aria-sort="ascending"><button data-sort="num">Price</button></th>
        <th scope="col"><button data-sort="num">Per day</button></th>
        <th scope="col"><button data-sort="num">Group</button></th>
        <th scope="col">Pool · Gear</th>
        <th scope="col">Checked</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r):
        $isCoach = $r['category'] === 'coaching';
        $perDay = (float)$r['days'] > 0 ? (float)$r['price_eur'] / max(0.5, (float)$r['days']) : 0; ?>
        <tr data-lvl="<?= $isCoach ? 'coach' : (int)$r['level'] ?>">
          <td data-v="<?= h($r['name']) ?>"><a href="/en/p/<?= h($r['slug']) ?>"><?= h($r['name']) ?></a><?= $r['verified'] ? ' <span class="yes" title="Verified">✓</span>' : '' ?><?= $r['is_sample'] ? ' <span class="badge b-sample">Sample</span>' : '' ?></td>
          <td data-v="<?= h($r['title']) ?>"><?= h($r['title']) ?><?= $r['agency'] ? '<br><span class="muted small">' . h($r['agency']) . '</span>' : '' ?></td>
          <td data-v="<?= $isCoach ? 9 : (int)$r['level'] ?>"><?= $isCoach ? 'Coaching' : h(LEVELS[(int)$r['level']][0]) ?></td>
          <td data-v="<?= (float)$r['days'] ?>"><?= h(rtrim(rtrim(number_format((float)$r['days'], 1), '0'), '.')) ?></td>
          <td data-v="<?= (float)$r['price_eur'] ?>"><b><?= money((float)$r['price_eur']) ?></b></td>
          <td data-v="<?= round($perDay) ?>"><?= $perDay ? money(round($perDay)) : '—' ?></td>
          <td data-v="<?= (int)$r['max_ratio'] ?: 99 ?>"><?= $r['max_ratio'] ? '≤' . (int)$r['max_ratio'] : '—' ?></td>
          <td><?= $r['pool'] ? 'Pool' : '—' ?> · <?= $r['gear_included'] ? 'Gear incl.' : 'Ask' ?></td>
          <td class="muted small"><?= $r['checked_on'] ? h(date('j M', strtotime($r['checked_on']))) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</section>

<section class="wrap section prose">
  <h2>How to read these prices</h2>
  <p><b>Same level, different names.</b> A beginner course is called AIDA 2, Molchanovs Wave 1 or SSI Level 1, depending on the agency. All three take you to about 20 m and are recognised everywhere.</p>
  <p><b>Cheapest isn't always best value.</b> Check the group size: one instructor with two students gives you far more time on the line than one with four. A pool day makes the first session much easier for nervous beginners.</p>
  <p><b>Ask what is included.</b> Most schools include gear, the certificate and the manual in the course price. Blue Hole sessions may add a gate fee and transport.</p>
  <p><b>Prices change.</b> Each row shows when we last checked it with the school. If you see something out of date, tell the school and they can update it.</p>
</section>
