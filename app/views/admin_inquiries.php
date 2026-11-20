<?php partial('adminnav'); $tok = h(csrf_token()); ?>
<section class="wrap section">
  <h1 class="h2">Inquiries</h1>
  <?php if (!$rows): ?><p class="muted">No inquiries yet.</p><?php endif; ?>
  <div class="cmp-scroll">
  <table class="admintable">
    <thead><tr><th>Ref / date</th><th>For</th><th>From</th><th>Details</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><b><?= h($r['ref']) ?></b><br><span class="muted small"><?= h($r['created_at']) ?> UTC</span></td>
        <td><?= $r['pname'] ? '<a href="/en/p/' . h($r['slug']) . '" target="_blank">' . h($r['pname']) . '</a>' : '—' ?></td>
        <td><?= h($r['name']) ?><br>
          <?php if ($r['email']): ?><a class="small" href="mailto:<?= h($r['email']) ?>"><?= h($r['email']) ?></a><br><?php endif; ?>
          <?php if ($r['whatsapp']): ?><a class="small" href="<?= h(wa_link($r['whatsapp'])) ?>" target="_blank" rel="noopener">WhatsApp <?= h($r['whatsapp']) ?></a><?php endif; ?></td>
        <td class="small"><?= h($r['dates']) ?> · <?= h($r['level']) ?> · <?= (int)$r['people'] ?> pax<br><?= nl2br(h($r['message'])) ?></td>
        <td>
          <form method="post" action="/admin/inquiries/<?= (int)$r['id'] ?>" class="inline">
            <input type="hidden" name="csrf" value="<?= $tok ?>">
            <select name="status" onchange="this.form.submit()">
              <?php foreach (['new' => 'New', 'sent' => 'Passed on', 'booked' => 'Booked', 'lost' => 'Lost'] as $k => $l): ?><option value="<?= $k ?>"<?= $r['status'] === $k ? ' selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
            </select>
            <noscript><button class="btn btn-small">Save</button></noscript>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</section>
