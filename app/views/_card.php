<?php
// Provider card used in the directory, home and spot pages. Needs $p and $services.
$beg = course_price($services, 1);
$from = null;
foreach ($services as $s) if ((float)$s['price_eur'] > 0 && $s['category'] !== 'rental' && ($from === null || (float)$s['price_eur'] < $from)) $from = (float)$s['price_eur'];
$levels = array_values(array_unique(array_map(fn($s) => (int)$s['level'], array_filter($services, fn($s) => $s['category'] === 'course'))));
$cats = array_values(array_unique(array_map(fn($s) => $s['category'], $services)));
$hue = avatar_hue($p['name']);
$compact = $compact ?? false;
?>
<article class="card pcard<?= $p['featured'] ? ' is-featured' : '' ?>"
  data-type="<?= h($p['type']) ?>"
  data-agencies="<?= h($p['agencies']) ?>"
  data-langs="<?= h($p['languages']) ?>"
  data-spots="<?= h($p['spots']) ?>"
  data-levels="<?= h(implode(',', $levels)) ?>"
  data-cats="<?= h(implode(',', $cats)) ?>"
  data-week="<?= (int)$p['teaching_week'] ?>"
  data-verified="<?= (int)$p['verified'] ?>"
  data-price="<?= $beg ? (float)$beg['price_eur'] : 99999 ?>"
  data-ratio="<?= (int)$p['max_ratio'] ?: 99 ?>"
  data-name="<?= h($p['name']) ?>"
  data-slug="<?= h($p['slug']) ?>">
  <div class="pcard-top">
    <div class="avatar" style="--h:<?= $hue ?>" aria-hidden="true"><?= h(initials($p['name'])) ?></div>
    <div class="pcard-head">
      <h3><a href="/en/p/<?= h($p['slug']) ?>"><?= h($p['name']) ?></a></h3>
      <p class="muted small"><?= h(PROVIDER_TYPES[$p['type']] ?? '') ?><?= $p['base_area'] ? ' · ' . h($p['base_area']) : '' ?></p>
    </div>
    <?php if (!$compact): ?>
    <label class="cmp-toggle" title="Add to compare">
      <input type="checkbox" class="cmp" value="<?= h($p['slug']) ?>" aria-label="Compare <?= h($p['name']) ?>"> <span>Compare</span>
    </label>
    <?php endif; ?>
  </div>
  <div class="badges">
    <?php if ($p['is_sample']): ?><span class="badge b-sample" title="Fictional profile for the prototype">Sample profile</span><?php endif; ?>
    <?php if ($p['verified']): ?><span class="badge b-ok">✓ Verified</span><?php endif; ?>
    <?php if ($p['featured']): ?><span class="badge b-feat">Featured</span><?php endif; ?>
    <?php if ($p['founding']): ?><span class="badge b-found">Founding member</span><?php endif; ?>
    <?php if ($p['teaching_week']): ?><span class="badge b-week">● Teaching this week</span><?php endif; ?>
  </div>
  <?php if ($p['tagline']): ?><p class="pcard-tag"><?= h($p['tagline']) ?></p><?php endif; ?>
  <dl class="facts">
    <div><dt>Agencies</dt><dd><?= h(str_replace(',', ' · ', $p['agencies']) ?: '—') ?></dd></div>
    <div><dt>Languages</dt><dd><?= h(str_replace(',', ', ', $p['languages']) ?: '—') ?></dd></div>
    <div><dt>Beginner course</dt><dd><?= $beg ? '<b>' . money((float)$beg['price_eur']) . '</b> · ' . h(rtrim(rtrim(number_format((float)$beg['days'], 1), '0'), '.')) . ' days' : '—' ?></dd></div>
    <div><dt>Group size</dt><dd><?= $p['max_ratio'] ? 'max ' . (int)$p['max_ratio'] . ' per instructor' : '—' ?></dd></div>
  </dl>
  <div class="pcard-foot">
    <span class="muted small"><?= $from ? 'From ' . money($from) : '' ?></span>
    <span class="pcard-actions">
      <a class="btn btn-small btn-ghost" href="/en/p/<?= h($p['slug']) ?>">Profile</a>
      <?php if ($p['whatsapp']): ?><a class="btn btn-small btn-wa" href="/go/whatsapp/<?= h($p['slug']) ?>" rel="nofollow">WhatsApp</a><?php endif; ?>
    </span>
  </div>
</article>
