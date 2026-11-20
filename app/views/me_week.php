<?php $tok = h(csrf_token()); ?>
<section class="pagehead"><div class="wrap narrow">
  <p class="eyebrow"><?= h($p['name']) ?></p>
  <?php if ($done !== ''): ?>
    <h1><?= $done === '1' ? 'Done: you show as teaching this week ✓' : 'Done: you are off this week' ?></h1>
    <p class="lead"><?= $done === '1' ? 'Visitors see the green "Teaching this week" tag on your profile for the next 7 days.' : 'The tag is off. Tap "Yes" any time you have space again.' ?></p>
    <p><a class="btn btn-ghost" href="<?= h($base) ?>">Open your profile page</a></p>
  <?php else: ?>
    <h1>Teaching this week?</h1>
    <p class="lead">One tap. It shows a green tag on your profile for 7 days.</p>
  <?php endif; ?>
  <form method="post" action="<?= h($base) ?>/week" class="form card week-form">
    <input type="hidden" name="csrf" value="<?= $tok ?>">
    <label>Short note <span class="muted small">(optional)</span><input name="week_note" maxlength="120" value="<?= h($p['week_note']) ?>" placeholder="e.g. Beginner course Monday, 2 spots left"></label>
    <div class="actions">
      <button class="btn btn-primary btn-big" name="on" value="1">Yes, I have space</button>
      <button class="btn btn-ghost btn-big" name="on" value="0">Not this week</button>
    </div>
  </form>
</div></section>
