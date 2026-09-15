<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('staff');
require_active_user();
$list = (new EntryExitLog($pdo))->list(['user_id' => (int)current_user()['id']], 100);
$page = ['title' => 'Entry / Exit', 'active' => 'history'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-3">Your entry / exit history</h1>
<div class="card panel-card mb-4"><div class="card-body table-responsive">
<h2 class="h6">Gate logs</h2>
<table class="table align-middle">
  <thead><tr><th>When</th><th>Type</th><th>Plate</th></tr></thead>
  <tbody>
  <?php if (!$list): ?><tr><td colspan="3" class="text-muted">No gate records.</td></tr>
  <?php else: foreach ($list as $l): ?>
    <tr><td class="small"><?= e(format_dt($l['logged_at'])) ?></td><td><?= status_badge($l['log_type']) ?></td><td><?= e($l['plate_number']) ?></td></tr>
  <?php endforeach; endif; ?>
  </tbody>
</table>
</div></div>
<?php
$otpHistory = (new Otp($pdo))->forUser((int)current_user()['id']);
?>
<div class="card panel-card"><div class="card-body table-responsive">
<h2 class="h6">OTP history</h2>
<table class="table align-middle">
  <thead><tr><th>Code</th><th>Plate</th><th>Status</th><th>Created</th></tr></thead>
  <tbody>
  <?php foreach ($otpHistory as $o): ?>
    <tr>
      <td class="font-monospace"><?= e($o['otp_code']) ?></td>
      <td><?= e($o['plate_number']) ?></td>
      <td><?= status_badge($o['status']) ?></td>
      <td class="small"><?= e(format_dt($o['created_at'])) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
