<?php if (!$events): ?>
  <p class="muted">No events posted yet.</p>
<?php else: ?>
<ul class="events">
  <?php foreach ($events as $e):
    $s = strtotime($e['starts_on']); $en = $e['ends_on'] ? strtotime($e['ends_on']) : null; ?>
    <li>
      <span class="ev-date"><b><?= date('j', $s) ?></b><?= date('M', $s) ?></span>
      <span class="ev-body">
        <b><?= h($e['title']) ?></b>
        <span class="muted small">
          <?= h(ucfirst($e['kind'])) ?><?= $en ? ' · until ' . date('D j M', $en) : ' · ' . date('D', $s) ?>
          <?php if (!empty($e['pname'])): ?> · <a href="/en/p/<?= h($e['pslug']) ?>"><?= h($e['pname']) ?></a><?php endif; ?>
        </span>
        <?php if ($e['description']): ?><span class="small"><?= h($e['description']) ?></span><?php endif; ?>
      </span>
    </li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
