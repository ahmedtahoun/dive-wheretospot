<?php partial('adminnav'); $tok = h(csrf_token()); ?>
<section class="wrap section">
  <?php if ($msg): ?><div class="notice ok" role="status"><?= h($msg) ?></div><?php endif; ?>
  <div class="statgrid">
    <a class="card stat" href="/admin/?status=pending"><p class="muted small">Waiting for approval</p><p class="stat-big"><?= (int)($counts['pending'] ?? 0) ?></p></a>
    <a class="card stat" href="/admin/?status=approved"><p class="muted small">Live</p><p class="stat-big"><?= (int)($counts['approved'] ?? 0) ?></p></a>
    <a class="card stat" href="/admin/inquiries"><p class="muted small">New inquiries</p><p class="stat-big"><?= $newInq ?></p></a>
    <div class="card stat"><p class="muted small">Contact clicks, 30 days</p><p class="stat-big"><?= $clicks30 ?></p></div>
  </div>

  <div class="sec-head">
    <h1 class="h2">Providers <?= $status ? '· ' . h($status) : '' ?></h1>
    <form method="post" action="/admin/new" class="inline">
      <input type="hidden" name="csrf" value="<?= $tok ?>">
      <input name="name" placeholder="Add a provider by name" required maxlength="80">
      <button class="btn btn-small btn-primary">Add</button>
    </form>
  </div>
  <p class="small"><a href="/admin/">All</a> · <a href="/admin/?status=pending">Pending</a> · <a href="/admin/?status=approved">Live</a> · <a href="/admin/?status=rejected">Rejected</a></p>

  <div class="cmp-scroll">
  <table class="admintable">
    <thead><tr><th>Name</th><th>Status</th><th>Badges</th><th>30 days</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($providers as $p): ?>
      <tr class="st-<?= h($p['status']) ?>">
        <td><a href="/admin/p/<?= (int)$p['id'] ?>"><b><?= h($p['name']) ?></b></a><br><span class="muted small"><?= h(PROVIDER_TYPES[$p['type']] ?? '') ?> · <?= h($p['whatsapp']) ?></span>
          <?php if ($p['submitted_courses']): ?><details class="small"><summary>Sent courses</summary><pre><?= h($p['submitted_courses']) ?></pre></details><?php endif; ?></td>
        <td><span class="badge st-<?= h($p['status']) ?>"><?= h($p['status']) ?></span><?= $p['is_sample'] ? ' <span class="badge b-sample">sample</span>' : '' ?></td>
        <td class="small"><?= $p['verified'] ? '✓ verified ' : '' ?><?= $p['featured'] ? '★ featured ' : '' ?><?= $p['founding'] ? 'founding ' : '' ?><?= $p['teaching_week'] ? '● this week' : '' ?><?= $p['media_pending'] ? '<br><b>' . (int)$p['media_pending'] . ' photo(s) to check</b>' : '' ?></td>
        <td class="small"><?= (int)$p['clicks30'] ?> clicks (<?= (int)$p['wa30'] ?> WhatsApp)<br><?= (int)$p['inq'] ?> inquiries</td>
        <td>
          <form method="post" action="/admin/p/<?= (int)$p['id'] ?>/action" class="actions">
            <input type="hidden" name="csrf" value="<?= $tok ?>"><input type="hidden" name="back" value="/admin/<?= $status ? '?status=' . h($status) : '' ?>">
            <?php if ($p['status'] !== 'approved'): ?><button name="a" value="approve" class="btn btn-small btn-primary">Approve</button><?php endif; ?>
            <?php if ($p['status'] === 'pending'): ?><button name="a" value="reject" class="btn btn-small btn-ghost">Reject</button><?php endif; ?>
            <button name="a" value="<?= $p['verified'] ? 'unverify' : 'verify' ?>" class="btn btn-small btn-ghost"><?= $p['verified'] ? 'Unverify' : 'Verify' ?></button>
            <button name="a" value="feature" class="btn btn-small btn-ghost"><?= $p['featured'] ? 'Unfeature' : 'Feature' ?></button>
            <button name="a" value="week" class="btn btn-small btn-ghost"><?= $p['teaching_week'] ? 'Not this week' : 'This week' ?></button>
            <a class="btn btn-small btn-ghost" href="/en/p/<?= h($p['slug']) ?>" target="_blank">View</a>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>

  <?php if ($samples): ?>
  <form method="post" action="/admin/samples/delete" class="danger-zone card" onsubmit="return confirm('Remove all <?= $samples ?> sample providers and sample events? Real providers are not touched.')">
    <input type="hidden" name="csrf" value="<?= $tok ?>">
    <p><b>Before launch:</b> remove the <?= $samples ?> fictional sample providers and their events.</p>
    <button class="btn btn-small btn-danger">Remove sample data</button>
  </form>
  <?php endif; ?>
</section>
