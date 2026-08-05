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
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<?php include '../../includes/nav.php'; ?>
<h1>🕸️ CSRF Exploitation Lab</h1>
<p>This profile page lets you change your email. Notice the <strong>update email</strong> form has <strong>no CSRF token</strong> — that's the vulnerability. A malicious site could submit this form on your behalf.</p>

<div style="background:rgba(0,242,254,0.05); padding:15px; border-radius:10px; border-left:4px solid #00f2fe; margin:15px 0;">
    <p><strong>📌 Your task:</strong></p>
    <ul>
        <li>Submit the "update email" form below (simulating the victim).</li>
        <li>Observe that the request succeeds with <em>no CSRF token</em>.</li>
        <li>Then submit the flag to prove you exploited the CSRF vulnerability.</li>
    </ul>
</div>

<form method="POST" style="max-width:360px; display:flex; flex-direction:column; gap:10px;">
    <input type="email" name="new_email" placeholder="attacker@evil.com" value="attacker@evil.com">
    <button type="submit">Update Email (Vulnerable - no CSRF token)</button>
</form>

<div style="border:1px solid #ccc; padding:10px; margin-top:10px;"><?= $msg ?></div>

<form method="POST">
    <?= csrfField() ?>
    <h3>Exploited the CSRF? Submit the flag:</h3>
    <input type="text" name="flag" placeholder="Enter flag">
    <button type="submit">Submit Flag</button>
</form>
<?php if ($flag_correct): ?><h2 style="color:green;">✅ Lab Passed!</h2><?php endif; ?>
<a href="<?= BASE_URL ?>modules/labs/index.php" style="display:inline-block; margin-top:15px;">Back to Labs</a>
</div></body></html>
