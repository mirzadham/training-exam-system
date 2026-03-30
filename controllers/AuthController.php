<?php
/**
 * Auth Controller
 * 
 * Handles admin login, registration, email verification,
 * forgot password, and password reset logic.
 */

require_once __DIR__ . '/../models/Admin.php';

class AuthController
{
    /**
     * Process login attempt (supports email-based login)
     * 
     * @return array ['success' => bool, 'error' => string|null]
     */
    public static function login(string $email, string $password): array
    {
        // Validate inputs
        if (!isRequired($email) || !isRequired($password)) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        // Try finding by email first, then by username (backward compatibility)
        $admin = Admin::findByEmail(trim($email));
        if (!$admin) {
            $admin = Admin::findByUsername(trim($email));
        }

        if (!$admin) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        // Verify password
        if (!password_verify($password, $admin['password'])) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        // Check if email is verified
        if ($admin['email'] && !$admin['email_verified_at']) {
            return ['success' => false, 'error' => 'Please verify your email address before logging in. Check your inbox for the verification link.'];
        }

        // Set session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'] ?: $admin['username'];
        $_SESSION['admin_username'] = $admin['username'];

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        return ['success' => true, 'error' => null];
    }

    /**
     * Process registration
     * 
     * @return array ['success' => bool, 'error' => string|null]
     */
    public static function register(string $email, string $password, string $confirmPassword, string $fullName): array
    {
        // Validate full name
        if (!isRequired($fullName)) {
            return ['success' => false, 'error' => 'Full name is required.'];
        }

        // Validate email
        if (!isRequired($email)) {
            return ['success' => false, 'error' => 'Email is required.'];
        }

        if (!isValidEmail($email)) {
            return ['success' => false, 'error' => 'Please enter a valid email address.'];
        }

        // Check email domain
        $email = strtolower(trim($email));
        $domain = substr(strrchr($email, '@'), 1);
        if ($domain !== ADMIN_EMAIL_DOMAIN) {
            return ['success' => false, 'error' => 'Only @' . ADMIN_EMAIL_DOMAIN . ' email addresses are allowed to register.'];
        }

        // Validate password
        if (!isRequired($password)) {
            return ['success' => false, 'error' => 'Password is required.'];
        }

        if (!isMinLength($password, 8)) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters long.'];
        }

        if ($password !== $confirmPassword) {
            return ['success' => false, 'error' => 'Passwords do not match.'];
        }

        // Check if email already exists
        if (Admin::findByEmail($email)) {
            return ['success' => false, 'error' => 'An account with this email already exists.'];
        }

        // Generate verification token
        $token = Admin::generateToken(VERIFICATION_TOKEN_LENGTH);

        // Create the account
        try {
            Admin::createWithEmail($email, $password, $fullName, $token);
        } catch (\PDOException $e) {
            return ['success' => false, 'error' => 'Registration failed. Please try again.'];
        }

        // Send verification email
        $emailSent = sendVerificationEmail($email, $fullName, $token);

        if (!$emailSent) {
            return ['success' => false, 'error' => 'Account created but verification email could not be sent. Please contact support.'];
        }

        return ['success' => true, 'error' => null];
    }

    /**
     * Verify email by token
     * 
     * @return array ['success' => bool, 'error' => string|null]
     */
    public static function verifyEmail(string $token): array
    {
        if (!isRequired($token)) {
            return ['success' => false, 'error' => 'Invalid verification link.'];
        }

        $verified = Admin::verifyEmail($token);

        if (!$verified) {
            return ['success' => false, 'error' => 'Invalid or expired verification link. Please register again or contact support.'];
        }

        return ['success' => true, 'error' => null];
    }

    /**
     * Process forgot password request
     * 
     * @return array ['success' => bool, 'error' => string|null]
     */
    public static function forgotPassword(string $email): array
    {
        if (!isRequired($email) || !isValidEmail($email)) {
            return ['success' => false, 'error' => 'Please enter a valid email address.'];
        }

        $email = strtolower(trim($email));
        $admin = Admin::findByEmail($email);

        // Always return success to prevent email enumeration
        $successMessage = 'If an account with that email exists, we\'ve sent a password reset link. Please check your inbox.';

        if (!$admin) {
            return ['success' => true, 'error' => null, 'message' => $successMessage];
        }

        // Check if email is verified
        if (!$admin['email_verified_at']) {
            return ['success' => true, 'error' => null, 'message' => $successMessage];
        }

        // Generate reset token
        $token = Admin::generateToken(VERIFICATION_TOKEN_LENGTH);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . RESET_TOKEN_EXPIRY_MINUTES . ' minutes'));

        Admin::setResetToken($admin['id'], $token, $expiresAt);

        // Send reset email
        sendPasswordResetEmail($admin['email'], $admin['full_name'] ?: $admin['username'], $token);

        return ['success' => true, 'error' => null, 'message' => $successMessage];
    }

    /**
     * Process password reset
     * 
     * @return array ['success' => bool, 'error' => string|null]
     */
    public static function resetPassword(string $token, string $password, string $confirmPassword): array
    {
        if (!isRequired($token)) {
            return ['success' => false, 'error' => 'Invalid reset link.'];
        }

        // Find admin by reset token (checks expiry too)
        $admin = Admin::findByResetToken($token);
        if (!$admin) {
            return ['success' => false, 'error' => 'This password reset link has expired or is invalid. Please request a new one.'];
        }

        // Validate new password
        if (!isRequired($password)) {
            return ['success' => false, 'error' => 'Password is required.'];
        }

        if (!isMinLength($password, 8)) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters long.'];
        }

        if ($password !== $confirmPassword) {
            return ['success' => false, 'error' => 'Passwords do not match.'];
        }

        // Update password
        Admin::resetPassword($admin['id'], $password);

        return ['success' => true, 'error' => null];
    }

    /**
     * Logout current admin
     */
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
