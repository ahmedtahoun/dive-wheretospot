<?php
// Profile + courses form fields, shared by the admin edit page and the provider's private page.
// Needs $p, $svc, $admin (bool).
$v = fn($k) => h($p[$k] ?? '');
$chk = fn($k, $x) => in_array($x, csv_list($p[$k]), true) ? ' checked' : '';
$rows = $svc;
for ($i = 0; $i < 3; $i++) $rows[] = ['category' => 'course', 'level' => 1, 'agency' => '', 'title' => '', 'days' => '', 'price_eur' => '', 'notes' => ''];
?>
<h2 id="profile">Profile</h2>
<div class="row3">
  <label>Name <input name="name" value="<?= $v('name') ?>" maxlength="80" required></label>
  <?php if ($admin): ?><label>URL name <input name="slug" value="<?= $v('slug') ?>" maxlength="80" pattern="[a-z0-9-]+"></label><?php endif; ?>
  <label>You are <select name="type"><?php foreach (PROVIDER_TYPES as $k => $t): ?><option value="<?= $k ?>"<?= $p['type'] === $k ? ' selected' : '' ?>><?= h($t) ?></option><?php endforeach; ?></select></label>
</div>
<label>One line about you <input name="tagline" value="<?= $v('tagline') ?>" maxlength="140" placeholder="e.g. Small-group AIDA courses at Lighthouse"></label>
<label>About <textarea name="bio" rows="5" maxlength="4000" placeholder="Who you are, how you teach, what makes your sessions different."><?= $v('bio') ?></textarea></label>
<div class="row3">
  <label>WhatsApp <input name="whatsapp" value="<?= $v('whatsapp') ?>" maxlength="30" required></label>
  <label>Email <span class="muted small">(inquiries are sent here)</span><input name="email" type="email" value="<?= $v('email') ?>" maxlength="120"></label>
  <label>Area / base <input name="base_area" value="<?= $v('base_area') ?>" maxlength="60" placeholder="e.g. Lighthouse"></label>
</div>
<div class="row2">
  <label>Status <select name="licence"><?php foreach (LICENCE as $k => $t): ?><option value="<?= $k ?>"<?= $p['licence'] === $k ? ' selected' : '' ?>><?= h($t) ?></option><?php endforeach; ?></select></label>
  <label>Centre you work with <span class="muted small">(if any)</span><input name="works_with" value="<?= $v('works_with') ?>" maxlength="80"></label>
</div>
<fieldset><legend>Agencies you teach</legend><div class="checks"><?php foreach (AGENCIES as $a): ?><label class="check"><input type="checkbox" name="agencies[]" value="<?= h($a) ?>"<?= $chk('agencies', $a) ?>> <?= h($a) ?></label><?php endforeach; ?></div></fieldset>
<fieldset><legend>Languages you teach in</legend><div class="checks"><?php foreach (LANGS as $l): ?><label class="check"><input type="checkbox" name="languages[]" value="<?= h($l) ?>"<?= $chk('languages', $l) ?>> <?= h($l) ?></label><?php endforeach; ?></div></fieldset>
<fieldset><legend>Where you teach</legend><div class="checks"><?php foreach (spots() as $k => $s): ?><label class="check"><input type="checkbox" name="spots[]" value="<?= h($k) ?>"<?= $chk('spots', $k) ?>> <?= h($s['name']) ?></label><?php endforeach; ?></div></fieldset>
<div class="row3">
  <label>Max students per instructor <input type="number" name="max_ratio" min="0" max="20" value="<?= (int)$p['max_ratio'] ?>"></label>
  <label>Years teaching <input type="number" name="years_teaching" min="0" max="60" value="<?= (int)$p['years_teaching'] ?>"></label>
  <label>Students certified <input type="number" name="students_certified" min="0" value="<?= (int)$p['students_certified'] ?>"></label>
</div>
<div class="checks">
  <label class="check"><input type="checkbox" name="pool" value="1"<?= $p['pool'] ? ' checked' : '' ?>> Training pool</label>
  <label class="check"><input type="checkbox" name="gear_included" value="1"<?= $p['gear_included'] ? ' checked' : '' ?>> Gear included in courses</label>
</div>
<div class="row2">
  <label>Personal best <span class="muted small">(optional)</span><input name="personal_best" value="<?= $v('personal_best') ?>" maxlength="60" placeholder="e.g. CWT 60 m"></label>
  <?php if ($admin): ?><label>Prices checked on <input type="date" name="checked_on" value="<?= $v('checked_on') ?>"></label><?php endif; ?>
</div>
<label>Experience & achievements <span class="muted small">(one per line)</span><textarea name="achievements" rows="3" maxlength="2000" placeholder="AIDA Instructor&#10;Safety diver at Blue Hole competitions"><?= $v('achievements') ?></textarea></label>

<h2 id="cert">Your certificate</h2>
<p class="muted small">We check this on the agency's public instructor list, then switch on your <b>✓ Verified</b> badge.</p>
<div class="row2">
  <label>Instructor agency <select name="cert_agency"><option value="">—</option><?php foreach (AGENCIES as $a): ?><option<?= $p['cert_agency'] === $a ? ' selected' : '' ?>><?= $a ?></option><?php endforeach; ?></select></label>
  <label>Instructor number <input name="cert_number" value="<?= $v('cert_number') ?>" maxlength="60"></label>
</div>

<h2 id="social">Show your work</h2>
<div class="row3"><?php foreach (SOCIALS as $k => $label): ?><label><?= h($label) ?> <input name="<?= $k ?>" type="url" value="<?= $v($k) ?>" maxlength="200" placeholder="https://"></label><?php endforeach; ?></div>
<label>Best posts or videos <span class="muted small">(up to 6 links, one per line: Instagram, TikTok or YouTube)</span>
  <textarea name="work_links" rows="3" maxlength="2000" placeholder="https://www.instagram.com/reel/…&#10;https://youtu.be/…"><?= $v('work_links') ?></textarea></label>

<?php if ($admin): ?>
<div class="row2">
  <label class="check"><input type="checkbox" name="teaching_week" value="1"<?= $p['teaching_week'] ? ' checked' : '' ?>> Teaching this week</label>
  <label>This week's note <input name="week_note" value="<?= $v('week_note') ?>" maxlength="120"></label>
</div>
<label>Certificate check note <input name="verified_note" value="<?= $v('verified_note') ?>" maxlength="200" placeholder="e.g. AIDA instructor #1234 checked 20 Nov"></label>
<?php endif; ?>

<h2 id="courses">Courses & prices <span class="muted small">(in €)</span></h2>
<p class="muted small">Tap a course to add it, then set the price. Leave the name empty or tick "Remove" to delete a row.</p>
<div class="chips presets" data-presets>
  <?php foreach (COURSE_PRESETS as $pr): ?>
    <button type="button" data-preset="<?= h(json_encode($pr)) ?>">+ <?= h($pr[3]) ?></button>
  <?php endforeach; ?>
</div>
<div class="cmp-scroll">
<table class="svc-edit" data-svc>
  <thead><tr><th>Type</th><th>Level</th><th>Agency</th><th>Name</th><th>Days</th><th>€</th><th>Notes</th><th>Remove</th></tr></thead>
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
<script>
// Course presets: fill the first empty row, or add a new row.
(function () {
  var box = document.querySelector('[data-presets]');
  var tbody = document.querySelector('[data-svc] tbody');
  if (!box || !tbody) return;
  box.addEventListener('click', function (e) {
    var b = e.target.closest('button[data-preset]'); if (!b) return;
    var pr = JSON.parse(b.dataset.preset);
    var row = Array.prototype.find.call(tbody.rows, function (r) { return !r.querySelector('[name$="[title]"]').value; });
    if (!row) {
      var last = tbody.rows[tbody.rows.length - 1], i = tbody.rows.length;
      row = last.cloneNode(true);
      row.querySelectorAll('[name]').forEach(function (el) { el.name = el.name.replace(/svc\[\d+\]/, 'svc[' + i + ']'); if (el.type === 'checkbox') el.checked = false; else if (el.tagName === 'INPUT') el.value = ''; });
      tbody.appendChild(row);
    }
    var set = function (k, v) { var el = row.querySelector('[name$="[' + k + ']"]'); if (el) el.value = v; };
    set('category', pr[0]); set('level', pr[1]); set('agency', pr[2]); set('title', pr[3]); set('days', pr[4]);
    var price = row.querySelector('[name$="[price_eur]"]'); price.focus();
  });
})();
</script>
