<section class="pagehead slim">
  <div class="wrap narrow">
    <?php if ($error): ?>
      <h1>Something is missing</h1>
      <p class="lead">Please add your name, a WhatsApp number or email, and tick at least one instructor. Go back and try again; your answers are still there.</p>
      <p><a class="btn btn-ghost" href="javascript:history.back()">← Back</a></p>
    <?php else: ?>
      <h1>Sent, <?= h($name) ?>!</h1>
      <p class="lead">Your request went to <?= count($sent) ?> instructor<?= count($sent) === 1 ? '' : 's' ?>. Most reply on WhatsApp within a day. Want a faster answer? Message them directly too:</p>
      <ul class="sentlist">
        <?php foreach ($sent as $x): $p = $x['p']; ?>
          <li>
            <span><b><?= h($p['name']) ?></b><span class="muted small"> · ref <?= h($x['ref']) ?></span></span>
            <?php if ($p['whatsapp']): ?><a class="btn btn-small btn-wa" target="_blank" rel="noopener nofollow" href="<?= h(wa_link($p['whatsapp'], "Hi {$p['name']}, I sent you a request on dive.wheretospot.com (ref {$x['ref']}).")) ?>">WhatsApp</a><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <p><a href="/en/today">Meanwhile: see today's conditions in Dahab →</a></p>
    <?php endif; ?>
  </div>
</section>
