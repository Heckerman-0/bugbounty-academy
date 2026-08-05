<?php
require_once 'db.php';
require_once 'functions.php';

function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) return false;
    // Validate the session user still exists in the database.
    // This prevents stale-session foreign key errors (e.g. after a DB re-import).
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        if ($stmt->fetch()) return true;
    } catch (PDOException $e) {
        return false;
    }
    // User no longer exists — clear the stale session data.
    foreach (['user_id', 'username', 'role', 'email'] as $key) {
        unset($_SESSION[$key]);
    }
    return false;
}

function isAdmin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

function registerUser($username, $email, $password) {
    global $pdo;
    // Basic validation
    if (strlen($username) < 3 || strlen($username) > 50) {
        return "Username must be 3-50 characters.";
    }
    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
        return "Username can only contain letters, numbers, underscore, and hyphen.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email address.";
    }
    if (strlen($password) < 6) {
        return "Password must be at least 6 characters.";
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hash]);
        return true;
    } catch(PDOException $e) {
        return "Username or email already exists.";
    }
}

// ---- CSRF protection ----
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

function verifyCsrfToken() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

// Verify CSRF token sent via GET query parameter (for links/state-changing GET actions)
function verifyCsrfTokenGet() {
    if (!isset($_GET['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_GET['csrf_token']);
}

// Append the CSRF token to a URL for state-changing GET links
function csrfUrl($url) {
    $sep = (strpos($url, '?') === false) ? '?' : '&';
    return $url . $sep . 'csrf_token=' . urlencode(generateCsrfToken());
}

function loginUser($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        updateStreak($user['id']);
        return true;
    }
    return false;
}

function changeEmail($user_id, $new_email) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$new_email, $user_id]);
        return true;
    } catch(PDOException $e) {
        return "Email already in use or invalid.";
    }
}

function changePassword($user_id, $old_password, $new_password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($old_password, $user['password_hash'])) {
        return "Current password is incorrect.";
    }
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $upd->execute([$new_hash, $user_id]);
    return true;
}

function logoutUser() {
    session_destroy();
    header("Location: " . BASE_URL . "login.php");
    exit;
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}
?>