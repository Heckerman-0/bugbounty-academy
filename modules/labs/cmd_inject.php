<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 16;
$msg = "";
$output = "";
$flag_correct = false;

if (isset($_GET['ip'])) {
    $input = $_GET['ip'];
    // Intentionally vulnerable: passes input directly to a shell command
    $output = shell_exec('ping -c 1 ' . $input . ' 2>&1');
    if (stripos($input, 'whoami') !== false || stripos($input, 'cat /etc/passwd') !== false) {
        $msg = "<b>Exploit Success!</b> Remote code execution achieved!";
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
<h1>Command Injection Lab</h1>
<p>Ping a host below. Hint: The app passes your input directly to the shell — try appending a command with <code>;</code> or <code>&&</code>.</p>
<form method="GET">
    <input type="text" name="ip" placeholder="127.0.0.1">
    <button type="submit">Ping</button>
</form>
<?php if ($output): ?>
<div style="border:1px solid #ccc; padding:10px;"><pre><?= htmlspecialchars($output) ?></pre></div>
<?php endif; ?>
<div style="border:1px solid #ccc; padding:10px; margin-top:10px;"><?= $msg ?></div>

<form method="POST">
    <?= csrfField() ?>
    <h3>Found the flag? Submit it:</h3>
    <input type="text" name="flag" placeholder="Enter flag">
    <button type="submit">Submit Flag</button>
</form>
<?php if ($flag_correct): ?><h2 style="color:green;">✅ Lab Passed!</h2><?php endif; ?>
<a href="<?= BASE_URL ?>modules/labs/index.php">Back to Labs</a>
</div></body></html>
