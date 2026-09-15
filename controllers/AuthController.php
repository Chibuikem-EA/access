<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        handle_login($pdo);
        break;
    case 'register':
        handle_register($pdo, $config);
        break;
    case 'update_profile':
        require_login();
        verify_csrf();
        handle_update_profile($pdo);
        break;
    case 'change_password':
        require_login();
        verify_csrf();
        handle_change_password($pdo);
        break;
    default:
        flash('error', 'Unknown auth action.');
        redirect('login.php');
}

function handle_login(PDO $pdo): void
{
    if (!is_post()) {
        redirect('login.php');
    }
    verify_csrf();
    $email = trim((string) request('email', ''));
    $password = (string) request('password', '');
    store_old(['email' => $email]);

    if ($email === '' || $password === '') {
        flash('error', 'Email and password are required.');
        redirect('login.php');
    }

    $users = new User($pdo);
    $user = $users->findByEmail($email);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        flash('error', 'Invalid email or password.');
        redirect('login.php');
    }
    if ($user['status'] === 'pending') {
        flash('error', 'Your account is pending administrator approval.');
        redirect('login.php');
    }
    if ($user['status'] === 'rejected') {
        flash('error', 'Your registration was rejected. Contact the administrator.');
        redirect('login.php');
    }
    if ($user['status'] !== 'active') {
        flash('error', 'Your account is inactive.');
        redirect('login.php');
    }

    $users->touchLogin((int) $user['id']);
    login_user($user);
    clear_old();
    audit_log($pdo, (int) $user['id'], 'login', 'user', (int) $user['id']);
    flash('success', 'Welcome back, ' . $user['full_name'] . '!');
    redirect(dashboard_for_role($user['role']));
}

function handle_register(PDO $pdo, array $config): void
{
    if (!is_post()) {
        redirect('register.php');
    }
    verify_csrf();

    if (setting($pdo, 'allow_registration', '1') !== '1') {
        flash('error', 'Registration is currently disabled.');
        redirect('login.php');
    }

    $fullName = trim((string) request('full_name', ''));
    $email = trim((string) request('email', ''));
    $phone = trim((string) request('phone', ''));
    $role = (string) request('role', 'student');
    $password = (string) request('password', '');
    $confirm = (string) request('password_confirm', '');
    $studentId = trim((string) request('student_id', ''));
    $staffId = trim((string) request('staff_id', ''));

    store_old(compact('fullName', 'email', 'phone', 'role', 'studentId', 'staffId') + [
        'full_name' => $fullName,
        'student_id' => $studentId,
        'staff_id' => $staffId,
    ]);

    $allowedRoles = ['student', 'staff', 'driver'];
    if (!in_array($role, $allowedRoles, true)) {
        flash('error', 'Invalid role selected.');
        redirect('register.php');
    }
    if ($fullName === '' || $email === '' || $password === '') {
        flash('error', 'Name, email, and password are required.');
        redirect('register.php');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Please enter a valid email address.');
        redirect('register.php');
    }
    if (strlen($password) < 8) {
        flash('error', 'Password must be at least 8 characters.');
        redirect('register.php');
    }
    if ($password !== $confirm) {
        flash('error', 'Passwords do not match.');
        redirect('register.php');
    }

    $users = new User($pdo);
    if ($users->findByEmail($email)) {
        flash('error', 'An account with that email already exists.');
        redirect('register.php');
    }

    $id = $users->create([
        'full_name' => $fullName,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'phone' => $phone !== '' ? $phone : null,
        'role' => $role,
        'student_id' => $role === 'student' ? ($studentId ?: null) : null,
        'staff_id' => in_array($role, ['staff', 'driver'], true) ? ($staffId ?: null) : null,
        'department' => null,
        'status' => 'pending',
    ]);

    audit_log($pdo, $id, 'register', 'user', $id, "Role: {$role}");
    clear_old();
    flash('success', 'Registration submitted. An administrator must approve your account before you can log in.');
    redirect('login.php');
}

function handle_update_profile(PDO $pdo): void
{
    $user = current_user();
    $fullName = trim((string) request('full_name', ''));
    $phone = trim((string) request('phone', ''));
    $department = trim((string) request('department', ''));
    $studentId = trim((string) request('student_id', ''));
    $staffId = trim((string) request('staff_id', ''));

    if ($fullName === '') {
        flash('error', 'Full name is required.');
        redirect($_SERVER['HTTP_REFERER'] ?? dashboard_for_role($user['role']));
    }

    $users = new User($pdo);
    $users->updateProfile((int) $user['id'], [
        'full_name' => $fullName,
        'phone' => $phone !== '' ? $phone : null,
        'department' => $department !== '' ? $department : null,
        'student_id' => $studentId !== '' ? $studentId : null,
        'staff_id' => $staffId !== '' ? $staffId : null,
    ]);
    refresh_session_user($pdo);
    audit_log($pdo, (int) $user['id'], 'profile_update', 'user', (int) $user['id']);
    flash('success', 'Profile updated.');
    redirect($_SERVER['HTTP_REFERER'] ?? dashboard_for_role($user['role']));
}

function handle_change_password(PDO $pdo): void
{
    $user = current_user();
    $current = (string) request('current_password', '');
    $new = (string) request('new_password', '');
    $confirm = (string) request('new_password_confirm', '');

    $users = new User($pdo);
    $row = $users->findById((int) $user['id']);
    if (!$row || !password_verify($current, $row['password_hash'])) {
        flash('error', 'Current password is incorrect.');
        redirect($_SERVER['HTTP_REFERER'] ?? dashboard_for_role($user['role']));
    }
    if (strlen($new) < 8) {
        flash('error', 'New password must be at least 8 characters.');
        redirect($_SERVER['HTTP_REFERER'] ?? dashboard_for_role($user['role']));
    }
    if ($new !== $confirm) {
        flash('error', 'New passwords do not match.');
        redirect($_SERVER['HTTP_REFERER'] ?? dashboard_for_role($user['role']));
    }

    $users->updatePassword((int) $user['id'], password_hash($new, PASSWORD_DEFAULT));
    audit_log($pdo, (int) $user['id'], 'password_change', 'user', (int) $user['id']);
    flash('success', 'Password changed successfully.');
    redirect($_SERVER['HTTP_REFERER'] ?? dashboard_for_role($user['role']));
}
