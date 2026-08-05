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
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<?php include '../../includes/nav.php'; ?>
<h1>💉 HTTP Header Manipulation Lab</h1>
<p>Your goal is to change your <code>User-Agent</code> to <strong>AdminHacker</strong>.</p>

<div style="background:rgba(0,242,254,0.05); padding:15px; border-radius:10px; border-left:4px solid #00f2fe; margin:15px 0;">
    <p><strong>📌 How to do it:</strong></p>
    <ul>
        <li><strong>Chrome/Edge:</strong> Open DevTools (F12) → Network tab → Right-click any request → Edit Headers → Add/Modify <code>User-Agent</code>.</li>
        <li><strong>Firefox:</strong> DevTools (F12) → Network → Edit and Resend.</li>
        <li><strong>Burp Suite:</strong> Capture the request and modify the <code>User-Agent</code> header.</li>
    </ul>
</div>

<p><strong>Your current User-Agent:</strong> <code style="background:#0d0d1f; padding:4px 10px; border-radius:5px;"><?= htmlspecialchars($user_agent) ?></code></p>

<?php if ($is_admin_agent): ?>
    <div style="background:rgba(0,242,254,0.15); padding:15px; border-radius:10px; border:1px solid #00f2fe;">
        🎯 <strong>Great!</strong> You are sending the correct header. Submit the flag below.
    </div>
<?php else: ?>
    <div style="background:rgba(254,0,0,0.05); padding:15px; border-radius:10px; border:1px solid rgba(254,0,0,0.2); color:#ff6b6b;">
        ⚠️ You are not sending the <code>AdminHacker</code> User-Agent. Change it and refresh this page!
    </div>
<?php endif; ?>

<hr style="border-color: rgba(255,255,255,0.05); margin:25px 0;">

<form method="POST">
    <?= csrfField() ?>
    <h3>📝 Enter the Flag:</h3>
    <input type="text" name="flag" placeholder="Enter flag..." style="width:300px;">
    <button type="submit">Submit</button>
</form>
<?php if ($flag_msg): ?><div style="margin-top:10px; font-weight:bold;"><?= $flag_msg ?></div><?php endif; ?>
<?php if ($flag_correct): ?><h2 style="color:green;">✅ Lab Passed!</h2><?php endif; ?>

<a href="<?= BASE_URL ?>dashboard.php" style="display:inline-block; margin-top:20px;">⬅ Back to Dashboard</a>
</div></body></html>