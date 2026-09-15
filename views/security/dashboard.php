<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('security');
require_active_user();

$logs = new EntryExitLog($pdo);
$visitors = new Visitor($pdo);
$stats = [
  'entries' => $logs->countToday('entry'),
  'exits' => $logs->countToday('exit'),
  'visitors' => $visitors->countOnCampus(),
];
$today = $logs->list(['today' => true], 10);
$page = ['title' => 'Security Dashboard', 'active' => 'dashboard'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-1">Security dashboard</h1>
<p class="text-muted mb-4">Verify OTPs, log exits, and register visitors.</p>
<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="stat-label">Entries today</div><div class="stat-value"><?= $stats['entries'] ?></div></div></div></div>
  <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="stat-label">Exits today</div><div class="stat-value"><?= $stats['exits'] ?></div></div></div></div>
  <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="stat-label">Visitors on campus</div><div class="stat-value"><?= $stats['visitors'] ?></div></div></div></div>
</div>
<div class="row g-3 mb-4">
  <div class="col-md-4"><a class="btn btn-accent w-100 py-3" href="<?= e(url('views/security/verify_otp.php')) ?>"><i class="bi bi-shield-check me-2"></i>Verify OTP / Entry</a></div>
  <div class="col-md-4"><a class="btn btn-primary w-100 py-3" href="<?= e(url('views/security/exit.php')) ?>"><i class="bi bi-box-arrow-right me-2"></i>Record Exit</a></div>
  <div class="col-md-4"><a class="btn btn-outline-primary w-100 py-3" href="<?= e(url('views/security/visitors.php')) ?>"><i class="bi bi-person-plus me-2"></i>Register Visitor</a></div>
</div>
<div class="card panel-card"><div class="card-body">
  <h2 class="h5 mb-3">Today's verification history</h2>
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr><th>Time</th><th>Type</th><th>Plate</th><th>Person</th><th>Notes</th></tr></thead>
      <tbody>
      <?php if (!$today): ?>
        <tr><td colspan="5" class="text-muted">No activity yet today.</td></tr>
      <?php else: foreach ($today as $l): ?>
        <tr>
          <td class="small"><?= e(date('g:i A', strtotime($l['logged_at']))) ?></td>
          <td><?= status_badge($l['log_type']) ?></td>
          <td><?= e($l['plate_number']) ?></td>
          <td><?= e($l['owner_name'] ?? $l['visitor_name'] ?? '—') ?></td>
          <td class="small"><?= e($l['notes'] ?? '') ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div></div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
