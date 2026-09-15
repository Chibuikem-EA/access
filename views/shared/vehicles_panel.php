<?php
/**
 * Vehicle list + register form.
 * Expects: $vehicles, $showShuttleFields (bool)
 */
$showShuttleFields = $showShuttleFields ?? false;
?>
<div class="row g-4">
  <div class="col-lg-5">
    <div class="card panel-card">
      <div class="card-body">
        <h2 class="h5 mb-3">Register vehicle</h2>
        <form method="post" action="<?= e(url('controllers/VehicleController.php?action=register')) ?>" class="row g-3">
          <?= csrf_field() ?>
          <div class="col-12">
            <label class="form-label">Plate number</label>
            <input class="form-control text-uppercase" name="plate_number" required pattern="[A-Za-z0-9\-\s]{3,30}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Make</label>
            <input class="form-control" name="make">
          </div>
          <div class="col-md-6">
            <label class="form-label">Model</label>
            <input class="form-control" name="model">
          </div>
          <div class="col-md-6">
            <label class="form-label">Color</label>
            <input class="form-control" name="color">
          </div>
          <div class="col-md-6">
            <label class="form-label">Type</label>
            <select class="form-select" name="vehicle_type">
              <?php foreach (['car','motorcycle','van','bus','other'] as $t): ?>
                <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if ($showShuttleFields): ?>
          <div class="col-md-8">
            <label class="form-label">Route name</label>
            <input class="form-control" name="route_name" placeholder="e.g. North Campus Loop">
          </div>
          <div class="col-md-4">
            <label class="form-label">Capacity</label>
            <input type="number" class="form-control" name="capacity" min="1">
          </div>
          <?php endif; ?>
          <div class="col-12">
            <button class="btn btn-primary" type="submit">Submit vehicle</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card panel-card">
      <div class="card-body">
        <h2 class="h5 mb-3">Your vehicles</h2>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Plate</th>
                <th>Details</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php if (!$vehicles): ?>
              <tr><td colspan="4" class="text-muted">No vehicles registered yet.</td></tr>
            <?php else: foreach ($vehicles as $v): ?>
              <tr>
                <td class="fw-semibold"><?= e($v['plate_number']) ?></td>
                <td class="small">
                  <?= e(trim(($v['make'] ?? '') . ' ' . ($v['model'] ?? '')) ?: '—') ?>
                  · <?= e(ucfirst($v['vehicle_type'])) ?>
                  <?= $v['color'] ? ' · ' . e($v['color']) : '' ?>
                </td>
                <td><?= status_badge($v['status']) ?></td>
                <td>
                  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editVeh<?= (int)$v['id'] ?>">Edit</button>
                </td>
              </tr>

              <div class="modal fade" id="editVeh<?= (int)$v['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                  <form class="modal-content" method="post" action="<?= e(url('controllers/VehicleController.php?action=update')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="vehicle_id" value="<?= (int)$v['id'] ?>">
                    <div class="modal-header"><h5 class="modal-title">Edit vehicle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body row g-3">
                      <div class="col-12"><label class="form-label">Plate</label><input class="form-control text-uppercase" name="plate_number" required value="<?= e($v['plate_number']) ?>"></div>
                      <div class="col-6"><label class="form-label">Make</label><input class="form-control" name="make" value="<?= e($v['make'] ?? '') ?>"></div>
                      <div class="col-6"><label class="form-label">Model</label><input class="form-control" name="model" value="<?= e($v['model'] ?? '') ?>"></div>
                      <div class="col-6"><label class="form-label">Color</label><input class="form-control" name="color" value="<?= e($v['color'] ?? '') ?>"></div>
                      <div class="col-6">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="vehicle_type">
                          <?php foreach (['car','motorcycle','van','bus','other'] as $t): ?>
                            <option value="<?= $t ?>" <?= $v['vehicle_type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary" type="submit">Save</button></div>
                  </form>
                </div>
              </div>
            <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
