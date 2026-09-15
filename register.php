<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect(dashboard_for_role(current_user()['role']));
}
if (setting($pdo, 'allow_registration', '1') !== '1') {
    flash('error', 'Registration is currently disabled.');
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register · <?= e($config['app_name']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= e(asset('css/style.css')) ?>" rel="stylesheet">
</head>
<body>
<div class="auth-wrap">
  <div class="card auth-card wide">
    <div class="card-body p-4 p-md-5">
      <h1 class="h4 mb-1">Create account</h1>
      <p class="text-muted small">Accounts require administrator approval before login.</p>

      <?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

      <form method="post" action="<?= e(url('controllers/AuthController.php?action=register')) ?>" class="row g-3" novalidate>
        <?= csrf_field() ?>
        <div class="col-12">
          <label class="form-label" for="full_name">Full name</label>
          <input class="form-control" id="full_name" name="full_name" required value="<?= old('full_name') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="email">Email</label>
          <input type="email" class="form-control" id="email" name="email" required value="<?= old('email') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="phone">Phone</label>
          <input class="form-control" id="phone" name="phone" value="<?= old('phone') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="role">Role</label>
          <select class="form-select" id="role" name="role" required>
            <?php foreach (['student' => 'Student', 'staff' => 'Staff', 'driver' => 'Shuttle Driver'] as $val => $label): ?>
              <option value="<?= $val ?>" <?= old('role', 'student') === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6" data-role-field="student">
          <label class="form-label" for="student_id">Student ID</label>
          <input class="form-control" id="student_id" name="student_id" value="<?= old('student_id') ?>">
        </div>
        <div class="col-md-6 d-none" data-role-field="staff,driver">
          <label class="form-label" for="staff_id">Staff / Driver ID</label>
          <input class="form-control" id="staff_id" name="staff_id" value="<?= old('staff_id') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="password">Password</label>
          <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" required minlength="8" autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary" data-toggle-password="password" aria-label="Show password" title="Show password">
              <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="password_confirm">Confirm password</label>
          <div class="input-group">
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="8" autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary" data-toggle-password="password_confirm" aria-label="Show password" title="Show password">
              <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
          </div>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary w-100">Submit registration</button>
        </div>
      </form>
      <p class="text-center small mt-3 mb-0">
        Already registered? <a href="<?= e(url('login.php')) ?>">Sign in</a>
      </p>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
