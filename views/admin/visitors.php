<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('admin');
require_active_user();

$model = new Visitor($pdo);
$filters = [
  'q' => trim((string) request('q', '')),
  'status' => (string) request('status', ''),
];
$list = $model->list(array_filter($filters), 200);
$page = ['title' => 'Visitors', 'active' => 'visitors'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-3">Visitors</h1>
<form class="row g-2 mb-3" method="get">
  <div class="col-md-6"><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Name, plate, phone"></div>
  <div class="col-md-4">
    <select class="form-select" name="status">
      <option value="">All</option>
      <option value="on_campus" <?= $filters['status'] === 'on_campus' ? 'selected' : '' ?>>On campus</option>
      <option value="exited" <?= $filters['status'] === 'exited' ? 'selected' : '' ?>>Exited</option>
    </select>
  </div>
  <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>
<div class="card panel-card"><div class="card-body table-responsive">
<table class="table align-middle">
  <thead><tr><th>Name</th><th>Plate</th><th>Host</th><th>Status</th><th>Entry</th><th>Exit</th><th>Registered by</th></tr></thead>
  <tbody>
  <?php foreach ($list as $v): ?>
    <tr>
      <td><?= e($v['full_name']) ?><div class="small text-muted"><?= e($v['phone'] ?? '') ?></div></td>
      <td><?= e($v['plate_number']) ?></td>
      <td class="small"><?= e($v['host_name'] ?? '—') ?></td>
      <td><?= status_badge($v['status']) ?></td>
      <td class="small"><?= e(format_dt($v['entry_at'])) ?></td>
      <td class="small"><?= e(format_dt($v['exit_at'] ?? null)) ?></td>
      <td class="small"><?= e($v['registrar_name'] ?? '—') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
