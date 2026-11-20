<?php partial('adminnav'); $tok = h(csrf_token()); ?>
<section class="wrap section">
  <h1 class="h2">Photos & videos to check</h1>
  <p class="muted">Approve if it's theirs, the people in it look fine with it, and nothing is unsafe (no solo diving, no blackout "stunts"). Verified providers skip this queue.</p>
  <?php if (!$rows): ?><p class="muted">Nothing waiting. ✓</p><?php endif; ?>
  <div class="gallery admin-gallery">
    <?php foreach ($rows as $m): $src = img_src($m['file'], 900); ?>
      <figure class="is-pending">
        <?php if ($m['kind'] === 'video'): ?><video src="<?= h($src) ?>" controls preload="metadata"></video><?php else: ?><img src="<?= h($src) ?>" alt=""><?php endif; ?>
        <figcaption class="small"><b><?= h($m['name']) ?></b> · <?= h($m['caption']) ?> <?= $m['spot'] && spot($m['spot']) ? '@ ' . h(spot($m['spot'])['name']) : '' ?>
          <form method="post" action="/admin/media/<?= (int)$m['id'] ?>" class="inline">
            <input type="hidden" name="csrf" value="<?= $tok ?>"><input type="hidden" name="back" value="/admin/photos">
            <button name="a" value="approve" class="btn btn-small btn-primary">Approve</button>
            <button name="a" value="delete" class="btn btn-small btn-danger" onclick="return confirm('Delete this file?')">Delete</button>
          </form>
        </figcaption>
      </figure>
    <?php endforeach; ?>
  </div>
</section>
