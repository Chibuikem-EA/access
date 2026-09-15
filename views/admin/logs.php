<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('admin');
require_active_user();

$model = new EntryExitLog($pdo);
$filters = [
  'q' => trim((string) request('q', '')),
  'log_type' => (string) request('log_type', ''),
  'from' => (string) request('from', ''),
  'to' => (string) request('to', ''),
];
$list = $model->list(array_filter($filters), 300);
$page = ['title' => 'Entry/Exit Logs', 'active' => 'logs'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-3">Entry / exit history</h1>
<form class="row g-2 mb-3" method="get">
  <div class="col-md-3"><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Plate or name"></div>
  <div class="col-md-2">
    <select class="form-select" name="log_type">
      <option value="">All types</option>
      <option value="entry" <?= $filters['log_type'] === 'entry' ? 'selected' : '' ?>>Entry</option>
      <option value="exit" <?= $filters['log_type'] === 'exit' ? 'selected' : '' ?>>Exit</option>
    </select>
  </div>
  <div class="col-md-2"><input type="date" class="form-control" name="from" value="<?= e($filters['from']) ?>"></div>
  <div class="col-md-2"><input type="date" class="form-control" name="to" value="<?= e($filters['to']) ?>"></div>
  <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>
<div class="card panel-card"><div class="card-body table-responsive">
<table class="table align-middle">
  <thead><tr><th>When</th><th>Type</th><th>Plate</th><th>Owner / Visitor</th><th>Verified by</th><th>Notes</th></tr></thead>
  <tbody>
  <?php foreach ($list as $l): ?>
    <tr>
      <td class="small"><?= e(format_dt($l['logged_at'])) ?></td>
      <td><?= status_badge($l['log_type']) ?></td>
      <td><?= e($l['plate_number']) ?></td>
      <td><?= e($l['owner_name'] ?? $l['visitor_name'] ?? '—') ?></td>
      <td class="small"><?= e($l['verifier_name'] ?? '—') ?></td>
      <td class="small"><?= e($l['notes'] ?? '') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
