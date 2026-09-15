<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('student');
require_active_user();
$list = (new EntryExitLog($pdo))->list(['user_id' => (int)current_user()['id']], 100);
$page = ['title' => 'History', 'active' => 'history'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-3">Your entry / exit history</h1>
<div class="card panel-card"><div class="card-body table-responsive">
<table class="table align-middle">
  <thead><tr><th>When</th><th>Type</th><th>Plate</th><th>Notes</th></tr></thead>
  <tbody>
  <?php if (!$list): ?>
    <tr><td colspan="4" class="text-muted">No records yet.</td></tr>
  <?php else: foreach ($list as $l): ?>
    <tr>
      <td class="small"><?= e(format_dt($l['logged_at'])) ?></td>
      <td><?= status_badge($l['log_type']) ?></td>
      <td><?= e($l['plate_number']) ?></td>
      <td class="small"><?= e($l['notes'] ?? '') ?></td>
    </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table>
</div></div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
