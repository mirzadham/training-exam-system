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

// Load configuration
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

// Load helpers
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/validation.php';
