<?php
/**
 * OTP request + active OTP display + history.
 * Expects: $approvedVehicles, $activeOtp, $otpHistory, $expiryMinutes
 */
?>
<div class="row g-4">
  <div class="col-lg-5">
    <div class="card panel-card">
      <div class="card-body">
        <h2 class="h5 mb-3">Request entry OTP</h2>
        <p class="small text-muted">OTP expires in <?= (int)$expiryMinutes ?> minute(s) and can be used once.</p>
        <?php if (!$approvedVehicles): ?>
          <div class="alert alert-warning mb-0">You need an <strong>approved</strong> vehicle before requesting an OTP.</div>
        <?php else: ?>
        <form method="post" action="<?= e(url('controllers/OtpController.php?action=request')) ?>">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Vehicle</label>
            <select class="form-select" name="vehicle_id" required>
              <?php foreach ($approvedVehicles as $v): ?>
                <option value="<?= (int)$v['id'] ?>"><?= e($v['plate_number']) ?> — <?= e(trim(($v['make'] ?? '') . ' ' . ($v['model'] ?? ''))) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-accent" type="submit"><i class="bi bi-key me-1"></i>Generate OTP</button>
        </form>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($activeOtp): ?>
    <div class="card panel-card mt-4 border border-success">
      <div class="card-body text-center">
        <div class="text-muted small">Active OTP · <?= e($activeOtp['plate_number']) ?></div>
        <div class="otp-display my-2"><?= e($activeOtp['otp_code']) ?></div>
        <div class="small">Expires <?= e(format_dt($activeOtp['expires_at'])) ?></div>
        <?= status_badge($activeOtp['status']) ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <div class="col-lg-7">
    <div class="card panel-card">
      <div class="card-body">
        <h2 class="h5 mb-3">OTP history</h2>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>Code</th><th>Plate</th><th>Status</th><th>Created</th><th>Expires</th></tr></thead>
            <tbody>
            <?php if (!$otpHistory): ?>
              <tr><td colspan="5" class="text-muted">No OTP requests yet.</td></tr>
            <?php else: foreach ($otpHistory as $o): ?>
              <tr>
                <td class="font-monospace"><?= e($o['otp_code']) ?></td>
                <td><?= e($o['plate_number']) ?></td>
                <td><?= status_badge($o['status']) ?></td>
                <td class="small"><?= e(format_dt($o['created_at'])) ?></td>
                <td class="small"><?= e(format_dt($o['expires_at'])) ?></td>
              </tr>
            <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
