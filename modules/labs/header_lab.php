<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 4;
$flag_msg = "";
$flag_correct = false;

// Check if the User-Agent is manipulated
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_admin_agent = (stripos($user_agent, 'AdminHacker') !== false);

// Flag submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['flag'])) {
    if (!verifyCsrfToken()) {
        $flag_msg = "Invalid form submission. Please try again.";
    } else {
        $stmt = $pdo->prepare("SELECT flag_text FROM lab_flags WHERE content_id=?");
        $stmt->execute([$id]);
        $flagData = $stmt->fetch();
        if ($flagData && hash_equals($flagData['flag_text'], $_POST['flag'])) {
            markComplete($_SESSION['user_id'], $id);
            $flag_correct = true;
            $flag_msg = "🎉 CORRECT FLAG! Lab Completed.";
        } else {
            $flag_msg = "❌ Wrong flag. Keep trying!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>SecureDocs | Portal</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>includes/lab.css">
    <style>
        :root { --accent: #0284c7; --accent2: #075985; }
        .fp-wrap { display:flex; gap:10px; align-items:center; background:#f8fafc; border:1px solid var(--border); border-radius:10px; padding:12px 16px; margin:14px 0; }
        .fp-wrap code { word-break: break-all; }
        .stat-row { display:flex; gap:16px; flex-wrap:wrap; margin-top:16px; }
        .stat-box { background:#f8fafc; border:1px solid var(--border); border-radius:12px; padding:16px 20px; flex:1; min-width:120px; text-align:center; }
        .stat-box .v { font-size:1.4rem; font-weight:800; color:var(--accent); }
        .stat-box .k { font-size:0.75rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.5px; }
    </style>
</head>
<body>
<div class="lab-banner">
    <div><span class="brand">🛡️ BBA</span> Lab — HTTP Header Manipulation</div>
    <div><a class="link" href="<?= BASE_URL ?>modules/labs/index.php">⬅ Back to Labs</a></div>
</div>

<header class="site-header">
    <div class="inner">
        <div class="logo">🔒 SecureDocs <span>Protected Portal</span></div>
        <nav>
            <a href="#">Documents</a>
            <a href="#">Admin Console</a>
            <a href="#">Settings</a>
        </nav>
    </div>
</header>

<main class="site-main">
    <div class="site-card">
        <h1>Access Restricted Content</h1>
        <p class="sub">This portal grants admin access based on your device's User-Agent header. Spoof it to <code>AdminHacker</code> to unlock the admin panel.</p>

        <div class="stat-row">
            <div class="stat-box"><div class="v"><?= $is_admin_agent ? '✅' : '🔒' ?></div><div class="k">Access</div></div>
            <div class="stat-box"><div class="v"><?= $is_admin_agent ? 'ADMIN' : 'GUEST' ?></div><div class="k">Role</div></div>
        </div>

        <div class="fp-wrap">
            <span>🖥️ Your User-Agent:</span>
            <code><?= htmlspecialchars($user_agent) ?></code>
        </div>

        <?php if ($is_admin_agent): ?>
            <div class="alert ok">🎯 <strong>Great!</strong> You are sending the correct header. Submit the flag below.</div>
        <?php else: ?>
            <div class="alert err">⚠️ You are not sending the <code>AdminHacker</code> User-Agent. Use DevTools (F12) → Network → Edit &amp; Resend to change it, then refresh.</div>
        <?php endif; ?>
    </div>

    <div class="flag-box">
        <h3>📝 Enter the Flag:</h3>
        <form method="POST" class="site-form">
            <?= csrfField() ?>
            <input type="text" name="flag" placeholder="Enter flag...">
            <button type="submit" class="btn-site">Submit</button>
        </form>
        <?php if ($flag_msg): ?><div style="margin-top:10px; font-weight:bold;"><?= $flag_msg ?></div><?php endif; ?>
        <?php if ($flag_correct): ?><div class="flag-done">✅ Lab Passed!</div><?php endif; ?>
    </div>
</main>
</body>
</html>
