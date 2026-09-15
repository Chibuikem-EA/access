<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('security');
require_active_user();

$model = new Visitor($pdo);
$onCampus = $model->list(['status' => 'on_campus'], 100);
$today = $model->list(['today' => true], 50);
$page = ['title' => 'Visitors', 'active' => 'visitors'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="row g-4">
  <div class="col-lg-5">
    <div class="card panel-card">
      <div class="card-body">
        <h1 class="h5 mb-3">Register visitor</h1>
        <form method="post" action="<?= e(url('controllers/SecurityController.php?action=register_visitor')) ?>" class="row g-3">
          <?= csrf_field() ?>
          <div class="col-12">
            <label class="form-label">Full name</label>
            <input class="form-control" name="full_name" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Plate number</label>
            <input class="form-control text-uppercase" name="plate_number" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input class="form-control" name="phone">
          </div>
          <div class="col-12">
            <label class="form-label">Host / destination</label>
            <input class="form-control" name="host_name">
          </div>
          <div class="col-12">
            <label class="form-label">Purpose</label>
            <input class="form-control" name="purpose">
          </div>
          <div class="col-12"><button class="btn btn-primary" type="submit">Register & log entry</button></div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card panel-card mb-4">
      <div class="card-body">
        <h2 class="h5">Currently on campus</h2>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>Name</th><th>Plate</th><th>Entry</th><th></th></tr></thead>
            <tbody>
            <?php if (!$onCampus): ?>
              <tr><td colspan="4" class="text-muted">None.</td></tr>
            <?php else: foreach ($onCampus as $v): ?>
              <tr>
                <td><?= e($v['full_name']) ?></td>
                <td><?= e($v['plate_number']) ?></td>
                <td class="small"><?= e(format_dt($v['entry_at'])) ?></td>
                <td>
                  <form method="post" action="<?= e(url('controllers/SecurityController.php?action=exit_visitor')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="visitor_id" value="<?= (int)$v['id'] ?>">
                    <button class="btn btn-sm btn-outline-primary">Mark exit</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="card panel-card">
      <div class="card-body">
        <h2 class="h5">Registered today</h2>
        <ul class="list-group list-group-flush">
          <?php foreach ($today as $v): ?>
            <li class="list-group-item d-flex justify-content-between">
              <span><?= e($v['full_name']) ?> · <?= e($v['plate_number']) ?></span>
              <?= status_badge($v['status']) ?>
            </li>
          <?php endforeach; ?>
          <?php if (!$today): ?><li class="list-group-item text-muted">No visitors today.</li><?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
