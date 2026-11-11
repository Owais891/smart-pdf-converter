<?php
/**
 * Smart PDF - Database Configuration (PDO)
 * ------------------------------------------------------------------
 * Update credentials for your local XAMPP environment.
 */

// --- Edit these to match your MySQL / XAMPP setup ---
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'smart_pdf_db');
define('DB_USER', 'root');
define('DB_PASS', '');                // Default XAMPP: empty password
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a single shared PDO connection (singleton).
 *
 * @return PDO
 * @throws RuntimeException if connection fails
 */
function db()
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never echo raw exception in production; log it.
            error_log('DB connection failed: ' . $e->getMessage());
            throw new RuntimeException(
                'Could not connect to the database. Please check config/database.php ' .
                'and make sure MySQL is running (XAMPP: start the MySQL service).'
            );
        }
    }

    return $pdo;
}
