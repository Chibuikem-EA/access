<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('security');
require_active_user();

$model = new EntryExitLog($pdo);
$filters = [
  'q' => trim((string) request('q', '')),
  'log_type' => (string) request('log_type', ''),
  'today' => request('all') ? null : true,
];
if (request('all')) {
    unset($filters['today']);
}
$list = $model->list(array_filter($filters, static fn($v) => $v !== null && $v !== ''), 200);
$page = ['title' => 'History', 'active' => 'history'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h1 class="h3 mb-0">Entry / exit history</h1>
  <div>
    <a class="btn btn-sm <?= !request('all') ? 'btn-primary' : 'btn-outline-primary' ?>" href="?">Today</a>
    <a class="btn btn-sm <?= request('all') ? 'btn-primary' : 'btn-outline-primary' ?>" href="?all=1">All</a>
  </div>
</div>
<form class="row g-2 mb-3" method="get">
  <?php if (request('all')): ?><input type="hidden" name="all" value="1"><?php endif; ?>
  <div class="col-md-6"><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Plate or name"></div>
  <div class="col-md-4">
    <select class="form-select" name="log_type">
      <option value="">All types</option>
      <option value="entry" <?= $filters['log_type'] === 'entry' ? 'selected' : '' ?>>Entry</option>
      <option value="exit" <?= $filters['log_type'] === 'exit' ? 'selected' : '' ?>>Exit</option>
    </select>
  </div>
  <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>
<div class="card panel-card"><div class="card-body table-responsive">
<table class="table align-middle">
  <thead><tr><th>When</th><th>Type</th><th>Plate</th><th>Person</th><th>Verified by</th></tr></thead>
  <tbody>
  <?php foreach ($list as $l): ?>
    <tr>
      <td class="small"><?= e(format_dt($l['logged_at'])) ?></td>
      <td><?= status_badge($l['log_type']) ?></td>
      <td><?= e($l['plate_number']) ?></td>
      <td><?= e($l['owner_name'] ?? $l['visitor_name'] ?? '—') ?></td>
      <td class="small"><?= e($l['verifier_name'] ?? '—') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
