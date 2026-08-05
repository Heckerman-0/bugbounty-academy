<?php
/**
 * Configuration template.
 * Copy this file to config.php and fill in your database credentials.
 *
 *   cp includes/config.example.php includes/config.php
 */

// ---- Database settings ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'bugbounty_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// ---- Security ----
// Set to false on production. Kept true for the local training environment.
define('APP_DEBUG', true);

// ---- Base URL ----
// Leave as null to auto-detect (recommended for localhost virtual hosts / XAMPP).
// Example manual override: define('APP_BASE_URL', '/bugbounty/');
define('APP_BASE_URL', null);

// ---- Session security ----
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');

if (APP_DEBUG === false) {
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'Lax');
    error_reporting(0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

session_start();

// Auto-detect base URL unless manually defined
function detectBaseUrl()
{
    if (defined('APP_BASE_URL') && APP_BASE_URL !== null) {
        return APP_BASE_URL;
    }
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if (substr($scriptDir, -1) !== '/') {
        $scriptDir .= '/';
    }
    // Normalize the path: strip a leading full path if any
    $root = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    if ($docRoot && strpos($root, $docRoot) === 0) {
        return substr($root, strlen($docRoot)) . '/';
    }
    return '/';
}

define('BASE_URL', detectBaseUrl());

