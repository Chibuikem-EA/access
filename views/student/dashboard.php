<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('student');
require_active_user();

$user = current_user();
$vehicles = new Vehicle($pdo);
$otpModel = new Otp($pdo);
$logs = new EntryExitLog($pdo);
$myVehicles = $vehicles->forUser((int)$user['id']);
$activeOtp = $otpModel->activeForUser((int)$user['id']);
$recentLogs = $logs->list(['user_id' => (int)$user['id']], 5);
$page = ['title' => 'Student Dashboard', 'active' => 'dashboard'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-1">Hello, <?= e($user['full_name']) ?></h1>
<p class="text-muted mb-4">Manage your vehicle and request gate entry OTPs.</p>
<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="stat-label">Vehicles</div><div class="stat-value"><?= count($myVehicles) ?></div></div></div></div>
  <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="stat-label">Active OTP</div><div class="stat-value"><?= $activeOtp ? e($activeOtp['otp_code']) : '—' ?></div></div></div></div>
  <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="stat-label">Recent gate events</div><div class="stat-value"><?= count($recentLogs) ?></div></div></div></div>
</div>
<div class="row g-3">
  <div class="col-md-4"><a class="btn btn-primary w-100" href="<?= e(url('views/student/vehicles.php')) ?>">My vehicles</a></div>
  <div class="col-md-4"><a class="btn btn-accent w-100" href="<?= e(url('views/student/otp.php')) ?>">Request OTP</a></div>
  <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="<?= e(url('views/student/history.php')) ?>">Entry/exit history</a></div>
</div>
<?php if ($activeOtp): ?>
<div class="card panel-card mt-4 border-success"><div class="card-body text-center">
  <div class="text-muted small">Show this OTP at the gate</div>
  <div class="otp-display"><?= e($activeOtp['otp_code']) ?></div>
  <div class="small">Plate <?= e($activeOtp['plate_number']) ?> · expires <?= e(format_dt($activeOtp['expires_at'])) ?></div>
</div></div>
<?php endif; ?>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
