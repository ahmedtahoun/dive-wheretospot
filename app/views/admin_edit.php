<?php
partial('adminnav');
$tok = h(csrf_token());
?>
<section class="wrap section">
  <p class="small"><a href="/admin/">← Providers</a></p>
  <div class="sec-head">
    <h1 class="h2"><?= h($p['name']) ?> <span class="badge st-<?= h($p['status']) ?>"><?= h($p['status']) ?></span><?= $p['is_sample'] ? ' <span class="badge b-sample">sample</span>' : '' ?></h1>
    <form method="post" action="/admin/p/<?= (int)$p['id'] ?>/action" class="actions">
      <input type="hidden" name="csrf" value="<?= $tok ?>"><input type="hidden" name="back" value="/admin/p/<?= (int)$p['id'] ?>">
      <?php if ($p['status'] !== 'approved'): ?><button name="a" value="approve" class="btn btn-small btn-primary">Approve & publish</button><?php else: ?><button name="a" value="pending" class="btn btn-small btn-ghost">Unpublish</button><?php endif; ?>
      <button name="a" value="<?= $p['verified'] ? 'unverify' : 'verify' ?>" class="btn btn-small btn-ghost"><?= $p['verified'] ? 'Remove verified' : 'Mark verified' ?></button>
      <button name="a" value="feature" class="btn btn-small btn-ghost"><?= $p['featured'] ? 'Unfeature' : 'Feature' ?></button>
      <button name="a" value="founding" class="btn btn-small btn-ghost"><?= $p['founding'] ? 'Not founding' : 'Founding member' ?></button>
      <a class="btn btn-small btn-ghost" href="/en/p/<?= h($p['slug']) ?>" target="_blank">Preview</a>
      <button name="a" value="delete" class="btn btn-small btn-danger" onclick="return confirm('Delete <?= h($p['name']) ?> for good?')">Delete</button>
    </form>
  </div>
  <?php if ($msg): ?><div class="notice <?= $msg === 'Saved' || $msg === 'Uploaded' ? 'ok' : 'err' ?>" role="status"><?= h($msg) ?></div><?php endif; ?>
  <?php if ($p['submitted_courses']): ?><div class="notice warn"><b>Courses they sent:</b><pre><?= h($p['submitted_courses']) ?></pre>Type them into the courses table below.</div><?php endif; ?>

  <div class="card linkcard" id="link">
    <h2>Their private page</h2>
    <p class="muted small">Anyone with this link can edit this profile, so send it only to them. They use it to edit everything, upload photos and set "teaching this week".</p>
    <input class="linkbox" readonly value="<?= h(me_url($p)) ?>" onclick="this.select()">
    <div class="actions">
      <?php if ($p['whatsapp']): ?><a class="btn btn-small btn-wa" target="_blank" rel="noopener" href="<?= h(wa_link($p['whatsapp'], "Hi {$p['name']}, here is your private link to edit your Where To Spot Dive profile, add photos and set your weekly status (keep it private): " . me_url($p))) ?>">Send on WhatsApp</a><?php endif; ?>
      <a class="btn btn-small btn-ghost" href="<?= h(me_url($p)) ?>" target="_blank">Open it</a>
      <form method="post" action="/admin/p/<?= (int)$p['id'] ?>/token" class="inline" onsubmit="return confirm('Make a new link? The old one stops working.')"><input type="hidden" name="csrf" value="<?= $tok ?>"><button class="btn btn-small btn-ghost">Make a new link</button></form>
    </div>
    <?php if ($p['cert_number'] || $p['cert_file']): ?>
      <p class="small"><b>Certificate claimed:</b> <?= h($p['cert_agency']) ?> <?= h($p['cert_number']) ?>
        <?php if ($p['cert_file']): ?> · <a href="/admin/cert/<?= (int)$p['id'] ?>" target="_blank">See card photo</a><?php endif; ?>
        <?= $p['verified'] ? ' · <span class="yes">✓ verified</span>' : ' · <b>not verified yet</b>' ?></p>
    <?php endif; ?>
  </div>

  <form class="form card" method="post" action="/admin/p/<?= (int)$p['id'] ?>">
    <input type="hidden" name="csrf" value="<?= $tok ?>">
    <?php partial('pform', ['p' => $p, 'svc' => $svc, 'admin' => true]); ?>
    <button class="btn btn-primary" type="submit">Save profile</button>
  </form>

  <div class="card" id="media">
    <h2>Photos & videos</h2>
    <form class="form" method="post" action="/admin/p/<?= (int)$p['id'] ?>/media" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= $tok ?>">
      <div class="row3">
        <label>File <span class="muted small">(JPG/PNG/WebP ≤12 MB, MP4 ≤40 MB)</span><input type="file" name="file" accept="image/jpeg,image/png,image/webp,video/mp4" required></label>
        <label>Taken at <select name="spot"><option value="">— no spot —</option><?php foreach (spots() as $k => $s): ?><option value="<?= $k ?>"><?= h($s['name']) ?></option><?php endforeach; ?></select></label>
        <label>Caption <input name="caption" maxlength="140"></label>
      </div>
      <label class="check"><input type="checkbox" name="approve_now" value="1" checked> Checked: theirs, people in it agreed, nothing unsafe. Show it now.</label>
      <button class="btn btn-primary btn-small">Upload</button>
    </form>
    <div class="gallery admin-gallery">
      <?php foreach ($media as $m): $src = img_src($m['file'], 900); ?>
        <figure class="<?= $m['status'] === 'pending' ? 'is-pending' : '' ?>">
          <?php if ($m['kind'] === 'video'): ?><video src="<?= h($src) ?>" controls preload="none"></video><?php else: ?><img src="<?= h($src) ?>" alt=""><?php endif; ?>
          <figcaption class="small"><?= h($m['caption']) ?> <?= $m['spot'] ? '@ ' . h(spot($m['spot'])['name'] ?? $m['spot']) : '' ?> · <b><?= h($m['status']) ?></b>
            <form method="post" action="/admin/media/<?= (int)$m['id'] ?>" class="inline">
              <input type="hidden" name="csrf" value="<?= $tok ?>">
              <?php if ($m['status'] === 'pending'): ?><button name="a" value="approve" class="btn btn-small btn-primary">Approve</button><?php else: ?><button name="a" value="hide" class="btn btn-small btn-ghost">Hide</button><?php endif; ?>
              <button name="a" value="delete" class="btn btn-small btn-danger" onclick="return confirm('Delete this file?')">Delete</button>
            </form>
          </figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
