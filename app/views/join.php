<?php
$v = fn($k) => h(is_string($old[$k] ?? null) ? $old[$k] : '');
$has = fn($k, $x) => in_array($x, (array)($old[$k] ?? []), true) ? ' checked' : '';
$err = fn($k) => isset($errors[$k]) ? '<span class="field-err">' . h($errors[$k]) . '</span>' : '';
?>
<section class="pagehead">
  <div class="wrap">
    <p class="eyebrow">For schools, instructors & coaches</p>
    <h1>Get listed in Dahab, free</h1>
    <p class="lead">Inquiries go straight to your WhatsApp. No commission, no contract. We check your certificate, then your profile goes live.</p>
  </div>
</section>
<div class="wrap join">
  <aside class="join-why card">
    <h2>What you get</h2>
    <ul class="ticks">
      <li>A profile with your courses, prices, photos and videos</li>
      <li>Your photos shown on the spot pages where you teach</li>
      <li>A "Verified" badge once we check your certificate</li>
      <li>"Teaching this week" status you can update in one tap</li>
      <li>Badge, live conditions widget and Instagram story card</li>
      <li>Monthly numbers: profile views, WhatsApp and Instagram clicks</li>
    </ul>
    <p class="muted small">Prefer to do it by chat? Send us your details on WhatsApp and we'll fill it in for you.</p>
  </aside>
  <form class="form card" method="post" action="/en/join" novalidate>
    <?php if ($errors): ?><div class="notice err" role="alert">Please fix the highlighted fields.</div><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <div class="hp" aria-hidden="true"><label>Leave empty <input name="website_url" tabindex="-1" autocomplete="off"></label></div>

    <div class="row2">
      <label>Name or school name * <input name="name" required maxlength="80" value="<?= $v('name') ?>"><?= $err('name') ?></label>
      <label>You are
        <select name="type"><?php foreach (PROVIDER_TYPES as $k => $t): ?><option value="<?= h($k) ?>"<?= ($old['type'] ?? '') === $k ? ' selected' : '' ?>><?= h($t) ?></option><?php endforeach; ?></select>
      </label>
    </div>
    <div class="row2">
      <label>WhatsApp * <span class="muted small">(with country code)</span><input name="whatsapp" required inputmode="tel" maxlength="30" value="<?= $v('whatsapp') ?>" placeholder="+20 100 000 0000"><?= $err('whatsapp') ?></label>
      <label>Email <input name="email" type="email" maxlength="120" value="<?= $v('email') ?>"></label>
    </div>
    <label>One line about you <input name="tagline" maxlength="140" value="<?= $v('tagline') ?>" placeholder="e.g. Small-group AIDA courses at Lighthouse"></label>
    <label>About you <textarea name="bio" rows="4" maxlength="2000"><?= $v('bio') ?></textarea></label>

    <fieldset><legend>Agencies you teach</legend>
      <div class="checks"><?php foreach (AGENCIES as $a): ?><label class="check"><input type="checkbox" name="agencies[]" value="<?= h($a) ?>"<?= $has('agencies', $a) ?>> <?= h($a) ?></label><?php endforeach; ?></div>
    </fieldset>
    <fieldset><legend>Languages you teach in</legend>
      <div class="checks"><?php foreach (LANGS as $l): ?><label class="check"><input type="checkbox" name="languages[]" value="<?= h($l) ?>"<?= $has('languages', $l) ?>> <?= h($l) ?></label><?php endforeach; ?></div>
    </fieldset>
    <fieldset><legend>Where you teach</legend>
      <div class="checks"><?php foreach (spots() as $k => $s): ?><label class="check"><input type="checkbox" name="spots[]" value="<?= h($k) ?>"<?= $has('spots', $k) ?>> <?= h($s['name']) ?></label><?php endforeach; ?></div>
    </fieldset>
    <div class="row2">
      <label>Your status
        <select name="licence" required><option value="">Choose…</option><?php foreach (LICENCE as $k => $t): ?><option value="<?= h($k) ?>"<?= ($old['licence'] ?? '') === $k ? ' selected' : '' ?>><?= h($t) ?></option><?php endforeach; ?></select><?= $err('licence') ?>
      </label>
      <label>Centre you work with <span class="muted small">(if any)</span><input name="works_with" maxlength="80" value="<?= $v('works_with') ?>"></label>
    </div>
    <div class="row2">
      <label>Area / base <input name="base_area" maxlength="60" value="<?= $v('base_area') ?>" placeholder="e.g. Lighthouse"></label>
      <label>Personal best <span class="muted small">(optional)</span><input name="personal_best" maxlength="60" value="<?= $v('personal_best') ?>" placeholder="e.g. CWT 60 m"></label>
    </div>
    <label>Courses and prices <span class="muted small">(one per line: course, days, price in €)</span>
      <textarea name="courses" rows="4" maxlength="2000" placeholder="AIDA 2 – 3 days – €250&#10;Coached session – €35"><?= $v('courses') ?></textarea></label>
    <fieldset><legend>Show your work <span class="muted small">(links)</span></legend>
      <div class="row2">
        <?php foreach (SOCIALS as $k => $label): ?><label><?= h($label) ?> <input name="<?= h($k) ?>" type="url" maxlength="200" value="<?= $v($k) ?>" placeholder="https://"></label><?php endforeach; ?>
      </div>
      <p class="muted small">You can send photos and videos after we approve you. We show them on your profile and on the spot pages where they were taken.</p>
    </fieldset>
    <label class="check"><input type="checkbox" name="consent" value="1"<?= !empty($old['consent']) ? ' checked' : '' ?>> I agree to be listed on dive.wheretospot.com, and the photos I send are mine and shared with the consent of the people in them. <?= $err('consent') ?></label>
    <button class="btn btn-primary" type="submit">Send for approval</button>
  </form>
</div>
