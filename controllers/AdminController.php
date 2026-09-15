<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('admin');
require_active_user();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'approve_user':
    case 'reject_user':
    case 'activate_user':
    case 'deactivate_user':
        verify_csrf();
        handle_user_status($pdo, $action);
        break;
    case 'reset_password':
        verify_csrf();
        handle_reset_password($pdo);
        break;
    case 'approve_vehicle':
    case 'reject_vehicle':
    case 'deactivate_vehicle':
        verify_csrf();
        handle_vehicle_status($pdo, $action);
        break;
    case 'save_settings':
        verify_csrf();
        handle_settings($pdo);
        break;
    case 'delete_user':
        verify_csrf();
        handle_delete_user($pdo);
        break;
    default:
        flash('error', 'Unknown admin action.');
        redirect('views/admin/dashboard.php');
}

function handle_user_status(PDO $pdo, string $action): void
{
    $id = (int) request('user_id', 0);
    $users = new User($pdo);
    $target = $users->findById($id);
    if (!$target) {
        flash('error', 'User not found.');
        redirect('views/admin/users.php');
    }
    if ((int) $target['id'] === (int) current_user()['id'] && in_array($action, ['deactivate_user', 'reject_user'], true)) {
        flash('error', 'You cannot deactivate your own account.');
        redirect('views/admin/users.php');
    }

    $map = [
        'approve_user' => 'active',
        'reject_user' => 'rejected',
        'activate_user' => 'active',
        'deactivate_user' => 'inactive',
    ];
    $status = $map[$action];
    $users->setStatus($id, $status);
    audit_log($pdo, (int) current_user()['id'], $action, 'user', $id, "Status -> {$status}");
    flash('success', 'User status updated to ' . $status . '.');
    redirect('views/admin/users.php');
}

function handle_reset_password(PDO $pdo): void
{
    $id = (int) request('user_id', 0);
    $newPass = (string) request('new_password', '');
    if (strlen($newPass) < 8) {
        flash('error', 'Password must be at least 8 characters.');
        redirect('views/admin/users.php');
    }
    $users = new User($pdo);
    if (!$users->findById($id)) {
        flash('error', 'User not found.');
        redirect('views/admin/users.php');
    }
    $users->updatePassword($id, password_hash($newPass, PASSWORD_DEFAULT));
    audit_log($pdo, (int) current_user()['id'], 'admin_reset_password', 'user', $id);
    flash('success', 'Password reset successfully.');
    redirect('views/admin/users.php');
}

function handle_vehicle_status(PDO $pdo, string $action): void
{
    $id = (int) request('vehicle_id', 0);
    $vehicles = new Vehicle($pdo);
    $v = $vehicles->findById($id);
    if (!$v) {
        flash('error', 'Vehicle not found.');
        redirect('views/admin/vehicles.php');
    }
    $map = [
        'approve_vehicle' => 'approved',
        'reject_vehicle' => 'rejected',
        'deactivate_vehicle' => 'inactive',
    ];
    $status = $map[$action];
    $vehicles->setStatus($id, $status);
    audit_log($pdo, (int) current_user()['id'], $action, 'vehicle', $id, "Plate {$v['plate_number']} -> {$status}");
    flash('success', 'Vehicle status updated.');
    redirect('views/admin/vehicles.php');
}

function handle_settings(PDO $pdo): void
{
    $otpExpiry = max(1, min(60, (int) request('otp_expiry_minutes', 5)));
    $otpLength = max(4, min(8, (int) request('otp_length', 6)));
    $campus = trim((string) request('campus_name', 'Campus Security Gate'));
    $allowReg = request('allow_registration') ? '1' : '0';
    $requireApproval = request('require_vehicle_approval') ? '1' : '0';

    set_setting($pdo, 'otp_expiry_minutes', (string) $otpExpiry);
    set_setting($pdo, 'otp_length', (string) $otpLength);
    set_setting($pdo, 'campus_name', $campus);
    set_setting($pdo, 'allow_registration', $allowReg);
    set_setting($pdo, 'require_vehicle_approval', $requireApproval);

    audit_log($pdo, (int) current_user()['id'], 'settings_update', 'settings', null);
    flash('success', 'Settings saved.');
    redirect('views/admin/settings.php');
}

function handle_delete_user(PDO $pdo): void
{
    $id = (int) request('user_id', 0);
    if ($id === (int) current_user()['id']) {
        flash('error', 'You cannot delete your own account.');
        redirect('views/admin/users.php');
    }
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role != \'admin\'');
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        // Allow deleting non-self admins only with care — block last admin deletion simply
        $admins = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        $target = (new User($pdo))->findById($id);
        if ($target && $target['role'] === 'admin' && $admins <= 1) {
            flash('error', 'Cannot delete the last administrator.');
            redirect('views/admin/users.php');
        }
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }
    audit_log($pdo, (int) current_user()['id'], 'delete_user', 'user', $id);
    flash('success', 'User deleted.');
    redirect('views/admin/users.php');
}
