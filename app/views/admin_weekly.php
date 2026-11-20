<?php partial('adminnav'); ?>
<section class="wrap section">
  <h1 class="h2">Weekly check</h1>
  <p class="muted">Every Monday: tap each button to open WhatsApp with a ready message. The provider taps the link and answers Yes or No themselves. "Teaching this week" switches off on its own after 7 days.</p>
  <div class="cmp-scroll">
  <table class="admintable">
    <thead><tr><th>Provider</th><th>This week</th><th>Send</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $p): ?>
      <tr>
        <td><b><?= h($p['name']) ?></b><br><span class="muted small"><?= h($p['whatsapp']) ?></span></td>
        <td class="small"><?= $p['teaching_week'] ? '● On since ' . h($p['week_updated']) : '<span class="muted">Off</span>' ?></td>
        <td><a class="btn btn-small btn-wa" target="_blank" rel="noopener" href="<?= h(wa_link($p['whatsapp'], "Hi {$p['name']}! Teaching this week? One tap to update your Where To Spot Dive profile: " . me_url($p, '/week'))) ?>">WhatsApp</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</section>
