<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    $uid = (int) current_user()['id'];
    audit_log($pdo, $uid, 'logout', 'user', $uid);
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool)$p['secure'], (bool)$p['httponly']);
}
session_destroy();

session_name($config['session']);
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);
flash('success', 'You have been logged out.');
redirect('login.php');
