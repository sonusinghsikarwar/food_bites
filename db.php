<?php
// includes/db.php
// Database connection setup using PDO

if (session_status() === PHP_SESSION_NONE) {
    // Session security hardening
    ini_set('session.cookie_httponly', 1);   // Prevent JS access to session cookie
    ini_set('session.use_strict_mode', 1);   // Reject uninitialized session IDs
    ini_set('session.cookie_samesite', 'Strict'); // CSRF protection
    session_start();
}

$host = '127.0.0.1';
$db   = 'food_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Database connection failed: " . $e->getMessage());
}

// Global System Settings Fetcher
function getSetting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['key_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}
?>
