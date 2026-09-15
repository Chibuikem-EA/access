<?php
/**
 * Application configuration loader.
 * Reads .env from project root (optional) then applies defaults.
 */

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Load .env once
if (!defined('VES_ENV_LOADED')) {
    define('VES_ENV_LOADED', true);
    $envFile = BASE_PATH . DIRECTORY_SEPARATOR . '.env';
    if (is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }
            if ($key !== '' && getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
            }
        }
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $val = $_ENV[$key] ?? getenv($key);
        if ($val === false || $val === null || $val === '') {
            return $default;
        }
        return $val;
    }
}

return [
    'app_name'   => (string) env('APP_NAME', 'ACCEZZ'),
    'app_url'    => rtrim((string) env('APP_URL', ''), '/'),
    'debug'      => filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN),
    'session'    => (string) env('SESSION_NAME', 'VESSESSID'),
    'otp_expiry' => (int) env('OTP_EXPIRY_MINUTES', 5),
    'db' => [
        'host' => (string) env('DB_HOST', '127.0.0.1'),
        'port' => (int) env('DB_PORT', 3306),
        'name' => (string) env('DB_NAME', 'vehicle_entry_system'),
        'user' => (string) env('DB_USER', 'root'),
        'pass' => (string) env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'roles' => ['admin', 'security', 'student', 'staff', 'driver'],
];
