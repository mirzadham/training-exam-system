<?php
/**
 * Database Configuration
 * 
 * PDO connection setup for MySQL.
 * For local development with Laragon, default credentials are used.
 * For production (cPanel), update these values or use a database.local.php override.
 */

// Allow local override for credentials (gitignored)
if (file_exists(__DIR__ . '/database.local.php')) {
    require __DIR__ . '/database.local.php';
    return;
}

// Default local development settings (Laragon)
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'training_exam_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection
 * 
 * @return PDO
 * @throws PDOException
 */
function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}
