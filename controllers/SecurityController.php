<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('security');
require_active_user();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'verify_entry':
        verify_csrf();
        handle_verify_entry($pdo);
        break;
    case 'record_exit':
        verify_csrf();
        handle_record_exit($pdo);
        break;
    case 'register_visitor':
        verify_csrf();
        handle_register_visitor($pdo);
        break;
    case 'exit_visitor':
        verify_csrf();
        handle_exit_visitor($pdo);
        break;
    default:
        flash('error', 'Unknown security action.');
        redirect('views/security/dashboard.php');
}

function handle_verify_entry(PDO $pdo): void
{
    $code = trim((string) request('otp_code', ''));
    if ($code === '' || !preg_match('/^\d{4,8}$/', $code)) {
        flash('error', 'Enter a valid OTP code.');
        redirect('views/security/verify_otp.php');
    }

    $otpModel = new Otp($pdo);
    $otp = $otpModel->findActiveByCode($code);

    if (!$otp) {
        flash('error', 'OTP is invalid, used, or expired.');
        redirect('views/security/verify_otp.php');
    }
    if (($otp['user_status'] ?? '') !== 'active') {
        flash('error', 'Vehicle owner account is not active.');
        redirect('views/security/verify_otp.php');
    }
    if (($otp['vehicle_status'] ?? '') !== 'approved') {
        flash('error', 'Vehicle is not approved for entry.');
        redirect('views/security/verify_otp.php');
    }

    $otpModel->markUsed((int) $otp['id']);

    $logs = new EntryExitLog($pdo);
    $logId = $logs->create([
        'user_id' => (int) $otp['user_id'],
        'vehicle_id' => (int) $otp['vehicle_id'],
        'otp_id' => (int) $otp['id'],
        'plate_number' => $otp['plate_number'],
        'log_type' => 'entry',
        'verified_by' => (int) current_user()['id'],
        'notes' => 'OTP verified entry',
    ]);

    audit_log(
        $pdo,
        (int) current_user()['id'],
        'otp_verify_entry',
        'entry_exit_log',
        $logId,
        "Plate {$otp['plate_number']} OTP {$code}"
    );

    flash('success', 'Entry approved for ' . $otp['plate_number'] . ' (' . $otp['full_name'] . ').');
    redirect('views/security/verify_otp.php');
}

function handle_record_exit(PDO $pdo): void
{
    $plate = strtoupper(trim((string) request('plate_number', '')));
    $notes = trim((string) request('notes', ''));

    if ($plate === '') {
        flash('error', 'Plate number is required.');
        redirect('views/security/exit.php');
    }

    $vehicles = new Vehicle($pdo);
    $vehicle = $vehicles->findByPlate($plate);

    $logs = new EntryExitLog($pdo);
    $logId = $logs->create([
        'user_id' => $vehicle['user_id'] ?? null,
        'vehicle_id' => $vehicle['id'] ?? null,
        'plate_number' => $plate,
        'log_type' => 'exit',
        'verified_by' => (int) current_user()['id'],
        'notes' => $notes !== '' ? $notes : 'Vehicle exit recorded',
    ]);

    audit_log($pdo, (int) current_user()['id'], 'record_exit', 'entry_exit_log', $logId, $plate);
    flash('success', 'Exit recorded for ' . $plate . '.');
    redirect('views/security/exit.php');
}

function handle_register_visitor(PDO $pdo): void
{
    $name = trim((string) request('full_name', ''));
    $plate = strtoupper(trim((string) request('plate_number', '')));
    $phone = trim((string) request('phone', ''));
    $purpose = trim((string) request('purpose', ''));
    $host = trim((string) request('host_name', ''));

    if ($name === '' || $plate === '') {
        flash('error', 'Visitor name and plate number are required.');
        redirect('views/security/visitors.php');
    }

    $visitors = new Visitor($pdo);
    $visitorId = $visitors->create([
        'full_name' => $name,
        'phone' => $phone ?: null,
        'plate_number' => $plate,
        'purpose' => $purpose ?: null,
        'host_name' => $host ?: null,
        'registered_by' => (int) current_user()['id'],
    ]);

    $logs = new EntryExitLog($pdo);
    $logs->create([
        'visitor_id' => $visitorId,
        'plate_number' => $plate,
        'log_type' => 'entry',
        'verified_by' => (int) current_user()['id'],
        'notes' => 'Visitor entry: ' . $name,
    ]);

    audit_log($pdo, (int) current_user()['id'], 'visitor_register', 'visitor', $visitorId, $plate);
    flash('success', 'Visitor registered and entry logged.');
    redirect('views/security/visitors.php');
}

function handle_exit_visitor(PDO $pdo): void
{
    $id = (int) request('visitor_id', 0);
    $visitors = new Visitor($pdo);
    $v = $visitors->findById($id);
    if (!$v || $v['status'] !== 'on_campus') {
        flash('error', 'Visitor not found or already exited.');
        redirect('views/security/visitors.php');
    }

    $visitors->markExited($id);
    $logs = new EntryExitLog($pdo);
    $logs->create([
        'visitor_id' => $id,
        'plate_number' => $v['plate_number'],
        'log_type' => 'exit',
        'verified_by' => (int) current_user()['id'],
        'notes' => 'Visitor exit: ' . $v['full_name'],
    ]);

    audit_log($pdo, (int) current_user()['id'], 'visitor_exit', 'visitor', $id, $v['plate_number']);
    flash('success', 'Visitor exit recorded.');
    redirect('views/security/visitors.php');
}
