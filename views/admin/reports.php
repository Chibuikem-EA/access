<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('admin');
require_active_user();

$users = new User($pdo);
$vehicles = new Vehicle($pdo);
$logs = new EntryExitLog($pdo);
$otp = new Otp($pdo);
$visitors = new Visitor($pdo);
$shuttles = new Shuttle($pdo);

$from = (string) request('from', date('Y-m-d', strtotime('-6 days')));
$to = (string) request('to', date('Y-m-d'));
$export = (string) request('export', '');

$filteredLogs = $logs->list(['from' => $from, 'to' => $to], 5000);

if ($export === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="entry_exit_report_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Logged At', 'Type', 'Plate', 'Owner/Visitor', 'Verified By', 'Notes']);
    foreach ($filteredLogs as $l) {
        fputcsv($out, [
            $l['logged_at'],
            $l['log_type'],
            $l['plate_number'],
            $l['owner_name'] ?? $l['visitor_name'] ?? '',
            $l['verifier_name'] ?? '',
            $l['notes'] ?? '',
        ]);
    }
    fclose($out);
    exit;
}

$daily = $logs->dailyCounts(7);
$roleCounts = $users->countByRole();
$shuttleCounts = $shuttles->countByStatus();

$page = ['title' => 'Reports', 'active' => 'reports'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h1 class="h3 mb-0">Reports & analytics</h1>
  <a class="btn btn-outline-primary" href="?from=<?= e(urlencode($from)) ?>&to=<?= e(urlencode($to)) ?>&export=csv">
    <i class="bi bi-download me-1"></i>Export CSV
  </a>
</div>
<form class="row g-2 mb-4" method="get">
  <div class="col-auto"><label class="col-form-label">From</label></div>
  <div class="col-md-3"><input type="date" class="form-control" name="from" value="<?= e($from) ?>"></div>
  <div class="col-auto"><label class="col-form-label">To</label></div>
  <div class="col-md-3"><input type="date" class="form-control" name="to" value="<?= e($to) ?>"></div>
  <div class="col-md-2"><button class="btn btn-primary">Apply</button></div>
</form>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="stat-label">Total users</div><div class="stat-value"><?= $users->countByStatus() ?></div></div></div></div>
  <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="stat-label">Vehicles</div><div class="stat-value"><?= $vehicles->count() ?></div></div></div></div>
  <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="stat-label">Gate events (range)</div><div class="stat-value"><?= count($filteredLogs) ?></div></div></div></div>
  <div class="col-md-3"><div class="card stat-card"><div class="card-body"><div class="stat-label">OTPs today</div><div class="stat-value"><?= $otp->countToday() ?></div></div></div></div>
</div>
<div class="row g-4">
  <div class="col-lg-6">
    <div class="card panel-card"><div class="card-body">
      <h2 class="h5">Users by role</h2>
      <ul class="list-group list-group-flush">
        <?php foreach ($roleCounts as $r): ?>
          <li class="list-group-item d-flex justify-content-between"><span><?= e(role_label($r['role'])) ?></span><strong><?= (int)$r['total'] ?></strong></li>
        <?php endforeach; ?>
      </ul>
    </div></div>
  </div>
  <div class="col-lg-6">
    <div class="card panel-card"><div class="card-body">
      <h2 class="h5">Shuttle status</h2>
      <?php if (!$shuttleCounts): ?>
        <p class="text-muted mb-0">No shuttle data.</p>
      <?php else: ?>
      <ul class="list-group list-group-flush">
        <?php foreach ($shuttleCounts as $s): ?>
          <li class="list-group-item d-flex justify-content-between"><span><?= e(ucfirst(str_replace('_',' ',$s['shuttle_status']))) ?></span><strong><?= (int)$s['total'] ?></strong></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div></div>
  </div>
  <div class="col-12">
    <div class="card panel-card"><div class="card-body">
      <h2 class="h5">Last 7 days gate activity</h2>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead><tr><th>Date</th><th>Type</th><th>Count</th></tr></thead>
          <tbody>
          <?php if (!$daily): ?>
            <tr><td colspan="3" class="text-muted">No data.</td></tr>
          <?php else: foreach ($daily as $d): ?>
            <tr><td><?= e($d['d']) ?></td><td><?= status_badge($d['log_type']) ?></td><td><?= (int)$d['total'] ?></td></tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
</div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
