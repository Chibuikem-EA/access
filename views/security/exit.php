<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('security');
require_active_user();
$page = ['title' => 'Record Exit', 'active' => 'exit'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card panel-card">
      <div class="card-body p-4">
        <h1 class="h4 mb-3">Record vehicle exit</h1>
        <form method="post" action="<?= e(url('controllers/SecurityController.php?action=record_exit')) ?>" class="vstack gap-3">
          <?= csrf_field() ?>
          <div>
            <label class="form-label">Plate number</label>
            <input class="form-control text-uppercase" name="plate_number" required pattern="[A-Za-z0-9\-\s]{3,30}">
          </div>
          <div>
            <label class="form-label">Notes (optional)</label>
            <input class="form-control" name="notes">
          </div>
          <button class="btn btn-primary" type="submit">Log exit</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
