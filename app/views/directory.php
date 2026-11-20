<section class="pagehead">
  <div class="wrap">
    <p class="eyebrow">Dahab</p>
    <h1>Freediving schools, instructors & coaches</h1>
    <p class="lead">Every listing in one place. Filter by what you want to learn, then tick up to 4 to compare side by side.</p>
  </div>
</section>

<div class="wrap dir" id="directory">
  <aside class="filters" aria-label="Filters">
    <button class="btn btn-ghost btn-small filters-toggle" type="button" aria-expanded="false" aria-controls="filters-body">Filters</button>
    <div id="filters-body" class="filters-body">
      <fieldset>
        <legend>I want to</legend>
        <div class="chips" data-filter="level" data-single>
          <button type="button" data-v="0">Try it</button>
          <button type="button" data-v="1">Beginner</button>
          <button type="button" data-v="2">Intermediate</button>
          <button type="button" data-v="3">Advanced</button>
          <button type="button" data-v="coach">Coaching</button>
          <button type="button" data-v="fun">Fun dives & trips</button>
        </div>
      </fieldset>
      <fieldset>
        <legend>Who</legend>
        <div class="chips" data-filter="type">
          <?php foreach (PROVIDER_TYPES as $k => $v): ?><button type="button" data-v="<?= h($k) ?>"><?= h($v) ?></button><?php endforeach; ?>
        </div>
      </fieldset>
      <fieldset>
        <legend>Agency</legend>
        <div class="chips" data-filter="agency">
          <?php foreach (AGENCIES as $a): ?><button type="button" data-v="<?= h($a) ?>"><?= h($a) ?></button><?php endforeach; ?>
        </div>
      </fieldset>
      <fieldset>
        <legend>Language</legend>
        <div class="chips" data-filter="lang">
          <?php foreach (LANGS as $l): ?><button type="button" data-v="<?= h($l) ?>"><?= h($l) ?></button><?php endforeach; ?>
        </div>
      </fieldset>
      <fieldset>
        <legend>Spot</legend>
        <div class="chips" data-filter="spot">
          <?php foreach (spots() as $k => $s): if (!$s['freedive']) continue; ?><button type="button" data-v="<?= h($k) ?>"><?= h($s['name']) ?></button><?php endforeach; ?>
        </div>
      </fieldset>
      <fieldset>
        <legend>Only show</legend>
        <label class="check"><input type="checkbox" data-flag="week"> Teaching this week</label>
        <label class="check"><input type="checkbox" data-flag="verified"> Verified certificates</label>
      </fieldset>
      <button type="button" class="linkbtn" data-reset>Clear all filters</button>
    </div>
  </aside>

  <div class="dir-main">
    <div class="dir-bar">
      <p class="muted" aria-live="polite"><b data-count><?= count($providers) ?></b> of <?= count($providers) ?> listings</p>
      <label class="sort">Sort
        <select data-sort>
          <option value="featured">Featured first</option>
          <option value="price">Beginner course: cheapest</option>
          <option value="ratio">Smallest groups</option>
          <option value="name">Name A–Z</option>
        </select>
      </label>
    </div>
    <div class="grid cards" data-list>
      <?php foreach ($providers as $p) partial('card', ['p' => $p, 'services' => $svc[(int)$p['id']] ?? []]); ?>
    </div>
    <p class="empty muted" data-empty hidden>Nothing matches all of those filters. Try removing one. <button type="button" class="linkbtn" data-reset>Clear all</button></p>
    <p class="muted small note">Missing a school or instructor? <a href="/en/join">They can list for free</a>. Prices are what each provider told us, with the date we last checked them on their profile.</p>
  </div>
</div>

<div class="cmpbar" data-cmpbar hidden>
  <div class="wrap cmpbar-in">
    <span><b data-cmpn>0</b> selected <span class="muted small">(up to 4)</span></span>
    <span class="cmp-names muted small" data-cmpnames></span>
    <button type="button" class="linkbtn" data-cmpclear>Clear</button>
    <a class="btn btn-primary btn-small" data-cmpgo href="/en/compare">Compare</a>
  </div>
</div>
