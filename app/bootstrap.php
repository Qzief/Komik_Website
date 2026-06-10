<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$envPath = dirname(__DIR__) . '/.env';
if (is_file($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if ($key !== '' && !array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

require __DIR__ . '/helpers.php';
require __DIR__ . '/database.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/repositories.php';

session_name((string) config('app.session_name'));
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
