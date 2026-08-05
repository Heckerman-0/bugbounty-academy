<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 6;
$output = "";
$flag_msg = "";
$flag_correct = false;
$xss_triggered = false;

if (isset($_GET['comment'])) {
    // Intentionally UNSAFE: the comment is echoed raw so the XSS reflects.
    $output = "You said: " . $_GET['comment'];
    if (stripos($_GET['comment'], "<script>alert") !== false) {
        $xss_triggered = true;
        markComplete($_SESSION['user_id'], $id);
    }
}

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
            $flag_msg = "❌ Wrong flag. Try harder!";
        }
    }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<?php include '../../includes/nav.php'; ?>
<h1>💉 XSS Playground</h1>
<p>Inject a JavaScript alert into the comment box. Try: <code><script>alert(1)</script></code></p>
<form method="GET">
    <input type="text" name="comment" placeholder="Type something...">
    <button type="submit">Post</button>
</form>
<div style="border:1px solid rgba(0,242,254,0.15); padding:12px; border-radius:8px; min-height:40px; margin:10px 0;"><?= $output ?></div>
<?php if ($xss_triggered): ?><div style="background:rgba(0,242,254,0.1); border-left:4px solid #00f2fe; padding:12px; border-radius:8px;">🚨 XSS executed! Now submit the flag below.</div><?php endif; ?>

<form method="POST">
    <?= csrfField() ?>
    <h3>🏴 Found the Flag?</h3>
    <input type="text" name="flag" placeholder="Enter flag">
    <button type="submit">Submit Flag</button>
</form>
<?php if ($flag_msg): ?><div style="margin-top:10px; font-weight:bold;"><?= htmlspecialchars($flag_msg) ?></div><?php endif; ?>
<?php if ($flag_correct): ?><h2 style="color:green;">✅ Lab Passed!</h2><?php endif; ?>
<a href="<?= BASE_URL ?>dashboard.php" style="display:inline-block; margin-top:15px;">⬅ Back to Dashboard</a>
</div></body></html>
