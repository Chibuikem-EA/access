<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('admin');
require_active_user();

$model = new Vehicle($pdo);
$filters = [
  'q' => trim((string) request('q', '')),
  'status' => (string) request('status', ''),
];
$list = $model->list(array_filter($filters), 200);
$page = ['title' => 'Vehicles', 'active' => 'vehicles'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-3">Vehicles</h1>
<form class="row g-2 mb-3" method="get">
  <div class="col-md-6"><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Search plate, owner, make"></div>
  <div class="col-md-4">
    <select class="form-select" name="status">
      <option value="">All statuses</option>
      <?php foreach (['pending','approved','rejected','inactive'] as $s): ?>
        <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>
<div class="card panel-card">
  <div class="card-body table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Plate</th><th>Owner</th><th>Details</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($list as $v): ?>
        <tr>
          <td class="fw-semibold"><?= e($v['plate_number']) ?></td>
          <td><?= e($v['full_name']) ?><div class="small text-muted"><?= e(role_label($v['role'])) ?></div></td>
          <td class="small"><?= e(trim(($v['make'] ?? '') . ' ' . ($v['model'] ?? '')) ?: '—') ?> · <?= e(ucfirst($v['vehicle_type'])) ?></td>
          <td><?= status_badge($v['status']) ?></td>
          <td class="text-nowrap">
            <?php if ($v['status'] !== 'approved'): ?>
              <form class="d-inline" method="post" action="<?= e(url('controllers/AdminController.php?action=approve_vehicle')) ?>">
                <?= csrf_field() ?><input type="hidden" name="vehicle_id" value="<?= (int)$v['id'] ?>">
                <button class="btn btn-sm btn-success">Approve</button>
              </form>
            <?php endif; ?>
            <?php if ($v['status'] === 'pending'): ?>
              <form class="d-inline" method="post" action="<?= e(url('controllers/AdminController.php?action=reject_vehicle')) ?>">
                <?= csrf_field() ?><input type="hidden" name="vehicle_id" value="<?= (int)$v['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Reject</button>
              </form>
            <?php endif; ?>
            <?php if ($v['status'] === 'approved'): ?>
              <form class="d-inline" method="post" action="<?= e(url('controllers/AdminController.php?action=deactivate_vehicle')) ?>">
                <?= csrf_field() ?><input type="hidden" name="vehicle_id" value="<?= (int)$v['id'] ?>">
                <button class="btn btn-sm btn-outline-secondary">Deactivate</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
