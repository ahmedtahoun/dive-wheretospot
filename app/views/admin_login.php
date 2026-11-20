<section class="pagehead"><div class="wrap narrow">
  <h1>Admin</h1>
  <?php if (!empty($error)): ?><div class="notice err" role="alert"><?= h($error) ?></div><?php endif; ?>
  <?php if (!cfg('admin_password_hash') && !is_local()): ?>
    <div class="notice warn">Admin is switched off: set <code>admin_password_hash</code> in config.local.php on the server.</div>
  <?php else: ?>
  <form class="form card" method="post" action="/admin/login">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <label>Password <input type="password" name="password" required autofocus autocomplete="current-password"></label>
    <?php if (!cfg('admin_password_hash') && is_local()): ?><p class="muted small">Local testing password: <code>dive-local</code></p><?php endif; ?>
    <button class="btn btn-primary" type="submit">Log in</button>
  </form>
  <?php endif; ?>
</div></section>
