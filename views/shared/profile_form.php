<?php
/**
 * Shared profile form partial.
 * Expects $userRow array and posts to AuthController.
 */
?>
<div class="row g-4">
  <div class="col-lg-7">
    <div class="card panel-card">
      <div class="card-body">
        <h2 class="h5 mb-3">Profile details</h2>
        <form method="post" action="<?= e(url('controllers/AuthController.php?action=update_profile')) ?>" class="row g-3">
          <?= csrf_field() ?>
          <div class="col-12">
            <label class="form-label">Full name</label>
            <input class="form-control" name="full_name" required value="<?= e($userRow['full_name']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input class="form-control" value="<?= e($userRow['email']) ?>" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input class="form-control" name="phone" value="<?= e($userRow['phone'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Department</label>
            <input class="form-control" name="department" value="<?= e($userRow['department'] ?? '') ?>">
          </div>
          <?php if (($userRow['role'] ?? '') === 'student'): ?>
          <div class="col-md-6">
            <label class="form-label">Student ID</label>
            <input class="form-control" name="student_id" value="<?= e($userRow['student_id'] ?? '') ?>">
          </div>
          <?php else: ?>
          <div class="col-md-6">
            <label class="form-label">Staff / Driver ID</label>
            <input class="form-control" name="staff_id" value="<?= e($userRow['staff_id'] ?? '') ?>">
          </div>
          <?php endif; ?>
          <div class="col-12">
            <button class="btn btn-primary" type="submit">Save profile</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card panel-card">
      <div class="card-body">
        <h2 class="h5 mb-3">Change password</h2>
        <form method="post" action="<?= e(url('controllers/AuthController.php?action=change_password')) ?>" class="vstack gap-3">
          <?= csrf_field() ?>
          <div>
            <label class="form-label">Current password</label>
            <input type="password" class="form-control" name="current_password" required>
          </div>
          <div>
            <label class="form-label">New password</label>
            <input type="password" class="form-control" name="new_password" required minlength="8">
          </div>
          <div>
            <label class="form-label">Confirm new password</label>
            <input type="password" class="form-control" name="new_password_confirm" required minlength="8">
          </div>
          <button class="btn btn-outline-primary" type="submit">Update password</button>
        </form>
      </div>
    </div>
  </div>
</div>
