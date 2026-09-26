<?php
declare(strict_types=1);

$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        if ($k !== '') putenv($k . '=' . trim($v, "\"'"));
    }
}

function envv(string $key, mixed $default = null): mixed {
    $v = getenv($key);
    return $v === false ? $default : $v;
}

const APP_NAME = 'UniFlow';
const APP_TIMEZONE = 'Asia/Aden';
const APP_DEBUG = false;

if (!defined('APP_URL')) define('APP_URL', (string) envv('APP_URL', 'http://localhost:8000'));
if (!defined('DB_HOST')) define('DB_HOST', (string) envv('DB_HOST', '127.0.0.1'));
if (!defined('DB_PORT')) define('DB_PORT', (int) envv('DB_PORT', 3308));
if (!defined('DB_NAME')) define('DB_NAME', (string) envv('DB_NAME', 'uniflow_graduate'));
if (!defined('DB_USER')) define('DB_USER', (string) envv('DB_USER', 'root'));
if (!defined('DB_PASS')) define('DB_PASS', (string) envv('DB_PASS', ''));

 date_default_timezone_set(APP_TIMEZONE);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
