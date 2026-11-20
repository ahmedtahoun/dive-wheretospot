<?php
$v = fn($k) => h(is_string($old[$k] ?? null) ? $old[$k] : '');
$has = fn($k, $x) => in_array($x, (array)($old[$k] ?? []), true) ? ' checked' : '';
$err = fn($k) => isset($errors[$k]) ? '<span class="field-err">' . h($errors[$k]) . '</span>' : '';
$adminWa = (string)cfg('whatsapp_admin');
?>
<section class="pagehead">
  <div class="wrap">
    <p class="eyebrow">For schools, instructors & coaches</p>
    <h1>Get listed in Dahab, free</h1>
    <p class="lead">One minute to start. You then get your own private page to add courses, photos and videos, and to say when you're teaching. No password, no commission.</p>
  </div>
</section>
<div class="wrap join">
  <aside class="join-why card">
    <h2>What you get</h2>
    <ul class="ticks">
      <li>Your profile: courses, prices, photos, videos and your best posts</li>
      <li>Your photos on the spot pages where you teach</li>
      <li>A "Verified" badge once we check your instructor number</li>
      <li>"Teaching this week" in one tap</li>
      <li>Your numbers: profile views, WhatsApp taps, inquiries</li>
      <li>Story card, badge and live conditions widget for your own site</li>
    </ul>
    <?php if ($adminWa !== ''): ?>
      <p class="small"><b>Rather do it by chat?</b></p>
      <a class="btn btn-wa btn-block" target="_blank" rel="noopener" href="<?= h(wa_link($adminWa, "Hi Where To Spot, I'd like to list on dive.wheretospot.com.\nName:\nI am a (school / freelance instructor / coach):\nAgencies:\nLanguages:")) ?>">Sign up on WhatsApp</a>
    <?php endif; ?>
  </aside>
  <form class="form card" method="post" action="/en/join" novalidate>
    <?php if ($errors): ?><div class="notice err" role="alert">Please fix the highlighted fields.</div><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <div class="hp" aria-hidden="true"><label>Leave empty <input name="website_url" tabindex="-1" autocomplete="off"></label></div>
    <p class="steps small"><b>Step 1 of 2</b> · the basics. Step 2 is your private page, right after this.</p>

    <label>Your name or school name <input name="name" required maxlength="80" value="<?= $v('name') ?>" autocomplete="organization"><?= $err('name') ?></label>
    <div class="row2">
      <label>You are
        <select name="type"><?php foreach (PROVIDER_TYPES as $k => $t): ?><option value="<?= h($k) ?>"<?= ($old['type'] ?? '') === $k ? ' selected' : '' ?>><?= h($t) ?></option><?php endforeach; ?></select>
      </label>
      <label>Your status
        <select name="licence" required><option value="">Choose…</option><?php foreach (LICENCE as $k => $t): ?><option value="<?= h($k) ?>"<?= ($old['licence'] ?? '') === $k ? ' selected' : '' ?>><?= h($t) ?></option><?php endforeach; ?></select><?= $err('licence') ?>
      </label>
    </div>
    <div class="row2">
      <label>WhatsApp <span class="muted small">(with country code)</span><input name="whatsapp" required inputmode="tel" maxlength="30" value="<?= $v('whatsapp') ?>" placeholder="+20 100 000 0000" autocomplete="tel"><?= $err('whatsapp') ?></label>
      <label>Email <span class="muted small">(optional, for inquiries)</span><input name="email" type="email" maxlength="120" value="<?= $v('email') ?>" autocomplete="email"></label>
    </div>
    <fieldset><legend>Agencies you teach</legend>
      <div class="checks"><?php foreach (AGENCIES as $a): ?><label class="check"><input type="checkbox" name="agencies[]" value="<?= h($a) ?>"<?= $has('agencies', $a) ?>> <?= h($a) ?></label><?php endforeach; ?></div>
    </fieldset>
    <label class="check"><input type="checkbox" name="consent" value="1"<?= !empty($old['consent']) ? ' checked' : '' ?>> I agree to be listed on dive.wheretospot.com, and anything I upload is mine and shared with the consent of the people in it. <?= $err('consent') ?></label>
    <button class="btn btn-primary" type="submit">Continue →</button>
  </form>
</div>
