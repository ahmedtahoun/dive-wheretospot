<section class="pagehead">
  <div class="wrap narrow">
    <p class="eyebrow">Step 2 of 2</p>
    <h1>Thanks, <?= h($name) ?>! Here is your private page.</h1>
    <p class="lead">Add your courses, prices, photos and links there. We check your details and approve you, usually within two days. Everything you add goes live together.</p>
    <div class="card linkcard">
      <p class="small"><b>Save this link.</b> It's your login: anyone who has it can edit your profile, so keep it to yourself.</p>
      <input class="linkbox" readonly value="<?= h($link) ?>" onclick="this.select()">
      <div class="actions">
        <a class="btn btn-primary" href="<?= h($link) ?>">Open my page →</a>
        <a class="btn btn-wa" target="_blank" rel="noopener" href="<?= h(wa_link($whatsapp, 'My Where To Spot Dive private page (keep it private): ' . $link)) ?>">Send it to my WhatsApp</a>
      </div>
      <?php if ($emailed): ?><p class="muted small">We also emailed it to you.</p><?php endif; ?>
    </div>
  </div>
</section>
