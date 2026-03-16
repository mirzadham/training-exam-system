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
