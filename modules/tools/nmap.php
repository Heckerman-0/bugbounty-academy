<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = $_GET['id']; // This is 3
$output = "";
$flag_msg = "";
$flag_correct = false;

// Simulated Nmap output
if (isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];
    if (strpos($cmd, "nmap -sV 127.0.0.1") !== false) {
        $output = "Starting Nmap...<br>PORT   STATE SERVICE VERSION<br>22/tcp open  ssh     OpenSSH 8.0<br>80/tcp open  http    Apache 2.4<br>✅ Scan complete! The hidden flag port is 22.";
        markComplete($_SESSION['user_id'], $id); // Mark tool as read
    } else {
        $output = "Command not recognized. Try: nmap -sV 127.0.0.1";
    }
}

// Flag submission for the Nmap Lab (ID 5)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['flag'])) {
    $stmt = $pdo->prepare("SELECT flag_text FROM lab_flags WHERE content_id=5");
    $stmt->execute();
    $flagData = $stmt->fetch();
    if ($flagData && $_POST['flag'] === $flagData['flag_text']) {
        markComplete($_SESSION['user_id'], 5);
        $flag_correct = true;
        $flag_msg = "🎉 CORRECT! Nmap Lab Complete.";
    } else {
        $flag_msg = "❌ Wrong flag. Hint: It's a port number.";
    }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<?php include '../../includes/nav.php'; ?>
<h1>🛠️ Nmap Simulator + Lab</h1>
<p>Type a command to scan localhost.</p>
<form method="GET">
    <input type="text" name="cmd" placeholder="Enter command..." style="width:300px;">
    <button type="submit">Run</button>
</form>
<pre style="background:#0d0d1f; color:#00f2fe; padding:15px; border-radius:8px; border:1px solid rgba(0,242,254,0.1);"><?= $output ?></pre>

<hr style="border-color: rgba(255,255,255,0.05); margin:20px 0;">

<form method="POST">
    <h3>🎯 Submit the flag (Port Number):</h3>
    <input type="text" name="flag" placeholder="Enter flag" style="width:200px;">
    <button type="submit">Submit Flag</button>
</form>
<?php if ($flag_msg): ?><div style="margin-top:10px; font-weight:bold;"><?= $flag_msg ?></div><?php endif; ?>
<?php if ($flag_correct): ?><h2 style="color:green;">✅ Lab Passed!</h2><?php endif; ?>
<a href="<?= BASE_URL ?>dashboard.php" style="display:inline-block; margin-top:20px;">⬅ Back to Dashboard</a>
</div></body></html>