<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role(['student', 'staff', 'driver']);
require_active_user();

$action = $_GET['action'] ?? $_POST['action'] ?? 'request';

if ($action === 'request') {
    verify_csrf();
    handle_request_otp($pdo, $config);
} else {
    flash('error', 'Unknown OTP action.');
    redirect(dashboard_for_role(current_user()['role']));
}

function otp_return_path(string $role): string
{
    return match ($role) {
        'student' => 'views/student/otp.php',
        'staff' => 'views/staff/otp.php',
        'driver' => 'views/driver/otp.php',
        default => dashboard_for_role($role),
    };
}

function handle_request_otp(PDO $pdo, array $config): void
{
    $user = current_user();
    $vehicleId = (int) request('vehicle_id', 0);
    $vehicles = new Vehicle($pdo);
    $vehicle = $vehicles->findById($vehicleId);

    if (!$vehicle || (int) $vehicle['user_id'] !== (int) $user['id']) {
        flash('error', 'Select one of your registered vehicles.');
        redirect(otp_return_path($user['role']));
    }
    if ($vehicle['status'] !== 'approved') {
        flash('error', 'Vehicle must be approved before requesting an OTP.');
        redirect(otp_return_path($user['role']));
    }

    $expiry = (int) (setting($pdo, 'otp_expiry_minutes', (string) $config['otp_expiry']) ?: 5);
    $length = (int) (setting($pdo, 'otp_length', '6') ?: 6);

    $otpModel = new Otp($pdo);
    $otp = $otpModel->create((int) $user['id'], $vehicleId, $expiry, $length, 'entry');

    audit_log($pdo, (int) $user['id'], 'otp_request', 'otp', (int) $otp['id'], $vehicle['plate_number']);
    flash('success', 'OTP generated. Present this code at the gate before it expires.');
    redirect(otp_return_path($user['role']));
}
