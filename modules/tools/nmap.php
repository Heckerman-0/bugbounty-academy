<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = $_GET['id'];
$output = "";
if (isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];
    if (strpos($cmd, "nmap -sV 127.0.0.1") !== false) {
        $output = "Starting Nmap...<br>PORT   STATE SERVICE VERSION<br>22/tcp open  ssh     OpenSSH 8.0<br>80/tcp open  http    Apache 2.4<br>✅ Scan complete!";
        markComplete($_SESSION['user_id'], $id);
    } else {
        $output = "Command not recognized. Try: nmap -sV 127.0.0.1";
    }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<h1>Nmap Simulator</h1>
<p>Type a command to scan localhost.</p>
<form method="GET">
    <input type="text" name="cmd" placeholder="Enter command..." style="width:300px;">
    <button type="submit">Run</button>
</form>
<pre style="background:#000;color:#0f0;padding:10px;"><?= $output ?></pre>
<a href="<?= BASE_URL ?>dashboard.php">Back</a>
</div></body></html>