<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect(dashboard_for_role(current_user()['role']));
}

$campus = setting($pdo, 'campus_name', 'Campus Security Gate');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login · <?= e($config['app_name']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= e(asset('css/style.css')) ?>" rel="stylesheet">
</head>
<body class="login-page">
<div class="login-shell">
  <aside class="login-brand" aria-label="ACCEZZ branding">
    <div class="login-brand-inner">
      <p class="login-kicker">Vehicle entry · Shuttle monitor</p>
      <h1 class="login-logo"><?= e($config['app_name']) ?></h1>
      <p class="login-lede">Secure campus gate access with OTP-verified vehicle entry.</p>
      <p class="login-campus"><?= e($campus) ?></p>
    </div>
    <div class="login-brand-mark" aria-hidden="true"></div>
  </aside>

  <main class="login-panel">
    <div class="login-panel-inner">
      <h2 class="login-heading">Sign in</h2>
      <p class="login-sub">Use your approved campus account.</p>

      <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
      <?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

      <form method="post" action="<?= e(url('controllers/AuthController.php?action=login')) ?>" novalidate class="login-form">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input type="email" class="form-control" id="email" name="email" required value="<?= old('email') ?>" autocomplete="username">
        </div>
        <div class="mb-4">
          <label class="form-label" for="password">Password</label>
          <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
            <button type="button" class="btn btn-outline-secondary" data-toggle-password="password" aria-label="Show password" title="Show password">
              <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="btn login-submit w-100">Sign in</button>
      </form>

      <p class="login-footer">
        New user? <a href="<?= e(url('register.php')) ?>">Register an account</a>
      </p>
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
