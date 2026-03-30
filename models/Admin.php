<?php
/**
 * Admin Model
 * 
 * Database interaction for the admins table.
 * Supports email-based registration, verification, and password reset.
 */

require_once __DIR__ . '/../config/database.php';

class Admin
{
    /**
     * Find admin by username
     */
    public static function findByUsername(string $username): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find admin by email
     */
    public static function findByEmail(string $email): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => strtolower(trim($email))]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find admin by ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Create a new admin account (legacy — used by seed script)
     */
    public static function create(string $username, string $password, string $fullName = ''): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO admins (username, password, full_name, email_verified_at) 
             VALUES (:username, :password, :full_name, NOW())"
        );
        $stmt->execute([
            'username'  => $username,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'full_name' => $fullName,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Create a new admin account with email (registration flow)
     */
    public static function createWithEmail(string $email, string $password, string $fullName, string $verificationToken): int
    {
        $pdo = getDBConnection();
        $email = strtolower(trim($email));
        // Use email prefix as username (before the @)
        $username = strstr($email, '@', true);

        // Ensure username is unique by appending a number if needed
        $baseUsername = $username;
        $counter = 1;
        while (self::findByUsername($username)) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO admins (username, email, password, full_name, verification_token) 
             VALUES (:username, :email, :password, :full_name, :verification_token)"
        );
        $stmt->execute([
            'username'           => $username,
            'email'              => $email,
            'password'           => password_hash($password, PASSWORD_DEFAULT),
            'full_name'          => trim($fullName),
            'verification_token' => $verificationToken,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Verify email by token — sets email_verified_at and clears the token
     */
    public static function verifyEmail(string $token): bool
    {
        $pdo = getDBConnection();

        // Find admin with this token
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE verification_token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        $admin = $stmt->fetch();

        if (!$admin) {
            return false;
        }

        // Mark as verified
        $stmt = $pdo->prepare(
            "UPDATE admins SET email_verified_at = NOW(), verification_token = NULL WHERE id = :id"
        );
        $stmt->execute(['id' => $admin['id']]);
        return true;
    }

    /**
     * Set a password reset token
     */
    public static function setResetToken(int $id, string $token, string $expiresAt): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "UPDATE admins SET reset_token = :token, reset_token_expires_at = :expires WHERE id = :id"
        );
        $stmt->execute([
            'token'   => $token,
            'expires' => $expiresAt,
            'id'      => $id,
        ]);
    }

    /**
     * Find admin by reset token (only if not expired)
     */
    public static function findByResetToken(string $token): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT * FROM admins 
             WHERE reset_token = :token 
             AND reset_token_expires_at > NOW() 
             LIMIT 1"
        );
        $stmt->execute(['token' => $token]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Reset the password and clear the reset token
     */
    public static function resetPassword(int $id, string $newPassword): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "UPDATE admins SET password = :password, reset_token = NULL, reset_token_expires_at = NULL WHERE id = :id"
        );
        $stmt->execute([
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id'       => $id,
        ]);
    }

    /**
     * Check if admin email is verified
     */
    public static function isEmailVerified(int $id): bool
    {
        $admin = self::findById($id);
        return $admin && $admin['email_verified_at'] !== null;
    }

    /**
     * Check if any admin exists in the database
     */
    public static function hasAny(): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Generate a secure random token
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}
