<?php
require_once __DIR__ . '/config.php';

/**
 * Returns a shared PDO connection (singleton).
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('<div style="font-family:sans-serif;max-width:600px;margin:60px auto;padding:24px;border:1px solid #f3c;border-radius:12px">
                <h2>Database connection failed</h2>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Make sure MySQL is running in XAMPP and you have imported <code>database.sql</code>.</p>
                </div>');
        }
    }
    return $pdo;
}
