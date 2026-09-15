<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('admin');
require_active_user();

$model = new AuditLog($pdo);
$filters = [
  'q' => trim((string) request('q', '')),
  'action' => (string) request('action', ''),
];
$list = $model->list(array_filter($filters), 250);
$page = ['title' => 'Audit Logs', 'active' => 'audit'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-3">Audit logs</h1>
<form class="row g-2 mb-3" method="get">
  <div class="col-md-6"><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Search action, details, user"></div>
  <div class="col-md-4"><input class="form-control" name="action" value="<?= e($filters['action']) ?>" placeholder="Exact action (optional)"></div>
  <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>
<div class="card panel-card"><div class="card-body table-responsive">
<table class="table align-middle table-sm">
  <thead><tr><th>When</th><th>User</th><th>Action</th><th>Entity</th><th>Details</th><th>IP</th></tr></thead>
  <tbody>
  <?php foreach ($list as $a): ?>
    <tr>
      <td class="small"><?= e(format_dt($a['created_at'])) ?></td>
      <td class="small"><?= e($a['full_name'] ?? 'System') ?></td>
      <td><code><?= e($a['action']) ?></code></td>
      <td class="small"><?= e(($a['entity_type'] ?? '') . ($a['entity_id'] ? ' #' . $a['entity_id'] : '')) ?></td>
      <td class="small"><?= e($a['details'] ?? '') ?></td>
      <td class="small"><?= e($a['ip_address'] ?? '') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
