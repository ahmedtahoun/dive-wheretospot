<?php
$tok = h(csrf_token());
$photos = array_filter($media, fn($m) => $m['status'] === 'approved');
$priced = array_filter($svc, fn($s) => (float)$s['price_eur'] > 0);
$checks = [
  ['One line about you', $p['tagline'] !== '', '#profile'],
  ['About you (at least a few sentences)', mb_strlen($p['bio']) >= 80, '#profile'],
  ['Courses with prices', count($priced) > 0, '#courses'],
  ['Languages and agencies', $p['languages'] !== '' && $p['agencies'] !== '', '#profile'],
  ['Where you teach', $p['spots'] !== '', '#profile'],
  ['What you\'re best at (specialties)', $p['specialties'] !== '', '#skills'],
  ['Instructor number', $p['cert_number'] !== '', '#cert'],
  ['At least 3 photos or videos', count($media) >= 3, '#media'],
  ['Instagram, TikTok, YouTube or website', (bool)($p['instagram'] || $p['tiktok'] || $p['youtube'] || $p['website']), '#social'],
];
$done = count(array_filter($checks, fn($c) => $c[1]));
$pct = (int)round($done / count($checks) * 100);
$msgs = [
  'saved' => 'Saved. Your profile is updated.', 'week' => 'Weekly status updated.', 'photo' => 'Uploaded. It is on your profile now.',
  'photo_pending' => 'Uploaded. It shows on your profile after a quick check by our team.', 'deleted' => 'Deleted.',
  'cert' => 'Thanks. We will check it and switch on your Verified badge.', 'event' => 'Event added.', 'event_deleted' => 'Event removed.',
];
$weekFresh = $p['teaching_week'] && $p['week_updated'];
?>
<section class="mehead">
  <div class="wrap">
    <p class="eyebrow">Your private page · keep this link to yourself</p>
    <h1>Hi <?= h($p['name']) ?></h1>
    <div class="badges">
      <?php if ($p['status'] === 'approved'): ?>
        <span class="badge b-ok">● Live</span> <a class="small" href="/en/p/<?= h($p['slug']) ?>" target="_blank">See your public profile ↗</a>
      <?php elseif ($p['status'] === 'pending'): ?>
        <span class="badge b-found">Waiting for approval</span> <span class="small muted">Finish your profile below, and it goes live as soon as we approve it.</span>
      <?php else: ?>
        <span class="badge b-warn">Not listed</span> <span class="small muted">Message us on WhatsApp if you think this is a mistake.</span>
      <?php endif; ?>
      <?php if ($p['verified']): ?><span class="badge b-ok">✓ Verified</span><?php endif; ?>
    </div>
    <p class="small muted">Tip: bookmark this page or add it to your home screen. It's your login: no password needed.</p>
  </div>
</section>

<div class="wrap me">
  <?php if (isset($msgs[$msg])): ?><div class="notice ok" role="status"><?= h($msgs[$msg]) ?></div><?php endif; ?>
  <?php if ($err !== ''): ?><div class="notice err" role="alert"><?= h($err) ?></div><?php endif; ?>

  <div class="me-top">
    <section class="card" id="week">
      <h2>Teaching this week?</h2>
      <p class="small muted"><?= $weekFresh ? 'Showing until ' . h(date('D j M', strtotime($p['week_updated'] . ' +7 days'))) . '.' : 'Turn it on when you have space. It switches off by itself after 7 days.' ?></p>
      <form method="post" action="<?= h($base) ?>/week" class="form">
        <input type="hidden" name="csrf" value="<?= $tok ?>"><input type="hidden" name="from" value="dash">
        <input name="week_note" maxlength="120" value="<?= h($p['week_note']) ?>" placeholder="e.g. Beginner course Monday, 2 spots left" aria-label="Note for this week">
        <div class="actions">
          <button class="btn <?= $p['teaching_week'] ? 'btn-ghost' : 'btn-primary' ?>" name="on" value="1"><?= $p['teaching_week'] ? 'Update note' : 'Yes, I have space' ?></button>
          <?php if ($p['teaching_week']): ?><button class="btn btn-ghost" name="on" value="0">Turn off</button><?php endif; ?>
        </div>
      </form>
    </section>
    <section class="card">
      <h2>Last 30 days</h2>
      <dl class="mestats">
        <div><dt>Profile views</dt><dd><?= (int)$stats['view'] ?></dd></div>
        <div><dt>WhatsApp taps</dt><dd><?= (int)$stats['whatsapp'] ?></dd></div>
        <div><dt>Social & website taps</dt><dd><?= (int)$stats['social'] ?></dd></div>
        <div><dt>Inquiries</dt><dd><?= (int)$stats['inquiries'] ?></dd></div>
      </dl>
    </section>
    <section class="card">
      <h2>Profile strength · <?= $pct ?>%</h2>
      <div class="meter" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"><i style="width:<?= $pct ?>%"></i></div>
      <ul class="todo">
        <?php foreach ($checks as [$label, $ok, $href]): ?><li class="<?= $ok ? 'ok' : '' ?>"><a href="<?= $href ?>"><?= h($label) ?></a></li><?php endforeach; ?>
      </ul>
    </section>
  </div>

  <?php if ($inq): ?>
  <section class="card" id="inquiries">
    <h2>Inquiries</h2>
    <ul class="inqlist">
      <?php foreach ($inq as $i): ?>
        <li>
          <b><?= h($i['name']) ?></b> <span class="muted small"><?= h($i['ref']) ?> · <?= h(date('j M', strtotime($i['created_at']))) ?></span><br>
          <span class="small"><?= h($i['dates']) ?><?= $i['dates'] ? ' · ' : '' ?><?= h($i['level']) ?> · <?= (int)$i['people'] ?> pax</span>
          <?php if ($i['message']): ?><p class="small"><?= nl2br(h($i['message'])) ?></p><?php endif; ?>
          <span class="actions">
            <?php if ($i['whatsapp']): ?><a class="btn btn-small btn-wa" target="_blank" rel="noopener" href="<?= h(wa_link($i['whatsapp'], "Hi {$i['name']}, this is {$p['name']} from Dahab. Thanks for your message on Where To Spot (ref {$i['ref']}).")) ?>">Reply on WhatsApp</a><?php endif; ?>
            <?php if ($i['email']): ?><a class="btn btn-small btn-ghost" href="mailto:<?= h($i['email']) ?>?subject=<?= rawurlencode('Freediving in Dahab · ref ' . $i['ref']) ?>">Reply by email</a><?php endif; ?>
          </span>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <form class="form card" method="post" action="<?= h($base) ?>/save">
    <input type="hidden" name="csrf" value="<?= $tok ?>">
    <?php partial('pform', ['p' => $p, 'svc' => $svc, 'admin' => false]); ?>
    <div class="savebar"><button class="btn btn-primary" type="submit">Save profile</button><span class="muted small">Saving also confirms your prices are up to date.</span></div>
  </form>

  <section class="card" id="cardphoto">
    <h2>Photo of your instructor card <span class="muted small">(optional, private)</span></h2>
    <p class="muted small">Only our team sees this. It speeds up your Verified badge.<?= $p['cert_file'] ? ' <b>Received ✓</b>' : '' ?></p>
    <form class="form inline-form" method="post" action="<?= h($base) ?>/cert" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= $tok ?>">
      <input type="file" name="card" accept="image/jpeg,image/png,image/webp" required>
      <button class="btn btn-small btn-ghost">Send card photo</button>
    </form>
  </section>

  <section class="card" id="media">
    <h2>Photos & videos</h2>
    <p class="muted small">Show your students, your sessions and the spots. Tag the spot and your photo also appears on that spot's page. Only post photos you took, with the consent of the people in them, and nothing unsafe (no solo diving).<?= ($p['verified'] && $p['status'] === 'approved') ? '' : ' New uploads show after a quick check.' ?></p>
    <form class="form" method="post" action="<?= h($base) ?>/media" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= $tok ?>">
      <div class="row3">
        <label>Photo or video <span class="muted small">(JPG/PNG ≤12 MB, MP4 ≤40 MB)</span><input type="file" name="file" accept="image/jpeg,image/png,image/webp,video/mp4" required></label>
        <label>Taken at <select name="spot"><option value="">— no spot —</option><?php foreach (spots() as $k => $s): ?><option value="<?= $k ?>"><?= h($s['name']) ?></option><?php endforeach; ?></select></label>
        <label>Caption <input name="caption" maxlength="140"></label>
      </div>
      <button class="btn btn-primary btn-small">Upload</button>
    </form>
    <?php if ($media): ?>
    <div class="gallery">
      <?php foreach ($media as $m): $src = img_src($m['file'], 900); ?>
        <figure class="<?= $m['status'] === 'pending' ? 'is-pending' : '' ?>">
          <?php if ($m['kind'] === 'video'): ?><video src="<?= h($src) ?>" controls preload="none"></video><?php else: ?><img src="<?= h($src) ?>" alt="" loading="lazy"><?php endif; ?>
          <figcaption class="small"><?= h($m['caption']) ?> <?= $m['spot'] && spot($m['spot']) ? '@ ' . h(spot($m['spot'])['name']) : '' ?>
            <?= $m['status'] === 'pending' ? '<b>· waiting for check</b>' : '' ?>
            <form method="post" action="<?= h($base) ?>/media/<?= (int)$m['id'] ?>/delete" class="inline" onsubmit="return confirm('Delete this?')"><input type="hidden" name="csrf" value="<?= $tok ?>"><button class="linkbtn">Delete</button></form>
          </figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>

  <section class="card" id="events">
    <h2>Your events & camps</h2>
    <p class="muted small">Training weeks, depth camps, workshops. They show on your profile, the home page and "Today in Dahab".</p>
    <?php if ($events): ?><?php foreach ($events as $e): ?>
      <p class="small"><b><?= h($e['title']) ?></b> · <?= h(date('j M', strtotime($e['starts_on']))) ?><?= $e['ends_on'] ? '–' . h(date('j M', strtotime($e['ends_on']))) : '' ?>
        <form method="post" action="<?= h($base) ?>/event/<?= (int)$e['id'] ?>/delete" class="inline" onsubmit="return confirm('Remove this event?')"><input type="hidden" name="csrf" value="<?= $tok ?>"><button class="linkbtn">Remove</button></form></p>
    <?php endforeach; ?><?php endif; ?>
    <form class="form" method="post" action="<?= h($base) ?>/event">
      <input type="hidden" name="csrf" value="<?= $tok ?>">
      <div class="row3">
        <label>Title <input name="title" maxlength="100" required placeholder="e.g. Blue Hole depth camp"></label>
        <label>Starts <input type="date" name="starts_on" required min="<?= date('Y-m-d') ?>"></label>
        <label>Ends <span class="muted small">(optional)</span><input type="date" name="ends_on" min="<?= date('Y-m-d') ?>"></label>
      </div>
      <div class="row2">
        <label>Type <select name="kind"><?php foreach (['camp' => 'Camp', 'course' => 'Course week', 'workshop' => 'Workshop', 'competition' => 'Competition', 'social' => 'Social', 'trip' => 'Trip'] as $k => $l): ?><option value="<?= $k ?>"><?= $l ?></option><?php endforeach; ?></select></label>
        <label>Short description <input name="description" maxlength="300"></label>
      </div>
      <button class="btn btn-ghost btn-small">Add event</button>
    </form>
  </section>

  <section class="card">
    <h2>Bring students to your profile</h2>
    <p class="muted small">Story card for Instagram, a badge and a live conditions box for your own website.</p>
    <?php if ($p['status'] === 'approved'): ?><a class="btn btn-ghost btn-small" href="/en/p/<?= h($p['slug']) ?>/kit" target="_blank">Open your share kit ↗</a><?php else: ?><p class="small muted">Available once your profile is live.</p><?php endif; ?>
  </section>
</div>
