<?php
// config.php is gitignored (it holds DB credentials).
// If it is missing, show a helpful setup message instead of a fatal error.
if (!file_exists(__DIR__ . '/config.php')) {
    header('Content-Type: text/plain; charset=utf-8');
    die("⚠️  Missing configuration.\n\n"
      . "Copy includes/config.example.php to includes/config.php and set your database credentials:\n"
      . "  cp includes/config.example.php includes/config.php\n\n"
      . "Then import bugbounty.sql into phpMyAdmin and reload this page.\n");
}
require_once 'config.php';
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>