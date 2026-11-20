<?php
$hue = avatar_hue($p['name']);
$cats = [];
foreach ($svc as $s) $cats[$s['category']][] = $s;
$socialLinks = array_filter(SOCIALS, fn($label, $k) => preg_match('#^https?://#', (string)$p[$k]), ARRAY_FILTER_USE_BOTH);
$spotList = array_filter(array_map(fn($k) => ($sp = spot($k)) ? [$k, $sp] : null, csv_list($p['spots'])));
$days = fn($d) => rtrim(rtrim(number_format((float)$d, 1), '0'), '.');
$jsonld = [
  '@context' => 'https://schema.org', '@type' => ['LocalBusiness', 'SportsActivityLocation'],
  'name' => $p['name'], 'description' => $p['tagline'] ?: mb_substr($p['bio'], 0, 160),
  'url' => abs_url('/en/p/' . $p['slug']),
  'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Dahab', 'addressRegion' => 'South Sinai', 'addressCountry' => 'EG'],
  'knowsLanguage' => csv_list($p['languages']),
  'sameAs' => array_values(array_map(fn($k) => $p[$k], array_keys($socialLinks))),
];
?>
<section class="phero" style="--h:<?= $hue ?>">
  <div class="wrap phero-in">
    <div class="avatar xl" style="--h:<?= $hue ?>" aria-hidden="true"><?= h(initials($p['name'])) ?></div>
    <div class="phero-text">
      <p class="eyebrow"><a href="/en/freediving">Schools & coaches</a> · <?= h(PROVIDER_TYPES[$p['type']] ?? '') ?><?= $p['base_area'] ? ' · ' . h($p['base_area']) : '' ?></p>
      <h1><?= h($p['name']) ?></h1>
      <?php if ($p['tagline']): ?><p class="lead"><?= h($p['tagline']) ?></p><?php endif; ?>
      <div class="badges">
        <?php if ($p['status'] !== 'approved'): ?><span class="badge b-warn">Not public yet (<?= h($p['status']) ?>)</span><?php endif; ?>
        <?php if ($p['is_sample']): ?><span class="badge b-sample" title="Fictional profile for the prototype">Sample profile</span><?php endif; ?>
        <?php if ($p['verified']): ?><span class="badge b-ok" title="<?= h($p['verified_note'] ?: 'Certificate checked on the agency\'s instructor search') ?>">✓ Verified certificates</span><?php endif; ?>
        <?php if ($p['founding']): ?><span class="badge b-found">Founding member</span><?php endif; ?>
        <span class="badge b-plain"><?= h(LICENCE[$p['licence']] ?? '') ?><?= $p['works_with'] ? ' · with ' . h($p['works_with']) : '' ?></span>
      </div>
    </div>
  </div>
</section>

<div class="wrap profile">
  <div class="profile-main">
    <?php if ($p['teaching_week']): ?>
      <div class="weeknote"><span class="dot" aria-hidden="true"></span><b>Teaching this week</b><?= $p['week_note'] ? ' · ' . h($p['week_note']) : '' ?></div>
    <?php endif; ?>

    <dl class="statrow">
      <?php if ($p['personal_best']): ?><div><dt>Personal best</dt><dd><?= h($p['personal_best']) ?></dd></div><?php endif; ?>
      <?php if ($p['years_teaching']): ?><div><dt>Teaching</dt><dd><?= (int)$p['years_teaching'] ?> years</dd></div><?php endif; ?>
      <?php if ($p['students_certified']): ?><div><dt>Students</dt><dd><?= number_format((int)$p['students_certified']) ?>+</dd></div><?php endif; ?>
      <?php if ($p['max_ratio']): ?><div><dt>Group size</dt><dd>max <?= (int)$p['max_ratio'] ?></dd></div><?php endif; ?>
      <div><dt>Pool</dt><dd><?= $p['pool'] ? 'Yes' : 'No' ?></dd></div>
    </dl>

    <section class="block" id="prices">
      <h2>Courses & prices</h2>
      <?php if (!$svc): ?><p class="muted">Prices coming soon. Ask on WhatsApp.</p><?php endif; ?>
      <?php foreach (SERVICE_CATS as $ck => $clabel): if (empty($cats[$ck])) continue; ?>
        <h3 class="svc-cat"><?= h($clabel) ?></h3>
        <table class="svc">
          <tbody>
          <?php foreach ($cats[$ck] as $s): ?>
            <tr>
              <td>
                <b><?= h($s['title']) ?></b>
                <?php
                  $meta = [];
                  if ($s['agency']) $meta[] = h($s['agency']);
                  if ($ck === 'course') $meta[] = h(LEVELS[(int)$s['level']][0]) . ' · ~' . LEVEL_DEPTH[(int)$s['level']] . ' m';
                  $dd = (float)$s['days'];
                  if ($dd > 0) $meta[] = $dd < 1 ? 'Half day' : $days($dd) . ($dd == 1 ? ' day' : ' days');
                  if ($s['notes']) $meta[] = h($s['notes']);
                ?>
                <span class="muted small"><?= implode(' · ', $meta) ?></span>
              </td>
              <td class="price"><?= money((float)$s['price_eur']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endforeach; ?>
      <p class="muted small"><?= $p['checked_on'] ? 'Prices checked with ' . h($p['name']) . ' on ' . h(date('j M Y', strtotime($p['checked_on']))) . '.' : '' ?> <?= $p['gear_included'] ? 'Gear included in courses.' : 'Ask whether gear is included.' ?></p>
    </section>

    <?php if ($p['bio']): ?>
    <section class="block">
      <h2>About</h2>
      <?php foreach (preg_split('/\n\s*\n/', trim($p['bio'])) as $para): ?><p><?= nl2br(h($para)) ?></p><?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (trim($p['achievements'])): ?>
    <section class="block">
      <h2>Experience & achievements</h2>
      <ul class="ticks"><?php foreach (preg_split('/\r?\n/', trim($p['achievements'])) as $a): if (trim($a) === '') continue; ?><li><?= h($a) ?></li><?php endforeach; ?></ul>
    </section>
    <?php endif; ?>

    <section class="block">
      <h2>Their work</h2>
      <?php $links = array_filter(preg_split('/\s+/', (string)$p['work_links'])); if ($links): ?>
        <div class="worklinks">
          <?php foreach ($links as $l):
            $host = strtolower((string)parse_url($l, PHP_URL_HOST));
            $yt = null;
            if (preg_match('#(?:youtube\.com/(?:watch\?v=|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})#', $l, $mm)) $yt = $mm[1];
            $label = str_contains($host, 'instagram') ? 'Instagram' : (str_contains($host, 'tiktok') ? 'TikTok' : ($yt || str_contains($host, 'youtu') ? 'YouTube' : preg_replace('/^www\./', '', $host)));
          ?>
            <?php if ($yt): ?>
              <button type="button" class="ytcard" data-yt="<?= h($yt) ?>" style="background-image:url('https://i.ytimg.com/vi/<?= h($yt) ?>/hqdefault.jpg')" aria-label="Play YouTube video"><span>▶</span></button>
            <?php else: ?>
              <a class="workcard" href="<?= h($l) ?>" target="_blank" rel="nofollow noopener"><b><?= h($label) ?></b><span class="small muted">See the post ↗</span></a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if ($media): ?>
        <div class="gallery">
          <?php foreach ($media as $m): $src = cfg('uploads_url') . '/' . rawurlencode($m['file']); ?>
            <figure>
              <?php if ($m['kind'] === 'video'): ?>
                <video src="<?= h($src) ?>" controls preload="none" playsinline></video>
              <?php else: ?>
                <img src="<?= h($src) ?>" alt="<?= h($m['caption'] ?: $p['name'] . ' freediving in Dahab') ?>" loading="lazy">
              <?php endif; ?>
              <?php if ($m['caption'] || $m['spot']): ?><figcaption><?= h($m['caption']) ?><?php if ($m['spot'] && spot($m['spot'])): ?> <a href="/en/spots/<?= h($m['spot']) ?>">@ <?= h(spot($m['spot'])['name']) ?></a><?php endif; ?></figcaption><?php endif; ?>
            </figure>
          <?php endforeach; ?>
        </div>
      <?php elseif (!$links): ?>
        <p class="muted">No photos yet<?= $socialLinks ? ': see their work on ' . implode(' and ', array_map(fn($k) => '<a href="/go/' . $k . '/' . h($p['slug']) . '" rel="nofollow">' . SOCIALS[$k] . '</a>', array_keys($socialLinks))) : '' ?>.</p>
      <?php endif; ?>
    </section>

    <?php if ($spotList): ?>
    <section class="block">
      <h2>Where they dive</h2>
      <div class="spotchips">
        <?php foreach ($spotList as [$k, $sp]): ?><a class="spotchip" href="/en/spots/<?= h($k) ?>"><b><?= h($sp['name']) ?></b><span class="muted small"><?= h($sp['depth']) ?></span></a><?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($events): ?>
    <section class="block"><h2>Upcoming</h2><?php partial('events', ['events' => $events]); ?></section>
    <?php endif; ?>

    <section class="block" id="inquire">
      <h2>Ask <?= h($p['name']) ?></h2>
      <?php if ($sent && preg_match('/^D[A-F0-9]{5}$/', (string)$sent)): ?>
        <div class="notice ok" role="status">
          <b>Sent. Your reference is <?= h($sent) ?>.</b>
          <p>We've passed your request to <?= h($p['name']) ?>. Want a faster answer? Send them the reference on WhatsApp too.</p>
          <?php if ($p['whatsapp']): ?><a class="btn btn-wa" href="<?= h(wa_link($p['whatsapp'], "Hi {$p['name']}, I sent an inquiry on dive.wheretospot.com (ref $sent).")) ?>" rel="nofollow">Message on WhatsApp</a><?php endif; ?>
        </div>
      <?php else: ?>
        <?php if (!empty($_GET['err'])): ?><div class="notice err" role="alert">Please add your name and an email or WhatsApp number so they can reply.</div><?php endif; ?>
        <form class="form" method="post" action="/en/p/<?= h($p['slug']) ?>/inquire">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <div class="hp" aria-hidden="true"><label>Leave empty <input name="website_url" tabindex="-1" autocomplete="off"></label></div>
          <div class="row2">
            <label>Your name <input name="name" required maxlength="80" autocomplete="name"></label>
            <label>Email <input name="email" type="email" maxlength="120" autocomplete="email"></label>
          </div>
          <div class="row2">
            <label>WhatsApp <span class="muted small">(with country code)</span> <input name="whatsapp" inputmode="tel" maxlength="30" autocomplete="tel"></label>
            <label>Dates <input name="dates" maxlength="80" placeholder="e.g. 12–16 March"></label>
          </div>
          <div class="row2">
            <label>Your level
              <select name="level"><option>Never freedived</option><option>Tried it once</option><option>Beginner certificate</option><option>Intermediate certificate</option><option>Advanced / training</option></select>
            </label>
            <label>People <input name="people" type="number" min="1" max="20" value="1"></label>
          </div>
          <label>Message <textarea name="message" rows="3" maxlength="2000" placeholder="What would you like to do?"></textarea></label>
          <p class="muted small">We share these details only with <?= h($p['name']) ?> so they can reply. <a href="/en/privacy">Privacy</a></p>
          <button class="btn btn-primary" type="submit">Send inquiry</button>
        </form>
      <?php endif; ?>
    </section>
  </div>

  <aside class="profile-side">
    <div class="contact-card">
      <?php $beg = course_price($svc, 1); if ($beg): ?><p class="muted small">Beginner course from</p><p class="bigprice"><?= money((float)$beg['price_eur']) ?></p><?php endif; ?>
      <?php if ($p['whatsapp']): ?><a class="btn btn-wa btn-block" href="/go/whatsapp/<?= h($p['slug']) ?>" rel="nofollow">Message on WhatsApp</a><?php endif; ?>
      <a class="btn btn-ghost btn-block" href="#inquire">Send an inquiry</a>
      <dl class="side-facts">
        <div><dt>Languages</dt><dd><?= h(str_replace(',', ', ', $p['languages']) ?: '—') ?></dd></div>
        <div><dt>Agencies</dt><dd><?= h(str_replace(',', ' · ', $p['agencies']) ?: '—') ?></dd></div>
      </dl>
      <?php if ($socialLinks): ?>
        <div class="socials">
          <?php foreach ($socialLinks as $k => $label): ?><a href="/go/<?= h($k) ?>/<?= h($p['slug']) ?>" rel="nofollow noopener" target="_blank"><?= h($label) ?></a><?php endforeach; ?>
        </div>
      <?php endif; ?>
      <p class="muted small side-note">Mention Where To Spot when you book.</p>
    </div>
    <p class="small center"><a href="/en/p/<?= h($p['slug']) ?>/kit">Is this you? Get your share kit →</a></p>
  </aside>
</div>
