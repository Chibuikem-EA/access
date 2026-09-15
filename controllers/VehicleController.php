<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role(['student', 'staff', 'driver', 'admin']);
require_active_user();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'register':
        verify_csrf();
        handle_register_vehicle($pdo);
        break;
    case 'update':
        verify_csrf();
        handle_update_vehicle($pdo);
        break;
    default:
        flash('error', 'Unknown vehicle action.');
        redirect(dashboard_for_role(current_user()['role']));
}

function vehicle_return_path(string $role): string
{
    return match ($role) {
        'admin' => 'views/admin/vehicles.php',
        'student' => 'views/student/vehicles.php',
        'staff' => 'views/staff/vehicles.php',
        'driver' => 'views/driver/vehicles.php',
        default => dashboard_for_role($role),
    };
}

function handle_register_vehicle(PDO $pdo): void
{
    $user = current_user();
    $plate = strtoupper(trim((string) request('plate_number', '')));
    $make = trim((string) request('make', ''));
    $model = trim((string) request('model', ''));
    $color = trim((string) request('color', ''));
    $type = (string) request('vehicle_type', 'car');
    $allowedTypes = ['car', 'motorcycle', 'van', 'bus', 'other'];
    if (!in_array($type, $allowedTypes, true)) {
        $type = 'car';
    }

    if ($plate === '' || !preg_match('/^[A-Z0-9\-\s]{3,30}$/', $plate)) {
        flash('error', 'Enter a valid plate number (3–30 alphanumeric characters).');
        redirect(vehicle_return_path($user['role']));
    }

    $vehicles = new Vehicle($pdo);
    if ($vehicles->findByPlate($plate)) {
        flash('error', 'That plate number is already registered.');
        redirect(vehicle_return_path($user['role']));
    }

    $requireApproval = setting($pdo, 'require_vehicle_approval', '1') === '1';
    $isShuttle = $user['role'] === 'driver' ? 1 : 0;
    $status = $requireApproval ? 'pending' : 'approved';
    if ($user['role'] === 'admin') {
        $status = 'approved';
    }

    $vehicleId = $vehicles->create([
        'user_id' => (int) $user['id'],
        'plate_number' => $plate,
        'make' => $make ?: null,
        'model' => $model ?: null,
        'color' => $color ?: null,
        'vehicle_type' => $type,
        'is_shuttle' => $isShuttle,
        'status' => $status,
    ]);

    if ($user['role'] === 'driver') {
        $route = trim((string) request('route_name', ''));
        $capacity = (int) request('capacity', 0);
        $shuttles = new Shuttle($pdo);
        $shuttles->create([
            'vehicle_id' => $vehicleId,
            'driver_id' => (int) $user['id'],
            'route_name' => $route ?: null,
            'capacity' => $capacity > 0 ? $capacity : null,
            'shuttle_status' => 'offline',
        ]);
    }

    audit_log($pdo, (int) $user['id'], 'vehicle_register', 'vehicle', $vehicleId, $plate);
    flash('success', $status === 'pending'
        ? 'Vehicle submitted for approval.'
        : 'Vehicle registered successfully.');
    redirect(vehicle_return_path($user['role']));
}

function handle_update_vehicle(PDO $pdo): void
{
    $user = current_user();
    $id = (int) request('vehicle_id', 0);
    $vehicles = new Vehicle($pdo);
    $v = $vehicles->findById($id);
    if (!$v || ((int) $v['user_id'] !== (int) $user['id'] && $user['role'] !== 'admin')) {
        flash('error', 'Vehicle not found or access denied.');
        redirect(vehicle_return_path($user['role']));
    }

    $plate = strtoupper(trim((string) request('plate_number', '')));
    if ($plate === '' || !preg_match('/^[A-Z0-9\-\s]{3,30}$/', $plate)) {
        flash('error', 'Invalid plate number.');
        redirect(vehicle_return_path($user['role']));
    }

    $existing = $vehicles->findByPlate($plate);
    if ($existing && (int) $existing['id'] !== $id) {
        flash('error', 'Plate number already in use.');
        redirect(vehicle_return_path($user['role']));
    }

    $type = (string) request('vehicle_type', $v['vehicle_type']);
    $vehicles->update($id, [
        'plate_number' => $plate,
        'make' => trim((string) request('make', '')) ?: null,
        'model' => trim((string) request('model', '')) ?: null,
        'color' => trim((string) request('color', '')) ?: null,
        'vehicle_type' => $type,
    ]);

    // Re-approval if plate changed and setting requires it
    if ($plate !== $v['plate_number'] && setting($pdo, 'require_vehicle_approval', '1') === '1' && $user['role'] !== 'admin') {
        $vehicles->setStatus($id, 'pending');
    }

    audit_log($pdo, (int) $user['id'], 'vehicle_update', 'vehicle', $id, $plate);
    flash('success', 'Vehicle updated.');
    redirect(vehicle_return_path($user['role']));
}
