<?php
/**
 * Authentication Helper Functions
 * 
 * Session-based auth checks for the admin area.
 */

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require admin to be logged in — redirect to login page if not
 */
function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        setFlash('error', 'Please log in to access the admin area.');
        redirect(url('admin/login.php'));
    }
}

/**
 * Get current admin ID from session
 */
function getAdminId(): ?int
{
    return $_SESSION['admin_id'] ?? null;
}

/**
 * Get current admin name from session
 */
function getAdminName(): string
{
    return $_SESSION['admin_name'] ?? 'Admin';
}

/**
 * Generate a CSRF token and store it in session
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token against the session
 */
function verify_csrf(string $token): bool
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Helper to generate hidden CSRF input field
 */
function csrf_field(): string
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" id="csrf_token_field" value="' . $token . '">';
}
