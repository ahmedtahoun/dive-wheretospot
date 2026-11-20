<?php partial('adminnav'); $tok = h(csrf_token()); ?>
<section class="wrap section">
  <h1 class="h2">Events & camps</h1>
  <?php if (!empty($_GET['err'])): ?><div class="notice err">Add a title and a start date.</div><?php endif; ?>
  <form class="form card" method="post" action="/admin/events">
    <input type="hidden" name="csrf" value="<?= $tok ?>">
    <div class="row2">
      <label>Title <input name="title" required maxlength="100"></label>
      <label>Type <select name="kind"><?php foreach (['camp','course','workshop','competition','social','trip'] as $k): ?><option><?= $k ?></option><?php endforeach; ?></select></label>
    </div>
    <div class="row2">
      <label>Starts <input type="date" name="starts_on" required></label>
      <label>Ends <span class="muted small">(optional)</span><input type="date" name="ends_on"></label>
    </div>
    <label>Run by <select name="provider_id"><option value="">— Community / no provider —</option><?php foreach ($providers as $p): ?><option value="<?= (int)$p['id'] ?>"><?= h($p['name']) ?></option><?php endforeach; ?></select></label>
    <label>Short description <input name="description" maxlength="300"></label>
    <button class="btn btn-primary">Add event</button>
  </form>
  <table class="admintable">
    <thead><tr><th>Date</th><th>Event</th><th>By</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $e): ?>
      <tr><td><?= h($e['starts_on']) ?><?= $e['ends_on'] ? ' → ' . h($e['ends_on']) : '' ?></td><td><b><?= h($e['title']) ?></b> <span class="muted small"><?= h($e['kind']) ?></span><?= $e['is_sample'] ? ' <span class="badge b-sample">sample</span>' : '' ?></td><td><?= h($e['pname'] ?? '—') ?></td>
        <td><form method="post" action="/admin/events/<?= (int)$e['id'] ?>/delete" onsubmit="return confirm('Delete this event?')"><input type="hidden" name="csrf" value="<?= $tok ?>"><button class="btn btn-small btn-ghost">Delete</button></form></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
