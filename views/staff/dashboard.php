<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('staff');
require_active_user();
$user = current_user();
$vehicles = (new Vehicle($pdo))->forUser((int)$user['id']);
$activeOtp = (new Otp($pdo))->activeForUser((int)$user['id']);
$page = ['title' => 'Staff Dashboard', 'active' => 'dashboard'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-1">Staff dashboard</h1>
<p class="text-muted mb-4">Register vehicles and request OTPs for campus entry.</p>
<div class="row g-3 mb-4">
  <div class="col-md-6"><div class="card stat-card"><div class="card-body"><div class="stat-label">Vehicles</div><div class="stat-value"><?= count($vehicles) ?></div></div></div></div>
  <div class="col-md-6"><div class="card stat-card"><div class="card-body"><div class="stat-label">Active OTP</div><div class="stat-value"><?= $activeOtp ? e($activeOtp['otp_code']) : '—' ?></div></div></div></div>
</div>
<div class="row g-3">
  <div class="col-md-4"><a class="btn btn-primary w-100" href="<?= e(url('views/staff/vehicles.php')) ?>">My vehicles</a></div>
  <div class="col-md-4"><a class="btn btn-accent w-100" href="<?= e(url('views/staff/otp.php')) ?>">Request OTP</a></div>
  <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="<?= e(url('views/staff/history.php')) ?>">Entry/exit history</a></div>
</div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
