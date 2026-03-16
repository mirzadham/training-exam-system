<?php
/**
 * Admin Seed Script
 * 
 * Creates the first admin account.
 * Run this ONCE from the command line or browser to set up the initial admin.
 * 
 * Usage:
 *   - CLI:     php database/seed_admin.php
 *   - Browser: http://localhost/training-exam-system/database/seed_admin.php
 * 
 * Default credentials:
 *   Username: admin
 *   Password: admin123
 * 
 * IMPORTANT: Change the password after first login.
 * IMPORTANT: Delete or restrict access to this file in production.
 */

require_once __DIR__ . '/../init.php';
require_once MODELS_PATH . '/Admin.php';

// ---- Configuration ----
$seedUsername = 'admin';
$seedPassword = 'admin123';
$seedFullName = 'System Administrator';

// ---- Safety check ----
if (Admin::hasAny()) {
    $message = "⚠️  Admin account(s) already exist. Seed skipped to prevent duplicates.";
    if (php_sapi_name() === 'cli') {
        echo $message . PHP_EOL;
    } else {
        echo "<p style='font-family: sans-serif; color: #856404; background: #fff3cd; padding: 1em; border-radius: 4px;'>$message</p>";
    }
    exit;
}

// ---- Create admin ----
try {
    $adminId = Admin::create($seedUsername, $seedPassword, $seedFullName);
    $message = "✅ Admin account created successfully!\n"
             . "   Username: $seedUsername\n"
             . "   Password: $seedPassword\n"
             . "   ID: $adminId\n\n"
             . "⚠️  Change the password after first login.\n"
             . "⚠️  Remove or protect this seed file in production.";

    if (php_sapi_name() === 'cli') {
        echo $message . PHP_EOL;
    } else {
        echo "<pre style='font-family: sans-serif; background: #d4edda; padding: 1em; border-radius: 4px;'>"
           . htmlspecialchars($message) 
           . "</pre>";
    }
} catch (PDOException $e) {
    $message = "❌ Failed to create admin: " . $e->getMessage();
    if (php_sapi_name() === 'cli') {
        echo $message . PHP_EOL;
    } else {
        echo "<p style='font-family: sans-serif; color: #721c24; background: #f8d7da; padding: 1em; border-radius: 4px;'>"
           . htmlspecialchars($message)
           . "</p>";
    }
    exit(1);
}
