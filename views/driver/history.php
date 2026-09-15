<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('driver');
require_active_user();
$otpHistory = (new Otp($pdo))->forUser((int)current_user()['id']);
$page = ['title' => 'OTP History', 'active' => 'history'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-3">OTP history</h1>
<div class="card panel-card"><div class="card-body table-responsive">
<table class="table align-middle">
  <thead><tr><th>Code</th><th>Plate</th><th>Status</th><th>Created</th><th>Expires</th><th>Used</th></tr></thead>
  <tbody>
  <?php if (!$otpHistory): ?>
    <tr><td colspan="6" class="text-muted">No OTP history.</td></tr>
  <?php else: foreach ($otpHistory as $o): ?>
    <tr>
      <td class="font-monospace"><?= e($o['otp_code']) ?></td>
      <td><?= e($o['plate_number']) ?></td>
      <td><?= status_badge($o['status']) ?></td>
      <td class="small"><?= e(format_dt($o['created_at'])) ?></td>
      <td class="small"><?= e(format_dt($o['expires_at'])) ?></td>
      <td class="small"><?= e(format_dt($o['used_at'] ?? null)) ?></td>
    </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table>
</div></div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
