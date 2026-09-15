<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
require_role('driver');
require_active_user();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'update_status') {
    verify_csrf();
    $id = (int) request('shuttle_id', 0);
    $status = (string) request('shuttle_status', 'offline');
    $notes = trim((string) request('notes', ''));
    $allowed = ['available', 'in_transit', 'offline', 'maintenance'];
    if (!in_array($status, $allowed, true)) {
        flash('error', 'Invalid shuttle status.');
        redirect('views/driver/shuttle.php');
    }

    $shuttles = new Shuttle($pdo);
    $shuttle = $shuttles->findById($id);
    if (!$shuttle || (int) $shuttle['driver_id'] !== (int) current_user()['id']) {
        flash('error', 'Shuttle not found.');
        redirect('views/driver/shuttle.php');
    }

    $shuttles->updateStatus($id, $status, $notes !== '' ? $notes : null);
    audit_log($pdo, (int) current_user()['id'], 'shuttle_status', 'shuttle', $id, $status);
    flash('success', 'Shuttle status updated to ' . str_replace('_', ' ', $status) . '.');
    redirect('views/driver/shuttle.php');
}

if ($action === 'update_details') {
    verify_csrf();
    $id = (int) request('shuttle_id', 0);
    $shuttles = new Shuttle($pdo);
    $shuttle = $shuttles->findById($id);
    if (!$shuttle || (int) $shuttle['driver_id'] !== (int) current_user()['id']) {
        flash('error', 'Shuttle not found.');
        redirect('views/driver/shuttle.php');
    }
    $shuttles->update($id, [
        'route_name' => trim((string) request('route_name', '')) ?: null,
        'capacity' => ((int) request('capacity', 0)) ?: null,
        'notes' => trim((string) request('notes', '')) ?: null,
    ]);
    flash('success', 'Shuttle details saved.');
    redirect('views/driver/shuttle.php');
}

flash('error', 'Unknown shuttle action.');
redirect('views/driver/shuttle.php');
