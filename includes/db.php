<?php
// includes/db.php - HORAA STORE Database Connection Handler

define('DB_HOST', 'localhost');
define('DB_NAME', 'horaa_store');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function get_db_connection() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            // Friendly error message if DB is not created yet
            die("Database Connection Error: " . $e->getMessage() . "<br><br><strong>Tip:</strong> Please import <code>database/schema.sql</code> and <code>database/seed.sql</code> into your MySQL server.");
        }
    }
    return $pdo;
}

// Global DB instance shorthand
$db = get_db_connection();
