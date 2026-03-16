<?php
/**
 * Admin Logout
 * 
 * Destroys the session and redirects to login page.
 */

require_once __DIR__ . '/../init.php';
require_once CONTROLLERS_PATH . '/AuthController.php';

AuthController::logout();

setFlash('success', 'You have been logged out successfully.');
redirect(url('admin/login.php'));
