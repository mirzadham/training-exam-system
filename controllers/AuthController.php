<?php
/**
 * Auth Controller
 * 
 * Handles admin login and logout logic.
 */

require_once __DIR__ . '/../models/Admin.php';

class AuthController
{
    /**
     * Process login attempt
     * 
     * @return array ['success' => bool, 'error' => string|null]
     */
    public static function login(string $username, string $password): array
    {
        // Validate inputs
        if (!isRequired($username) || !isRequired($password)) {
            return ['success' => false, 'error' => 'Username and password are required.'];
        }

        // Find admin by username
        $admin = Admin::findByUsername(trim($username));

        if (!$admin) {
            // Generic message to avoid revealing whether username exists
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        // Verify password
        if (!password_verify($password, $admin['password'])) {
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        // Set session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'] ?: $admin['username'];
        $_SESSION['admin_username'] = $admin['username'];

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        return ['success' => true, 'error' => null];
    }

    public static function logout(): void
    {
        // Clear only admin session data
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_name']);
        unset($_SESSION['admin_username']);

        // Destroy the entire session only if a participant is not currently taking an exam
        if (empty($_SESSION['participant_id'])) {
            // Destroy the session cookie
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            // Destroy the session
            session_destroy();
        }
    }
}
