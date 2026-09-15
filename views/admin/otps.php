<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('admin');
require_active_user();

$model = new Otp($pdo);
$filters = [
  'q' => trim((string) request('q', '')),
  'status' => (string) request('status', ''),
];
$list = $model->listAll(array_filter($filters), 200);
$page = ['title' => 'OTP Requests', 'active' => 'otps'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-3">OTP requests</h1>
<form class="row g-2 mb-3" method="get">
  <div class="col-md-6"><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Code, plate, name"></div>
  <div class="col-md-4">
    <select class="form-select" name="status">
      <option value="">All statuses</option>
      <?php foreach (['active','used','expired','revoked'] as $s): ?>
        <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>
<div class="card panel-card"><div class="card-body table-responsive">
<table class="table align-middle">
  <thead><tr><th>Code</th><th>User</th><th>Plate</th><th>Status</th><th>Created</th><th>Expires</th><th>Used</th></tr></thead>
  <tbody>
  <?php foreach ($list as $o): ?>
    <tr>
      <td class="font-monospace"><?= e($o['otp_code']) ?></td>
      <td><?= e($o['full_name']) ?></td>
      <td><?= e($o['plate_number']) ?></td>
      <td><?= status_badge($o['status']) ?></td>
      <td class="small"><?= e(format_dt($o['created_at'])) ?></td>
      <td class="small"><?= e(format_dt($o['expires_at'])) ?></td>
      <td class="small"><?= e(format_dt($o['used_at'] ?? null)) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
