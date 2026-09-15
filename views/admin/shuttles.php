<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('admin');
require_active_user();

$model = new Shuttle($pdo);
$filters = [
  'q' => trim((string) request('q', '')),
  'status' => (string) request('status', ''),
];
$list = $model->listAll(array_filter($filters));
$page = ['title' => 'Shuttles', 'active' => 'shuttles'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-3">Shuttle buses</h1>
<form class="row g-2 mb-3" method="get">
  <div class="col-md-6"><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Plate, route, driver"></div>
  <div class="col-md-4">
    <select class="form-select" name="status">
      <option value="">All statuses</option>
      <?php foreach (['available','in_transit','offline','maintenance'] as $s): ?>
        <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_',' ',$s))) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>
<div class="card panel-card"><div class="card-body table-responsive">
<table class="table align-middle">
  <thead><tr><th>Plate</th><th>Driver</th><th>Route</th><th>Capacity</th><th>Status</th><th>Updated</th></tr></thead>
  <tbody>
  <?php if (!$list): ?>
    <tr><td colspan="6" class="text-muted">No shuttles registered.</td></tr>
  <?php else: foreach ($list as $s): ?>
    <tr>
      <td class="fw-semibold"><?= e($s['plate_number']) ?></td>
      <td><?= e($s['driver_name']) ?><div class="small text-muted"><?= e($s['driver_email']) ?></div></td>
      <td><?= e($s['route_name'] ?? '—') ?></td>
      <td><?= e((string)($s['capacity'] ?? '—')) ?></td>
      <td><?= status_badge($s['shuttle_status']) ?></td>
      <td class="small"><?= e(format_dt($s['last_status_at'] ?? $s['updated_at'])) ?></td>
    </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table>
</div></div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
