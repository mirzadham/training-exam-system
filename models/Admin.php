<?php
/**
 * Admin Model
 * 
 * Database interaction for the admins table.
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
     * Create a new admin account
     */
    public static function create(string $username, string $password, string $fullName = ''): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO admins (username, password, full_name) VALUES (:username, :password, :full_name)"
        );
        $stmt->execute([
            'username'  => $username,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'full_name' => $fullName,
        ]);
        return (int) $pdo->lastInsertId();
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
}
