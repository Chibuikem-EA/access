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

$stats = [
  'users' => $users->countByStatus(),
  'pending_users' => $users->countByStatus('pending'),
  'vehicles' => $vehicles->count(),
  'pending_vehicles' => $vehicles->count('pending'),
  'entries_today' => $logs->countToday('entry'),
  'exits_today' => $logs->countToday('exit'),
  'otps_today' => $otp->countToday(),
  'visitors_on_campus' => $visitors->countOnCampus(),
];
$recentLogs = $logs->list(['today' => true], 8);
$pendingUsers = $users->list(['status' => 'pending'], 8);
$page = ['title' => 'Admin Dashboard', 'active' => 'dashboard'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="h3 mb-1">Administrator dashboard</h1>
    <p class="text-muted mb-0"><?= e(setting($pdo, 'campus_name', 'Campus Security Gate')) ?></p>
  </div>
</div>
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Users', $stats['users'], 'bi-people', 'pending: ' . $stats['pending_users']],
    ['Vehicles', $stats['vehicles'], 'bi-car-front', 'pending: ' . $stats['pending_vehicles']],
    ['Entries today', $stats['entries_today'], 'bi-box-arrow-in-right', 'exits: ' . $stats['exits_today']],
    ['OTPs today', $stats['otps_today'], 'bi-key', 'visitors on campus: ' . $stats['visitors_on_campus']],
  ];
  foreach ($cards as [$label, $value, $icon, $sub]): ?>
  <div class="col-6 col-xl-3">
    <div class="card stat-card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div>
            <div class="stat-label"><?= e($label) ?></div>
            <div class="stat-value"><?= (int)$value ?></div>
            <div class="small text-muted"><?= e($sub) ?></div>
          </div>
          <i class="bi <?= e($icon) ?> fs-3 text-secondary opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<div class="row g-4">
  <div class="col-lg-6">
    <div class="card panel-card">
      <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
          <h2 class="h5 mb-0">Pending account approvals</h2>
          <a href="<?= e(url('views/admin/users.php?status=pending')) ?>">View all</a>
        </div>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>Name</th><th>Role</th><th></th></tr></thead>
            <tbody>
            <?php if (!$pendingUsers): ?>
              <tr><td colspan="3" class="text-muted">No pending users.</td></tr>
            <?php else: foreach ($pendingUsers as $u): ?>
              <tr>
                <td><?= e($u['full_name']) ?><div class="small text-muted"><?= e($u['email']) ?></div></td>
                <td><?= e(role_label($u['role'])) ?></td>
                <td class="text-end text-nowrap">
                  <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#dashView<?= (int)$u['id'] ?>">View</button>
                  <form class="d-inline" method="post" action="<?= e(url('controllers/AdminController.php?action=approve_user')) ?>">
                    <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <button class="btn btn-sm btn-success">Approve</button>
                  </form>
                  <form class="d-inline" method="post" action="<?= e(url('controllers/AdminController.php?action=reject_user')) ?>">
                    <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" data-confirm="Reject this user?">Reject</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card panel-card">
      <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
          <h2 class="h5 mb-0">Today's gate activity</h2>
          <a href="<?= e(url('views/admin/logs.php')) ?>">View logs</a>
        </div>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>Time</th><th>Type</th><th>Plate</th><th>Person</th></tr></thead>
            <tbody>
            <?php if (!$recentLogs): ?>
              <tr><td colspan="4" class="text-muted">No activity today.</td></tr>
            <?php else: foreach ($recentLogs as $l): ?>
              <tr>
                <td class="small"><?= e(date('g:i A', strtotime($l['logged_at']))) ?></td>
                <td><?= status_badge($l['log_type']) ?></td>
                <td><?= e($l['plate_number']) ?></td>
                <td class="small"><?= e($l['owner_name'] ?? $l['visitor_name'] ?? '—') ?></td>
              </tr>
            <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php foreach ($pendingUsers as $u): ?>
<div class="modal fade" id="dashView<?= (int)$u['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registration details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning py-2 small mb-3">Review this information before approving or rejecting the account.</div>
        <dl class="row mb-0">
          <dt class="col-sm-4">Full name</dt>
          <dd class="col-sm-8"><?= e($u['full_name']) ?></dd>
          <dt class="col-sm-4">Email</dt>
          <dd class="col-sm-8"><?= e($u['email']) ?></dd>
          <dt class="col-sm-4">Phone</dt>
          <dd class="col-sm-8"><?= e($u['phone'] ?: '—') ?></dd>
          <dt class="col-sm-4">Role</dt>
          <dd class="col-sm-8"><?= e(role_label($u['role'])) ?></dd>
          <?php if (($u['role'] ?? '') === 'student'): ?>
            <dt class="col-sm-4">Student ID</dt>
            <dd class="col-sm-8"><?= e($u['student_id'] ?: '—') ?></dd>
          <?php else: ?>
            <dt class="col-sm-4">Staff / Driver ID</dt>
            <dd class="col-sm-8"><?= e($u['staff_id'] ?: '—') ?></dd>
          <?php endif; ?>
          <dt class="col-sm-4">Department</dt>
          <dd class="col-sm-8"><?= e($u['department'] ?: '—') ?></dd>
          <dt class="col-sm-4">Registered</dt>
          <dd class="col-sm-8"><?= e(format_dt($u['created_at'])) ?></dd>
        </dl>
      </div>
      <div class="modal-footer">
        <form method="post" action="<?= e(url('controllers/AdminController.php?action=reject_user')) ?>" class="me-auto">
          <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
          <button type="submit" class="btn btn-outline-danger" data-confirm="Reject this registration?">Reject</button>
        </form>
        <form method="post" action="<?= e(url('controllers/AdminController.php?action=approve_user')) ?>">
          <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
          <button type="submit" class="btn btn-success">Approve account</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
