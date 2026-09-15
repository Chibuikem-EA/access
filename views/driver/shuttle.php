<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('driver');
require_active_user();
$shuttles = (new Shuttle($pdo))->forDriver((int)current_user()['id']);
$page = ['title' => 'Shuttle Status', 'active' => 'shuttle'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-3">Update shuttle status</h1>
<?php if (!$shuttles): ?>
  <div class="alert alert-warning">Register a shuttle vehicle first. Status controls appear after registration.</div>
<?php else: foreach ($shuttles as $s): ?>
  <div class="card panel-card mb-4">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h2 class="h5 mb-1"><?= e($s['plate_number']) ?></h2>
          <div class="text-muted small"><?= e($s['route_name'] ?? 'No route set') ?> · Cap <?= e((string)($s['capacity'] ?? '—')) ?></div>
        </div>
        <?= status_badge($s['shuttle_status']) ?>
      </div>
      <form method="post" action="<?= e(url('controllers/ShuttleController.php?action=update_status')) ?>" class="row g-3 mb-3">
        <?= csrf_field() ?>
        <input type="hidden" name="shuttle_id" value="<?= (int)$s['id'] ?>">
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select class="form-select" name="shuttle_status">
            <?php foreach (['available','in_transit','offline','maintenance'] as $st): ?>
              <option value="<?= $st ?>" <?= $s['shuttle_status'] === $st ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_',' ',$st))) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Notes</label>
          <input class="form-control" name="notes" value="<?= e($s['notes'] ?? '') ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button class="btn btn-primary w-100" type="submit">Update</button>
        </div>
      </form>
      <form method="post" action="<?= e(url('controllers/ShuttleController.php?action=update_details')) ?>" class="row g-3">
        <?= csrf_field() ?>
        <input type="hidden" name="shuttle_id" value="<?= (int)$s['id'] ?>">
        <div class="col-md-6">
          <label class="form-label">Route name</label>
          <input class="form-control" name="route_name" value="<?= e($s['route_name'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Capacity</label>
          <input type="number" class="form-control" name="capacity" min="1" value="<?= e((string)($s['capacity'] ?? '')) ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-outline-secondary w-100" type="submit">Save details</button>
        </div>
      </form>
    </div>
  </div>
<?php endforeach; endif; ?>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
