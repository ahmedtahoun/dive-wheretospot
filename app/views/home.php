<?php
$count = count($providers);
$st = overall_state($c);
$pick = ['blue-hole', 'lighthouse', 'canyon', 'islands'];
$head = '<link rel="preload" as="image" href="' . h(img_src('photo-1628630500614-1c8924c99c3e', 1600)) . '">';
?>
<section class="hero" style="--hero:url('<?= h(img_src('photo-1628630500614-1c8924c99c3e', 1800)) ?>')">
  <div class="wrap hero-in">
    <p class="eyebrow">Dahab · Red Sea</p>
    <h1>Freediving in Dahab, <em>compared.</em></h1>
    <p class="lead">Every school, instructor and coach in one place, with prices, languages and today's sea conditions.</p>
    <form class="quickfind" action="/en/freediving" method="get" aria-label="Find a school">
      <label><span class="sr-only">I want to</span>
        <select name="level">
          <option value="">What do you want to do?</option>
          <option value="0">Try freediving</option>
          <option value="1">Beginner course (~20 m)</option>
          <option value="2">Intermediate (~30 m)</option>
          <option value="3">Advanced (~40 m)</option>
          <option value="coach">Train with a coach</option>
        </select>
      </label>
      <label><span class="sr-only">Language</span>
        <select name="lang">
          <option value="">Any language</option>
          <?php foreach (LANGS as $l): ?><option><?= h($l) ?></option><?php endforeach; ?>
        </select>
      </label>
      <button class="btn btn-coral" type="submit">Find</button>
    </form>
    <p class="hero-note"><?= $count ?> schools & coaches · <?= count($teaching) ?> teaching this week</p>
  </div>
  <p class="photo-credit"><?= unsplash_credit('sandbarproductions') ?></p>
</section>

<section class="todaybar state-<?= h($st[0]) ?>">
  <div class="wrap todaybar-in">
    <span class="tb-now"><span class="dot" aria-hidden="true"></span><?= h($st[1]) ?></span>
    <?php if ($c): ?>
      <span class="tb-nums"><?= (int)$c['wind'] ?> kn wind · <?= $c['water'] !== null ? (int)$c['water'] . '°C water' : '' ?></span>
    <?php endif; ?>
    <a class="tb-link" href="/en/today">Today in Dahab →</a>
  </div>
</section>

<section class="wrap section">
  <div class="sec-head">
    <div><h2>Pick a spot</h2><p class="muted">Each spot page has an interactive dive: scroll down to descend.</p></div>
    <a href="/en/spots">All <?= count(spots()) ?> spots →</a>
  </div>
  <div class="spotcards">
    <?php foreach ($pick as $k): $s = spot($k); ?>
      <a class="spotcard" href="/en/spots/<?= h($k) ?>">
        <img src="<?= h(img_src($s['photo'], 700)) ?>" alt="<?= h($s['photo_alt']) ?>" loading="lazy">
        <span class="spotcard-t">
          <?php if ($k === 'blue-hole'): ?><span class="tag tag-light">Interactive dive ↓</span><?php endif; ?>
          <b><?= h($s['name']) ?></b>
          <span><?= h($s['depth']) ?> · <?= h(explode(' · ', $s['level'])[0]) ?></span>
        </span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="wrap section pt0">
  <div class="sec-head">
    <h2>Featured schools & coaches</h2>
    <a href="/en/freediving">See all <?= $count ?> →</a>
  </div>
  <div class="grid cards">
    <?php foreach (array_slice($providers, 0, 3) as $p) partial('card', ['p' => $p, 'services' => $svc[(int)$p['id']] ?? [], 'compact' => true]); ?>
  </div>
</section>

<section class="band">
  <div class="wrap section two-col">
    <div>
      <h2>Teaching this week</h2>
      <ul class="weeklist">
        <?php foreach (array_slice($teaching, 0, 4) as $p): ?>
          <li><a href="/en/p/<?= h($p['slug']) ?>"><b><?= h($p['name']) ?></b></a><span class="muted small"><?= h($p['week_note'] ?: (PROVIDER_TYPES[$p['type']] ?? '')) ?></span></li>
        <?php endforeach; ?>
        <?php if (!$teaching): ?><li class="muted">Nobody has updated this week yet.</li><?php endif; ?>
      </ul>
      <?php if (count($teaching) > 4): ?><a class="small" href="/en/freediving?week=1">All <?= count($teaching) ?> →</a><?php endif; ?>
    </div>
    <div>
      <h2>Coming up</h2>
      <?php partial('events', ['events' => array_slice($events, 0, 3)]); ?>
      <a class="small" href="/en/today#events">All events →</a>
    </div>
  </div>
</section>

<section class="wrap section">
  <div class="cta-providers">
    <div>
      <h2>Teach freediving in Dahab?</h2>
      <p>List for free. Inquiries go straight to your WhatsApp, no commission.</p>
    </div>
    <a class="btn btn-coral" href="/en/join">List your school</a>
  </div>
</section>
