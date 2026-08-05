<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 24;
$msg = "";
$login_msg = "";
$broke = false;
$flag_correct = false;

// Intentionally vulnerable login form (simulated)
if (isset($_POST['vlogin'])) {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    // Vulnerable: string concatenation (simulated) - admin bypass via ' OR '1'='1
    if ($user === "admin" && $pass === "LetMeIn999") {
        $login_msg = "<b>Success!</b> Logged in as admin.";
        $broke = true;
    } elseif (stripos($user, "' OR '1'='1") !== false) {
        $login_msg = "<b>Exploit Success!</b> You bypassed authentication with SQLi!";
        $broke = true;
    } elseif ($user === "admin") {
        $login_msg = "Wrong password for admin.";
    } else {
        $login_msg = "Invalid credentials.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['flag'])) {
    if (!verifyCsrfToken()) {
        $msg = "Invalid form submission. Please try again.";
    } else {
        $stmt = $pdo->prepare("SELECT flag_text FROM lab_flags WHERE content_id=?");
        $stmt->execute([$id]);
        $flagData = $stmt->fetch();
        if ($flagData && hash_equals($flagData['flag_text'], $_POST['flag'])) {
            markComplete($_SESSION['user_id'], $id);
            $flag_correct = true;
            $msg = "🎉 CORRECT FLAG! Lab Completed.";
        } else {
            $msg = "❌ Wrong flag. Try harder!";
        }
    }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<?php include '../../includes/nav.php'; ?>
<h1>🔐 Authentication Bypass Lab</h1>
<p>Try to log in as <strong>admin</strong> without knowing the password. Hint: The login query is built with string concatenation — try an SQL injection payload like <code>' OR '1'='1</code>.</p>

<form method="POST" style="max-width:360px; display:flex; flex-direction:column; gap:10px;">
    <input type="text" name="username" placeholder="Username" value="admin">
    <input type="password" name="password" placeholder="Password">
    <button type="submit" name="vlogin">Login</button>
</form>

<?php if ($login_msg): ?>
    <div style="border:1px solid #ccc; padding:10px; margin-top:10px;">
        <?= $login_msg ?>
        <?php if ($broke): ?>
            <p style="color:green; margin-top:8px;">🎯 You bypassed the login! Submit the flag below.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>
<div style="border:1px solid #ccc; padding:10px; margin-top:10px;"><?= $msg ?></div>

<form method="POST">
    <?= csrfField() ?>
    <h3>Bypassed the login? Submit the flag:</h3>
    <input type="text" name="flag" placeholder="Enter flag">
    <button type="submit">Submit Flag</button>
</form>
<?php if ($flag_correct): ?><h2 style="color:green;">✅ Lab Passed!</h2><?php endif; ?>
<a href="<?= BASE_URL ?>modules/labs/index.php" style="display:inline-block; margin-top:15px;">Back to Labs</a>
</div></body></html>
