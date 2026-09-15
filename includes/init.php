<?php
declare(strict_types=1);

/**
 * Application bootstrap. Include from every public/view entry point:
 *   require_once __DIR__ . '/../includes/init.php';  (adjust relative path)
 */

if (defined('VES_INIT')) {
    return;
}
define('VES_INIT', true);

$config = require dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Autoload models
spl_autoload_register(static function (string $class): void {
    $path = dirname(__DIR__) . '/models/' . $class . '.php';
    if (is_readable($path)) {
        require_once $path;
    }
    $cpath = dirname(__DIR__) . '/controllers/' . $class . '.php';
    if (is_readable($cpath)) {
        require_once $cpath;
    }
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($config['session']);
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

try {
    $pdo = Database::connection($config['db']);
} catch (Throwable $e) {
    if (!empty($config['debug'])) {
        http_response_code(500);
        echo '<h1>Database connection failed</h1><pre>' . e($e->getMessage()) . '</pre>';
        echo '<p>Import <code>database/schema.sql</code> and copy <code>.env.example</code> to <code>.env</code>.</p>';
        exit;
    }
    http_response_code(500);
    echo 'Service temporarily unavailable.';
    exit;
}

// Expire stale OTPs opportunistically
try {
    $pdo->exec("UPDATE otps SET status = 'expired' WHERE status = 'active' AND expires_at < NOW()");
} catch (Throwable $e) {
    // Schema may not be imported yet
}
