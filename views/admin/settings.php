<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('admin');
require_active_user();

$settings = [
  'otp_expiry_minutes' => setting($pdo, 'otp_expiry_minutes', '5'),
  'otp_length' => setting($pdo, 'otp_length', '6'),
  'campus_name' => setting($pdo, 'campus_name', 'Campus Security Gate'),
  'allow_registration' => setting($pdo, 'allow_registration', '1'),
  'require_vehicle_approval' => setting($pdo, 'require_vehicle_approval', '1'),
];
$page = ['title' => 'Settings', 'active' => 'settings'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h1 class="h3 mb-3">System settings</h1>
<div class="card panel-card" style="max-width:640px">
  <div class="card-body">
    <form method="post" action="<?= e(url('controllers/AdminController.php?action=save_settings')) ?>" class="vstack gap-3">
      <?= csrf_field() ?>
      <div>
        <label class="form-label">Campus / gate name</label>
        <input class="form-control" name="campus_name" value="<?= e($settings['campus_name']) ?>" required>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">OTP expiry (minutes)</label>
          <input type="number" class="form-control" name="otp_expiry_minutes" min="1" max="60" value="<?= e($settings['otp_expiry_minutes']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">OTP length (digits)</label>
          <input type="number" class="form-control" name="otp_length" min="4" max="8" value="<?= e($settings['otp_length']) ?>" required>
        </div>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="allow_registration" id="allow_registration" <?= $settings['allow_registration'] === '1' ? 'checked' : '' ?>>
        <label class="form-check-label" for="allow_registration">Allow public registration</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="require_vehicle_approval" id="require_vehicle_approval" <?= $settings['require_vehicle_approval'] === '1' ? 'checked' : '' ?>>
        <label class="form-check-label" for="require_vehicle_approval">Require admin approval for new vehicles</label>
      </div>
      <button class="btn btn-primary" type="submit">Save settings</button>
    </form>
  </div>
</div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
