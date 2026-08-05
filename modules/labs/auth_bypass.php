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
<html>
<head>
    <title>Vault Bank | Online Banking</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>includes/lab.css">
    <style>
        :root { --accent: #0f766e; --accent2: #134e4a; }
        .login-wrap { max-width: 400px; margin: 0 auto; }
        .card-mark { text-align:center; margin-bottom: 18px; }
        .card-mark .icon { font-size: 2.6rem; }
        .card-mark .bank { font-size: 1.3rem; font-weight: 800; color: var(--accent); }
        .card-mark .secure { font-size: 0.8rem; color: var(--muted); }
    </style>
</head>
<body>
<div class="lab-banner">
    <div><span class="brand">🛡️ BBA</span> Lab — Authentication Bypass</div>
    <div><a class="link" href="<?= BASE_URL ?>modules/labs/index.php">⬅ Back to Labs</a></div>
</div>

<header class="site-header">
    <div class="inner">
        <div class="logo">🏦 Vault Bank <span>Online Banking</span></div>
        <nav>
            <a href="#">Personal</a>
            <a href="#">Business</a>
            <a href="#">Security</a>
        </nav>
    </div>
</header>

<main class="site-main">
    <div class="site-card login-wrap">
        <div class="card-mark">
            <div class="icon">🏦</div>
            <div class="bank">Vault Bank</div>
            <div class="secure">🔒 Secure Member Login</div>
        </div>

        <h1 style="text-align:center; font-size:1.4rem;">Sign In</h1>
        <p class="sub" style="text-align:center;">Log in to continue to your account.</p>

        <form method="POST" class="site-form">
            <label>Username</label>
            <input type="text" name="username" placeholder="Username" value="admin">
            <label>Password</label>
            <input type="password" name="password" placeholder="Password">
            <button type="submit" name="vlogin" class="btn-site block">🔐 Sign In</button>
        </form>

        <?php if ($login_msg): ?>
            <div class="alert <?= $broke ? 'ok' : 'err' ?>" style="margin-top:16px;">
                <?= $login_msg ?>
                <?php if ($broke): ?><div style="margin-top:6px;">🎯 You bypassed the login! Submit the flag below.</div><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="flag-box">
        <h3>🏴 Bypassed the login? Submit the flag:</h3>
        <form method="POST" class="site-form">
            <?= csrfField() ?>
            <input type="text" name="flag" placeholder="Enter flag">
            <button type="submit" class="btn-site">Submit Flag</button>
        </form>
<?php if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['flag'])): ?><div style="margin-top:10px;"><?= nl2br(htmlspecialchars($msg)) ?></div><?php endif; ?>
        <?php if ($flag_correct): ?><div class="flag-done">✅ Lab Passed!</div><?php endif; ?>
    </div>
</main>

<?php
$stuckSteps = [
    'This is a bank login page, but it stores and checks credentials insecurely.',
    'The login query is built by concatenating your username straight into the SQL (simulated here).',
    'That means you can inject SQL to bypass the password check.',
    'In the Username field, enter the classic SQL injection payload:   admin\' OR \'1\'=\'1',
    'Leave the password anything (or blank).',
    'The WHERE clause becomes always-true, so the query returns the admin account without a valid password.',
    'The page recognises the bypass and reveals that you logged in as admin.',
    'Submit the flag below to complete the lab.',
];
$stuckTip = 'Log in with username  admin\' OR \'1\'=\'1  and any password to bypass authentication.';
include '../../includes/stuck_widget.php';
?>
</body>
</html>
