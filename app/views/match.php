<?php
$top = array_values(array_filter($results, fn($r) => !$r['blocked'] && $r['score'] >= 55));
$close = false;
if ($asked && !$top) { $top = array_slice(array_values(array_filter($results, fn($r) => !$r['blocked'])), 0, 3); $close = true; }
$top = array_slice($top, 0, 3);
$topSlugs = array_map(fn($r) => $r['p']['slug'], $top);
$rest = array_values(array_filter($results, fn($r) => !in_array($r['p']['slug'], $topSlugs, true)));
$covers = provider_covers();
$foot = '<script src="/assets/match.js?v=' . @filemtime(ROOT_DIR . '/assets/match.js') . '" defer></script>';
?>
<section class="pagehead slim">
  <div class="wrap narrow">
    <p class="eyebrow">1 minute · no sign-up</p>
    <h1>Find your freediving match</h1>
    <p class="lead">Tell us where you are and what you need. We'll show the instructors in Dahab who fit you best, and why.</p>
  </div>
</section>

<div class="wrap narrow">
  <details class="quizwrap"<?= $asked ? '' : ' open' ?>>
  <summary><?= $asked ? 'Change your answers' : 'Your answers' ?></summary>
  <form class="quiz" method="get" action="/en/match#results" data-quiz>
    <input type="hidden" name="go" value="1">
    <fieldset class="qstep">
      <legend><span class="qn">1</span> Your experience</legend>
      <div class="qcards">
        <?php foreach (MATCH_EXP as $k => [$t, $sub]): ?>
          <label class="qcard"><input type="radio" name="exp" value="<?= $k ?>"<?= $in['exp'] === $k ? ' checked' : '' ?> required><b><?= h($t) ?></b><span><?= h($sub) ?></span></label>
        <?php endforeach; ?>
      </div>
    </fieldset>
    <fieldset class="qstep">
      <legend><span class="qn">2</span> What do you want?</legend>
      <div class="qcards">
        <?php foreach (MATCH_GOALS as $k => [$t, $sub]): ?>
          <label class="qcard"><input type="radio" name="goal" value="<?= $k ?>"<?= $in['goal'] === $k ? ' checked' : '' ?> required><b><?= h($t) ?></b><span><?= h($sub) ?></span></label>
        <?php endforeach; ?>
      </div>
    </fieldset>
    <fieldset class="qstep">
      <legend><span class="qn">3</span> Anything important to you? <span class="muted small">(optional)</span></legend>
      <div class="qchips">
        <?php foreach (MATCH_NEEDS as $k => $t): ?>
          <label class="qchip"><input type="checkbox" name="needs[]" value="<?= $k ?>"<?= in_array($k, $in['needs'], true) ? ' checked' : '' ?>><span><?= h($t) ?></span></label>
        <?php endforeach; ?>
      </div>
    </fieldset>
    <fieldset class="qstep">
      <legend><span class="qn">4</span> The details</legend>
      <div class="row3">
        <label>Language <select name="lang"><option value="">Any</option><?php foreach (LANGS as $l): ?><option<?= $in['lang'] === $l ? ' selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select></label>
        <label>When <select name="month"><option value="0">Not sure yet</option><?php foreach (MONTHS as $n => $m): ?><option value="<?= $n ?>"<?= $in['month'] === $n ? ' selected' : '' ?>><?= $m ?></option><?php endforeach; ?></select></label>
        <label>Course budget <select name="budget"><?php foreach (MATCH_BUDGETS as $v => $t): ?><option value="<?= $v ?>"<?= $in['budget'] === $v ? ' selected' : '' ?>><?= h($t) ?></option><?php endforeach; ?></select></label>
      </div>
    </fieldset>
    <button class="btn btn-coral btn-big" type="submit">Show my matches</button>
  </form>
  </details>
</div>

<?php if ($asked): ?>
<section class="wrap narrow matchres" id="results">
  <h2><?= $close ? 'No perfect match, but these come closest' : 'Your best matches' ?></h2>
  <p class="muted small"><?= h(match_summary($in)) ?></p>

  <?php if (!$top): ?>
    <p class="notice warn">Nobody fits all of that yet. Try another language or month, or <a href="/en/freediving">browse everyone</a>.</p>
  <?php endif; ?>

  <div class="mcards">
    <?php foreach ($top as $i => $r): $p = $r['p']; $cover = $covers[(int)$p['id']] ?? null; ?>
      <article class="mcard">
        <a class="mcard-img" href="/en/p/<?= h($p['slug']) ?>" style="--h:<?= avatar_hue($p['name']) ?>">
          <?php if ($cover): ?><img src="<?= h(img_src($cover, 500)) ?>" alt="" loading="lazy"><?php else: ?><span class="pcard-initials"><?= h(initials($p['name'])) ?></span><?php endif; ?>
          <?php if ($p['is_sample']): ?><span class="tag tag-sample">Sample</span><?php endif; ?>
        </a>
        <div class="mcard-body">
          <div class="mcard-head">
            <div>
              <h3><a href="/en/p/<?= h($p['slug']) ?>"><?= h($p['name']) ?></a><?php if ($p['verified']): ?> <span class="vtick" aria-label="Verified">✓</span><?php endif; ?></h3>
              <p class="pcard-meta"><?= h(PROVIDER_TYPES[$p['type']] ?? '') ?><?= $p['base_area'] ? ' · ' . h($p['base_area']) : '' ?></p>
            </div>
            <span class="score" style="--s:<?= (int)$r['score'] ?>" aria-label="<?= (int)$r['score'] ?>% match"><b><?= (int)$r['score'] ?>%</b><small>match</small></span>
          </div>
          <ul class="why">
            <?php foreach (array_slice(array_filter($r['why'], fn($w) => $w[0]), 0, 4) as [, $t]): ?><li class="good"><?= h($t) ?></li><?php endforeach; ?>
            <?php foreach (array_slice(array_filter($r['why'], fn($w) => !$w[0]), 0, 2) as [, $t]): ?><li class="bad"><?= h($t) ?></li><?php endforeach; ?>
          </ul>
          <div class="mcard-foot">
            <label class="check"><input type="checkbox" name="to[]" value="<?= h($p['slug']) ?>" form="ask" checked> Ask them</label>
            <a class="btn btn-small btn-ghost" href="/en/p/<?= h($p['slug']) ?>">Profile</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if ($top): ?>
  <form class="form card askall" id="ask" method="post" action="/en/match/ask">
    <h2>Ask <?= count($top) > 1 ? 'all ' . count($top) : 'them' ?> in one message</h2>
    <p class="muted small">We send your answers above with your message, so they can reply with the right course and dates.</p>
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <div class="hp" aria-hidden="true"><label>Leave empty <input name="website_url" tabindex="-1" autocomplete="off"></label></div>
    <input type="hidden" name="exp" value="<?= h($in['exp']) ?>"><input type="hidden" name="goal" value="<?= h($in['goal']) ?>">
    <?php foreach ($in['needs'] as $n): ?><input type="hidden" name="needs[]" value="<?= h($n) ?>"><?php endforeach; ?>
    <input type="hidden" name="lang" value="<?= h($in['lang']) ?>"><input type="hidden" name="month" value="<?= (int)$in['month'] ?>"><input type="hidden" name="budget" value="<?= (int)$in['budget'] ?>">
    <div class="row2">
      <label>Your name <input name="name" required maxlength="80" autocomplete="name"></label>
      <label>WhatsApp <span class="muted small">(with country code)</span><input name="whatsapp" inputmode="tel" maxlength="30" autocomplete="tel"></label>
    </div>
    <div class="row3">
      <label>Email <input name="email" type="email" maxlength="120" autocomplete="email"></label>
      <label>Dates <input name="dates" maxlength="80" placeholder="e.g. 12–16 March"></label>
      <label>People <input name="people" type="number" min="1" max="20" value="1"></label>
    </div>
    <label>Anything else? <textarea name="message" rows="2" maxlength="1500"></textarea></label>
    <p class="muted small">Add a WhatsApp number or an email so they can reply. We share it only with the instructors you ticked. <a href="/en/privacy">Privacy</a></p>
    <button class="btn btn-coral" type="submit">Send to my matches</button>
  </form>
  <?php endif; ?>

  <?php if ($rest): ?>
  <details class="others">
    <summary>Other instructors (<?= count($rest) ?>)</summary>
    <ul class="olist">
      <?php foreach ($rest as $r): $p = $r['p']; $bad = array_values(array_filter($r['why'], fn($w) => !$w[0])); ?>
        <li><a href="/en/p/<?= h($p['slug']) ?>"><b><?= h($p['name']) ?></b></a> <span class="muted small"><?= (int)$r['score'] ?>%<?= $bad ? ' · ' . h($bad[0][1]) : '' ?></span></li>
      <?php endforeach; ?>
    </ul>
  </details>
  <?php endif; ?>
</section>
<?php endif; ?>
