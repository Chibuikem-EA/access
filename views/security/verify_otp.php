<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/init.php';
require_role('security');
require_active_user();
$page = ['title' => 'Verify OTP', 'active' => 'verify'];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card panel-card">
      <div class="card-body p-4">
        <h1 class="h4 mb-1">Verify OTP & approve entry</h1>
        <p class="text-muted small">Enter the one-time code shown on the driver's dashboard.</p>
        <form method="post" action="<?= e(url('controllers/SecurityController.php?action=verify_entry')) ?>" class="vstack gap-3">
          <?= csrf_field() ?>
          <div>
            <label class="form-label" for="otp_code">OTP code</label>
            <input class="form-control form-control-lg text-center font-monospace" id="otp_code" name="otp_code" required pattern="\d{4,8}" maxlength="8" autocomplete="one-time-code" autofocus>
          </div>
          <button class="btn btn-accent btn-lg" type="submit"><i class="bi bi-check2-circle me-1"></i>Verify & log entry</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
