<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('admin');
require_active_user();

$userModel = new User($pdo);
$filters = [
  'q' => trim((string) request('q', '')),
  'role' => (string) request('role', ''),
  'status' => (string) request('status', ''),
];
$list = $userModel->list(array_filter($filters), 200);
$page = ['title' => 'Manage Users', 'active' => 'users'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h1 class="h3 mb-0">Users</h1>
</div>
<form class="row g-2 mb-3" method="get">
  <div class="col-md-4"><input class="form-control" name="q" placeholder="Search name, email, phone" value="<?= e($filters['q']) ?>"></div>
  <div class="col-md-3">
    <select class="form-select" name="role">
      <option value="">All roles</option>
      <?php foreach (['admin','security','student','staff','driver'] as $r): ?>
        <option value="<?= $r ?>" <?= $filters['role'] === $r ? 'selected' : '' ?>><?= e(role_label($r)) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <select class="form-select" name="status">
      <option value="">All statuses</option>
      <?php foreach (['pending','active','inactive','rejected'] as $s): ?>
        <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>
<div class="card panel-card">
  <div class="card-body table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Name</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($list as $u): ?>
        <tr>
          <td>
            <div class="fw-semibold"><?= e($u['full_name']) ?></div>
            <div class="small text-muted"><?= e($u['email']) ?></div>
          </td>
          <td><?= e(role_label($u['role'])) ?></td>
          <td><?= status_badge($u['status']) ?></td>
          <td class="small"><?= e(format_dt($u['created_at'])) ?></td>
          <td class="text-nowrap">
            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#view<?= (int)$u['id'] ?>">
              View
            </button>
            <?php if ($u['status'] === 'pending'): ?>
              <form class="d-inline" method="post" action="<?= e(url('controllers/AdminController.php?action=approve_user')) ?>">
                <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                <button class="btn btn-sm btn-success">Approve</button>
              </form>
              <form class="d-inline" method="post" action="<?= e(url('controllers/AdminController.php?action=reject_user')) ?>">
                <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Reject</button>
              </form>
            <?php elseif ($u['status'] === 'active'): ?>
              <form class="d-inline" method="post" action="<?= e(url('controllers/AdminController.php?action=deactivate_user')) ?>">
                <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                <button class="btn btn-sm btn-outline-secondary" data-confirm="Deactivate user?">Deactivate</button>
              </form>
            <?php else: ?>
              <form class="d-inline" method="post" action="<?= e(url('controllers/AdminController.php?action=activate_user')) ?>">
                <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                <button class="btn btn-sm btn-outline-success">Activate</button>
              </form>
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reset<?= (int)$u['id'] ?>">Reset PW</button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php foreach ($list as $u): ?>
  <div class="modal fade" id="view<?= (int)$u['id'] ?>" tabindex="-1" aria-labelledby="viewLabel<?= (int)$u['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewLabel<?= (int)$u['id'] ?>">Registration details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php if ($u['status'] === 'pending'): ?>
            <div class="alert alert-warning py-2 small mb-3">Review this information before approving or rejecting the account.</div>
          <?php endif; ?>
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

            <dt class="col-sm-4">Status</dt>
            <dd class="col-sm-8"><?= status_badge($u['status']) ?></dd>

            <dt class="col-sm-4">Registered</dt>
            <dd class="col-sm-8"><?= e(format_dt($u['created_at'])) ?></dd>
          </dl>
        </div>
        <div class="modal-footer">
          <?php if ($u['status'] === 'pending'): ?>
            <form method="post" action="<?= e(url('controllers/AdminController.php?action=reject_user')) ?>" class="me-auto">
              <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
              <button type="submit" class="btn btn-outline-danger" data-confirm="Reject this registration?">Reject</button>
            </form>
            <form method="post" action="<?= e(url('controllers/AdminController.php?action=approve_user')) ?>">
              <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
              <button type="submit" class="btn btn-success">Approve account</button>
            </form>
          <?php else: ?>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="reset<?= (int)$u['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" method="post" action="<?= e(url('controllers/AdminController.php?action=reset_password')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
        <div class="modal-header"><h5 class="modal-title">Reset password · <?= e($u['full_name']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <label class="form-label">New password</label>
          <input type="password" class="form-control" name="new_password" required minlength="8">
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
      </form>
    </div>
  </div>
<?php endforeach; ?>

<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
