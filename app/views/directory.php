<section class="pagehead slim">
  <div class="wrap">
    <h1>Freediving schools & coaches in Dahab</h1>
    <p class="lead">Filter by what you want to learn, tick up to 4 to compare. Not sure? <a href="/en/match">Find my match in 1 minute →</a></p>
  </div>
</section>

<div class="wrap dir" id="directory">
  <div class="fbar" aria-label="Filters">
    <div class="chips" data-filter="level" data-single role="group" aria-label="What do you want to do">
      <button type="button" data-v="0">Try it</button>
      <button type="button" data-v="1">Beginner</button>
      <button type="button" data-v="2">Intermediate</button>
      <button type="button" data-v="3">Advanced</button>
      <button type="button" data-v="coach">Coaching</button>
      <button type="button" data-v="fun">Fun dives</button>
    </div>
    <div class="fbar-row">
      <label class="fsel"><span class="sr-only">Good for</span>
        <select data-fsel="spec"><option value="">Good for…</option><?php foreach (SPECIALTIES as $k => [$label]): ?><option value="<?= $k ?>"><?= h($label) ?></option><?php endforeach; ?></select></label>
      <label class="fsel"><span class="sr-only">Language</span>
        <select data-fsel="lang"><option value="">Any language</option><?php foreach (LANGS as $l): ?><option><?= h($l) ?></option><?php endforeach; ?></select></label>
      <label class="fsel"><span class="sr-only">Agency</span>
        <select data-fsel="agency"><option value="">Any agency</option><?php foreach (AGENCIES as $a): ?><option><?= h($a) ?></option><?php endforeach; ?></select></label>
      <label class="fsel"><span class="sr-only">Spot</span>
        <select data-fsel="spot"><option value="">Any spot</option><?php foreach (spots() as $k => $s): if (!$s['freedive']) continue; ?><option value="<?= h($k) ?>"><?= h($s['name']) ?></option><?php endforeach; ?></select></label>
      <label class="fsel"><span class="sr-only">Type</span>
        <select data-fsel="type"><option value="">Schools & freelancers</option><?php foreach (PROVIDER_TYPES as $k => $v): ?><option value="<?= h($k) ?>"><?= h($v) ?></option><?php endforeach; ?></select></label>
      <label class="switch"><input type="checkbox" data-flag="week"> This week</label>
      <label class="switch"><input type="checkbox" data-flag="verified"> Verified</label>
    </div>
  </div>

  <div class="dir-bar">
    <p class="muted small" aria-live="polite"><b data-count><?= count($providers) ?></b> of <?= count($providers) ?> <button type="button" class="linkbtn" data-reset>Clear filters</button></p>
    <label class="sort small">Sort
      <select data-sort>
        <option value="featured">Recommended</option>
        <option value="price">Cheapest beginner course</option>
        <option value="ratio">Smallest groups</option>
        <option value="name">Name A–Z</option>
      </select>
    </label>
  </div>
  <div class="grid cards" data-list>
    <?php foreach ($providers as $p) partial('card', ['p' => $p, 'services' => $svc[(int)$p['id']] ?? []]); ?>
  </div>
  <p class="empty muted" data-empty hidden>Nothing matches all of those filters. <button type="button" class="linkbtn" data-reset>Clear filters</button></p>
  <p class="muted small note">Missing someone? <a href="/en/join">They can list for free</a>. Prices are what each provider told us; each profile shows when they were last updated.</p>
</div>

<div class="cmpbar" data-cmpbar hidden>
  <div class="wrap cmpbar-in">
    <span><b data-cmpn>0</b> selected</span>
    <span class="cmp-names small" data-cmpnames></span>
    <button type="button" class="linkbtn" data-cmpclear>Clear</button>
    <a class="btn btn-coral btn-small" data-cmpgo href="/en/compare">Compare</a>
  </div>
</div>
