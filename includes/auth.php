<?php
require_once 'db.php';
require_once 'functions.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

function registerUser($username, $email, $password) {
    global $pdo;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hash]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
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