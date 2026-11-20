<?php
partial('adminnav');
$tok = h(csrf_token());
$v = fn($k) => h($p[$k] ?? '');
$chk = fn($k, $x) => in_array($x, csv_list($p[$k]), true) ? ' checked' : '';
$rows = $svc;
for ($i = 0; $i < 3; $i++) $rows[] = ['category' => 'course', 'level' => 1, 'agency' => '', 'title' => '', 'days' => '', 'price_eur' => '', 'notes' => ''];
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

  <form class="form card" method="post" action="/admin/p/<?= (int)$p['id'] ?>">
    <input type="hidden" name="csrf" value="<?= $tok ?>">
    <h2>Profile</h2>
    <div class="row3">
      <label>Name <input name="name" value="<?= $v('name') ?>" maxlength="80" required></label>
      <label>URL name <input name="slug" value="<?= $v('slug') ?>" maxlength="80" pattern="[a-z0-9-]+"></label>
      <label>Type <select name="type"><?php foreach (PROVIDER_TYPES as $k => $t): ?><option value="<?= $k ?>"<?= $p['type'] === $k ? ' selected' : '' ?>><?= h($t) ?></option><?php endforeach; ?></select></label>
    </div>
    <label>Tagline <input name="tagline" value="<?= $v('tagline') ?>" maxlength="140"></label>
    <label>About <textarea name="bio" rows="5" maxlength="4000"><?= $v('bio') ?></textarea></label>
    <div class="row3">
      <label>WhatsApp <input name="whatsapp" value="<?= $v('whatsapp') ?>" maxlength="30"></label>
      <label>Email <span class="muted small">(gets inquiry emails)</span><input name="email" type="email" value="<?= $v('email') ?>" maxlength="120"></label>
      <label>Area / base <input name="base_area" value="<?= $v('base_area') ?>" maxlength="60"></label>
    </div>
    <div class="row3">
      <label>Status <select name="licence"><?php foreach (LICENCE as $k => $t): ?><option value="<?= $k ?>"<?= $p['licence'] === $k ? ' selected' : '' ?>><?= h($t) ?></option><?php endforeach; ?></select></label>
      <label>Works with centre <input name="works_with" value="<?= $v('works_with') ?>" maxlength="80"></label>
      <label>Certificate check note <input name="verified_note" value="<?= $v('verified_note') ?>" maxlength="200" placeholder="e.g. AIDA instructor #1234 checked 20 Nov"></label>
    </div>
    <fieldset><legend>Agencies</legend><div class="checks"><?php foreach (AGENCIES as $a): ?><label class="check"><input type="checkbox" name="agencies[]" value="<?= h($a) ?>"<?= $chk('agencies', $a) ?>> <?= h($a) ?></label><?php endforeach; ?></div></fieldset>
    <fieldset><legend>Languages</legend><div class="checks"><?php foreach (LANGS as $l): ?><label class="check"><input type="checkbox" name="languages[]" value="<?= h($l) ?>"<?= $chk('languages', $l) ?>> <?= h($l) ?></label><?php endforeach; ?></div></fieldset>
    <fieldset><legend>Spots</legend><div class="checks"><?php foreach (spots() as $k => $s): ?><label class="check"><input type="checkbox" name="spots[]" value="<?= h($k) ?>"<?= $chk('spots', $k) ?>> <?= h($s['name']) ?></label><?php endforeach; ?></div></fieldset>
    <div class="row3">
      <label>Max students per instructor <input type="number" name="max_ratio" min="0" max="20" value="<?= (int)$p['max_ratio'] ?>"></label>
      <label>Years teaching <input type="number" name="years_teaching" min="0" value="<?= (int)$p['years_teaching'] ?>"></label>
      <label>Students certified <input type="number" name="students_certified" min="0" value="<?= (int)$p['students_certified'] ?>"></label>
    </div>
    <div class="checks">
      <label class="check"><input type="checkbox" name="pool" value="1"<?= $p['pool'] ? ' checked' : '' ?>> Has a training pool</label>
      <label class="check"><input type="checkbox" name="gear_included" value="1"<?= $p['gear_included'] ? ' checked' : '' ?>> Gear included in courses</label>
    </div>
    <div class="row2">
      <label>Personal best <input name="personal_best" value="<?= $v('personal_best') ?>" maxlength="60"></label>
      <label>Prices checked on <input type="date" name="checked_on" value="<?= $v('checked_on') ?>"></label>
    </div>
    <label>Achievements <span class="muted small">(one per line)</span><textarea name="achievements" rows="3" maxlength="2000"><?= $v('achievements') ?></textarea></label>
    <div class="row2">
      <label class="check"><input type="checkbox" name="teaching_week" value="1"<?= $p['teaching_week'] ? ' checked' : '' ?>> Teaching this week</label>
      <label>This week's note <input name="week_note" value="<?= $v('week_note') ?>" maxlength="120" placeholder="e.g. Beginner course Monday, 2 spots left"></label>
    </div>
    <fieldset><legend>Social links</legend><div class="row3"><?php foreach (SOCIALS as $k => $label): ?><label><?= h($label) ?> <input name="<?= $k ?>" type="url" value="<?= $v($k) ?>" maxlength="200" placeholder="https://"></label><?php endforeach; ?></div></fieldset>

    <h2 id="services">Courses & prices <span class="muted small">(€; leave the title empty to skip a row)</span></h2>
    <div class="cmp-scroll">
    <table class="svc-edit">
      <thead><tr><th>Type</th><th>Level</th><th>Agency</th><th>Title</th><th>Days</th><th>€</th><th>Notes</th><th>Remove</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $i => $s): ?>
        <tr>
          <td><select name="svc[<?= $i ?>][category]"><?php foreach (SERVICE_CATS as $k => $l): ?><option value="<?= $k ?>"<?= $s['category'] === $k ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select></td>
          <td><select name="svc[<?= $i ?>][level]"><?php foreach (LEVELS as $k => [$l]): ?><option value="<?= $k ?>"<?= (int)$s['level'] === $k ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select></td>
          <td><select name="svc[<?= $i ?>][agency]"><option value="">—</option><?php foreach (AGENCIES as $a): ?><option<?= $s['agency'] === $a ? ' selected' : '' ?>><?= $a ?></option><?php endforeach; ?></select></td>
          <td><input name="svc[<?= $i ?>][title]" value="<?= h($s['title']) ?>" maxlength="100"></td>
          <td><input name="svc[<?= $i ?>][days]" type="number" step="0.5" min="0" value="<?= h($s['days']) ?>" class="num"></td>
          <td><input name="svc[<?= $i ?>][price_eur]" type="number" step="1" min="0" value="<?= h($s['price_eur']) ?>" class="num"></td>
          <td><input name="svc[<?= $i ?>][notes]" value="<?= h($s['notes']) ?>" maxlength="120"></td>
          <td><input type="checkbox" name="svc[<?= $i ?>][del]" value="1" aria-label="Remove row"></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <p class="muted small">Save adds 3 more empty rows.</p>
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
      <?php foreach ($media as $m): $src = cfg('uploads_url') . '/' . rawurlencode($m['file']); ?>
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
