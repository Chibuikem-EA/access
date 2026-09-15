<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('driver');
require_active_user();
$user = current_user();
$shuttles = (new Shuttle($pdo))->forDriver((int)$user['id']);
$activeOtp = (new Otp($pdo))->activeForUser((int)$user['id']);
$page = ['title' => 'Driver Dashboard', 'active' => 'dashboard'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-1">Shuttle driver dashboard</h1>
<p class="text-muted mb-4">Request gate OTPs and keep shuttle status up to date.</p>
<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="stat-label">Shuttles</div><div class="stat-value"><?= count($shuttles) ?></div></div></div></div>
  <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="stat-label">Active OTP</div><div class="stat-value"><?= $activeOtp ? e($activeOtp['otp_code']) : '—' ?></div></div></div></div>
  <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="stat-label">Current status</div><div class="stat-value fs-5"><?= $shuttles ? e(str_replace('_',' ', $shuttles[0]['shuttle_status'])) : '—' ?></div></div></div></div>
</div>
<div class="row g-3">
  <div class="col-md-3"><a class="btn btn-primary w-100" href="<?= e(url('views/driver/vehicles.php')) ?>">Vehicle</a></div>
  <div class="col-md-3"><a class="btn btn-accent w-100" href="<?= e(url('views/driver/otp.php')) ?>">Request OTP</a></div>
  <div class="col-md-3"><a class="btn btn-outline-primary w-100" href="<?= e(url('views/driver/shuttle.php')) ?>">Shuttle status</a></div>
  <div class="col-md-3"><a class="btn btn-outline-secondary w-100" href="<?= e(url('views/driver/history.php')) ?>">OTP history</a></div>
</div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
