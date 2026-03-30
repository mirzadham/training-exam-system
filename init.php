<?php
/**
 * Application Bootstrap
 * 
 * Initializes session, loads config, and includes helpers.
 * Every entry point (index.php, admin/*.php) should require this file first.
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load .env file (lightweight parser, no external dependencies)
function loadEnv(string $path): void
{
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // Strip surrounding quotes if present
        if (preg_match('/^(["\'])(.*)\\1$/', $value, $m)) {
            $value = $m[2];
        }
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}
loadEnv(__DIR__ . '/.env');

// Load configuration
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

// Load helpers
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/validation.php';
require_once __DIR__ . '/helpers/pagination.php';
require_once __DIR__ . '/helpers/mail.php';
