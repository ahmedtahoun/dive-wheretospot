<?php
// Provider card: photo first, then only what helps you choose. Needs $p and $services.
$beg = course_price($services, 1);
$from = null;
foreach ($services as $s) if ((float)$s['price_eur'] > 0 && $s['category'] !== 'rental' && ($from === null || (float)$s['price_eur'] < $from)) $from = (float)$s['price_eur'];
$levels = array_values(array_unique(array_map(fn($s) => (int)$s['level'], array_filter($services, fn($s) => $s['category'] === 'course'))));
$cats = array_values(array_unique(array_map(fn($s) => $s['category'], $services)));
$cover = provider_covers()[(int)$p['id']] ?? null;
$compact = $compact ?? false;
$url = '/en/p/' . h($p['slug']);
?>
<article class="pcard<?= $p['featured'] ? ' is-featured' : '' ?>"
  data-type="<?= h($p['type']) ?>" data-agencies="<?= h($p['agencies']) ?>" data-langs="<?= h($p['languages']) ?>"
  data-spots="<?= h($p['spots']) ?>" data-levels="<?= h(implode(',', $levels)) ?>" data-cats="<?= h(implode(',', $cats)) ?>"
  data-week="<?= (int)$p['teaching_week'] ?>" data-verified="<?= (int)$p['verified'] ?>"
  data-price="<?= $beg ? (float)$beg['price_eur'] : 99999 ?>" data-ratio="<?= (int)$p['max_ratio'] ?: 99 ?>"
  data-name="<?= h($p['name']) ?>" data-slug="<?= h($p['slug']) ?>">
  <a class="pcard-media" href="<?= $url ?>" tabindex="-1" aria-hidden="true" style="--h:<?= avatar_hue($p['name']) ?>">
    <?php if ($cover): ?><img src="<?= h(img_src($cover, 640)) ?>" alt="" loading="lazy"><?php else: ?><span class="pcard-initials"><?= h(initials($p['name'])) ?></span><?php endif; ?>
    <?php if ($p['featured']): ?><span class="tag tag-feat">Featured</span><?php endif; ?>
    <?php if ($p['teaching_week']): ?><span class="tag tag-week"><i></i>Teaching this week</span><?php endif; ?>
    <?php if ($p['is_sample']): ?><span class="tag tag-sample" title="Fictional profile for the prototype">Sample</span><?php endif; ?>
  </a>
  <?php if (!$compact): ?>
    <label class="cmp-toggle" title="Add to compare"><input type="checkbox" class="cmp" value="<?= h($p['slug']) ?>"><span>Compare</span></label>
  <?php endif; ?>
  <div class="pcard-body">
    <h3><a href="<?= $url ?>"><?= h($p['name']) ?></a><?php if ($p['verified']): ?> <span class="vtick" title="Certificate verified" aria-label="Verified">✓</span><?php endif; ?></h3>
    <p class="pcard-meta"><?= h(PROVIDER_TYPES[$p['type']] ?? '') ?><?= $p['base_area'] ? ' · ' . h($p['base_area']) : '' ?></p>
    <p class="pcard-line"><?= h(str_replace(',', ' · ', $p['agencies'])) ?><?= $p['languages'] ? '<span class="sep"></span>' . h(short_langs($p['languages'])) : '' ?></p>
    <p class="pcard-price"><?php if ($beg): ?>Beginner course <b><?= money((float)$beg['price_eur']) ?></b><?php elseif ($from): ?>From <b><?= money($from) ?></b><?php else: ?><span class="muted">Ask for prices</span><?php endif; ?></p>
  </div>
</article>
