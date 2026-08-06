<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 25;
$msg = "";
$broke = false;
$flag_correct = false;

// Simulate a CSRF attack: this endpoint "changes" the victim's email with no token check
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_email'])) {
    $email = $_POST['new_email'];
    // Intentionally NO CSRF token check - vulnerable
    $broke = true;
    $msg = "<b>Exploit Success!</b> You changed the victim's email to <code>" . htmlspecialchars($email) . "</code> without any CSRF token!";
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
    <title>MyAccount | Settings</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>includes/lab.css">
    <style>
        :root { --accent: #4338ca; --accent2: #312e81; }
        .settings-nav { display:flex; gap:6px; border-bottom:2px solid var(--border); margin-bottom:20px; flex-wrap:wrap; }
        .settings-nav a { padding:10px 16px; font-size:0.9rem; color:var(--muted); border-bottom:3px solid transparent; margin-bottom:-2px; }
        .settings-nav a.active { color:var(--accent); border-bottom-color:var(--accent); font-weight:600; }
        .field { display:flex; justify-content:space-between; align-items:center; padding:14px 0; border-bottom:1px solid var(--border); }
        .field .k { font-weight:600; }
        .field .v { color:var(--muted); }
    </style>
</head>
<body>
<div class="lab-banner">
    <div><span class="brand">🛡️ BBA</span> Lab — CSRF</div>
    <div><a class="link" href="<?= BASE_URL ?>modules/labs/index.php">⬅ Back to Labs</a></div>
</div>

<header class="site-header">
    <div class="inner">
        <div class="logo">👤 MyAccount <span>Account Settings</span></div>
        <nav>
            <a href="#">Profile</a>
            <a href="#">Security</a>
            <a href="#">Billing</a>
        </nav>
    </div>
</header>

<main class="site-main">
    <div class="site-card">
        <div class="settings-nav">
            <a href="#" class="active">Profile</a>
            <a href="#">Security</a>
            <a href="#">Notifications</a>
        </div>

        <h1>Account Settings</h1>
        <p class="sub">Manage your personal information and email address.</p>

        <div class="field"><span class="k">Name</span><span class="v">Mr. Admin</span></div>
        <div class="field"><span class="k">Username</span><span class="v">admin</span></div>
        <div class="field"><span class="k">Email</span><span class="v">admin@example.com</span></div>

        <h3 style="margin-top:24px;">Update Email</h3>
        <form method="POST" class="site-form">
            <input type="email" name="new_email" placeholder="attacker@evil.com" value="attacker@evil.com">
            <button type="submit" class="btn-site">Update Email</button>
        </form>
        <p style="font-size:0.8rem; color:var(--muted); margin-top:8px;">⚠️ This form has no CSRF token — a malicious site could submit it on your behalf.</p>

        <?php if ($msg): ?><div class="alert <?= $broke ? 'ok' : 'err' ?>" style="margin-top:16px;"><?= $msg ?></div><?php endif; ?>
    </div>

    <div class="flag-box">
        <h3>🏴 Exploited the CSRF? Submit the flag:</h3>
        <form method="POST" class="site-form">
            <?= csrfField() ?>
            <input type="text" name="flag" placeholder="Enter flag">
            <button type="submit" class="btn-site">Submit Flag</button>
        </form>
<?php if ($flag_correct): ?><div class="flag-done">✅ Lab Passed!</div><?php endif; ?>
    </div>
</main>

<?php
$stuckSteps = [
    'This is an account settings page where you can change the email address.',
    'The "Update Email" form does NOT include a CSRF token.',
    'That means any website you visit could silently submit this form on your behalf using your session.',
    'That is the bug: Cross-Site Request Forgery (CSRF).',
    'Simply submit the "Update Email" form with the attacker email (attacker@evil.com).',
    'Because there is no token check, the server accepts the change and marks it as a CSRF exploit.',
    'The page confirms the email was changed without any CSRF protection.',
    'Submit the flag below to complete the lab.',
];
$stuckTip = 'Just click "Update Email" — the missing CSRF token is the vulnerability. A real attack would do this from a malicious page.';
include '../../includes/stuck_widget.php';
?>
</body>
</html>
