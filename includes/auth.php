<?php
declare(strict_types=1);

/**
 * Session auth + RBAC helpers.
 */

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_role(string|array $roles): void
{
    require_login();
    $roles = (array) $roles;
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        flash('error', 'You are not authorized to access that page.');
        redirect(dashboard_for_role($user['role'] ?? 'student'));
    }
}

function require_active_user(): void
{
    require_login();
    $user = current_user();
    if (($user['status'] ?? '') !== 'active') {
        logout_user();
        flash('error', 'Your account is not active.');
        redirect('login.php');
    }
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'         => (int) $user['id'],
        'full_name'  => $user['full_name'],
        'email'      => $user['email'],
        'role'       => $user['role'],
        'status'     => $user['status'],
        'phone'      => $user['phone'] ?? null,
        'department' => $user['department'] ?? null,
        'student_id' => $user['student_id'] ?? null,
        'staff_id'   => $user['staff_id'] ?? null,
    ];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function dashboard_for_role(string $role): string
{
    return match ($role) {
        'admin' => 'views/admin/dashboard.php',
        'security' => 'views/security/dashboard.php',
        'student' => 'views/student/dashboard.php',
        'staff' => 'views/staff/dashboard.php',
        'driver' => 'views/driver/dashboard.php',
        default => 'login.php',
    };
}

function refresh_session_user(PDO $pdo): void
{
    $user = current_user();
    if (!$user) {
        return;
    }
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $user['id']]);
    $row = $stmt->fetch();
    if (!$row || $row['status'] !== 'active') {
        logout_user();
        flash('error', 'Your account session ended.');
        redirect('login.php');
    }
    login_user($row);
}
