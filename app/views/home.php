<?php
$count = count($providers);
$st = overall_state($c);
$bh = spot('blue-hole');
?>
<section class="hero">
  <div class="hero-waves" aria-hidden="true"></div>
  <div class="wrap hero-in">
    <p class="eyebrow">Dahab · Red Sea · Egypt</p>
    <h1>Every freediving school and coach in Dahab, <em>side by side.</em></h1>
    <p class="lead">Compare courses by price, depth, language and group size. See today's conditions, who is teaching this week, and dive the spots before you get wet.</p>
    <form class="quickfind" action="/en/freediving" method="get" aria-label="Find a school">
      <label><span>I want to</span>
        <select name="level">
          <option value="">see everything</option>
          <option value="0">try freediving</option>
          <option value="1">take a beginner course</option>
          <option value="2">go to ~30 m (intermediate)</option>
          <option value="3">go to ~40 m (advanced)</option>
          <option value="coach">train with a coach</option>
        </select>
      </label>
      <label><span>in</span>
        <select name="lang">
          <option value="">any language</option>
          <?php foreach (LANGS as $l): ?><option><?= h($l) ?></option><?php endforeach; ?>
        </select>
      </label>
      <button class="btn btn-primary" type="submit">Show me</button>
    </form>
    <ul class="hero-stats">
      <li><b><?= $count ?></b> schools, instructors & coaches</li>
      <li><b><?= count($teaching) ?></b> teaching this week</li>
      <li><b><?= count(spots()) ?></b> spots, mapped in depth</li>
    </ul>
  </div>
</section>

<section class="todaybar state-<?= h($st[0]) ?>">
  <div class="wrap todaybar-in">
    <div class="tb-now">
      <span class="dot" aria-hidden="true"></span>
      <b>Right now in Dahab:</b> <?= h($st[1]) ?>
    </div>
    <?php if ($c): ?>
    <ul class="tb-nums">
      <li><span>Wind</span><b><?= (int)$c['wind'] ?> kn</b> <?= h(compass($c['dir'])) ?></li>
      <?php if ($c['water'] !== null): ?><li><span>Water</span><b><?= (int)$c['water'] ?>°C</b></li><?php endif; ?>
      <?php if ($c['wave'] !== null): ?><li><span>Waves</span><b><?= h($c['wave']) ?> m</b></li><?php endif; ?>
    </ul>
    <?php endif; ?>
    <a class="tb-link" href="/en/today">Today's calmest spots →</a>
  </div>
</section>

<section class="wrap section split">
  <div>
    <p class="eyebrow">Interactive spot guide</p>
    <h2>Dive the Blue Hole from your sofa</h2>
    <p>Scroll down and you sink: the depth gauge counts, the water darkens, and you meet the Saddle, the Arch and the depth every course reaches. The schools that train you to each depth appear on the way down.</p>
    <p><a class="btn btn-primary" href="/en/spots/blue-hole">Start the dive ↓</a> <a class="btn btn-ghost" href="/en/spots">All <?= count(spots()) ?> spots</a></p>
  </div>
  <a class="teaser" href="/en/spots/blue-hole" aria-label="Open the Blue Hole interactive guide">
    <?php partial('profile', ['s' => $bh, 'labels' => true, 'line' => 30, 'id' => 'teaser']); ?>
  </a>
</section>

<section class="wrap section">
  <div class="sec-head">
    <h2>Featured in Dahab</h2>
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
      <p class="muted">Instructors tap one link to say they have space this week. Fresh every Monday.</p>
      <ul class="weeklist">
        <?php foreach (array_slice($teaching, 0, 6) as $p): ?>
          <li><a href="/en/p/<?= h($p['slug']) ?>"><b><?= h($p['name']) ?></b></a><span class="muted"><?= h($p['week_note'] ?: (PROVIDER_TYPES[$p['type']] ?? '')) ?></span></li>
        <?php endforeach; ?>
        <?php if (!$teaching): ?><li class="muted">Nobody has updated this week yet.</li><?php endif; ?>
      </ul>
    </div>
    <div>
      <h2>Coming up</h2>
      <p class="muted">Depth camps, beginner weeks, workshops and community sessions.</p>
      <?php partial('events', ['events' => $events]); ?>
      <p><a href="/en/today#events">All events →</a></p>
    </div>
  </div>
</section>

<section class="wrap section ladder-sec">
  <h2>Where are you on the depth ladder?</h2>
  <p class="muted">AIDA, Molchanovs and SSI use different names for the same steps. Here they are lined up.</p>
  <ol class="ladder">
    <?php foreach (LEVELS as $lvl => [$name, $agencies]): if ($lvl === 4) continue; ?>
      <li style="--d:<?= LEVEL_DEPTH[$lvl] ?>">
        <a href="/en/freediving?level=<?= $lvl ?>">
          <span class="ld-depth">~<?= LEVEL_DEPTH[$lvl] ?> m</span>
          <b><?= h($name) ?></b>
          <span class="muted small"><?= h($agencies) ?></span>
          <span class="ld-go">Who teaches this →</span>
        </a>
      </li>
    <?php endforeach; ?>
  </ol>
</section>

<section class="wrap section cta-providers">
  <div>
    <h2>Teach freediving in Dahab?</h2>
    <p>Get listed for free. Inquiries go straight to your WhatsApp, no commission. You also get a badge, a live conditions widget for your own site and an Instagram story card of your week.</p>
  </div>
  <a class="btn btn-primary" href="/en/join">List your school or coaching</a>
</section>
