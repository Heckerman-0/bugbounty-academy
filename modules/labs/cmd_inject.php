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
<html>
<head>
    <title>PingGrid | Network Monitor</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>includes/lab.css">
    <style>
        :root { --accent: #0891b2; --accent2: #164e63; }
        .status-dot { display:inline-block; width:10px; height:10px; border-radius:50%; background:#22c55e; margin-right:8px; }
    </style>
</head>
<body>
<div class="lab-banner">
    <div><span class="brand">🛡️ BBA</span> Lab — Command Injection</div>
    <div><a class="link" href="<?= BASE_URL ?>modules/labs/index.php">⬅ Back to Labs</a></div>
</div>

<header class="site-header">
    <div class="inner">
        <div class="logo">⚡ PingGrid <span>Host Monitor</span></div>
        <nav>
            <a href="#">Dashboard</a>
            <a href="#">Hosts</a>
            <a href="#">Logs</a>
        </nav>
    </div>
</header>

<main class="site-main">
    <div class="site-card">
        <h1>Ping a Host</h1>
        <p class="sub"><span class="status-dot"></span>All systems operational. Enter an IP address to run a diagnostic ping.</p>

        <form method="GET" class="site-form">
            <label for="ip">Target IP Address</label>
            <input type="text" name="ip" id="ip" placeholder="127.0.0.1" value="<?= htmlspecialchars($_GET['ip'] ?? '') ?>">
            <button type="submit" class="btn-site">▶ Run Ping Test</button>
        </form>

        <?php if ($output): ?>
            <div class="terminal">
                <div class="term-head">Output — ping diagnostic</div>
                <?= htmlspecialchars($output) ?>
            </div>
        <?php endif; ?>

        <?php if ($msg): ?>
            <div class="alert ok"><?= $msg ?></div>
        <?php endif; ?>
    </div>

    <div class="flag-box">
        <h3>🏴 Achieved RCE? Submit the flag:</h3>
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
    'This tool lets you ping a host IP address.',
    'The app passes your input directly into a shell command (ping) without sanitising it.',
    'That means any shell command you append will also run on the server.',
    'Enter a normal IP first, e.g.  127.0.0.1 , and run the test to see ping output.',
    'Now append a shell command after the IP using a command separator.',
    'Try:  127.0.0.1; whoami   or   127.0.0.1 && cat /etc/passwd',
    'If you see the command output, you have achieved Remote Code Execution (RCE).',
    'Find the flag and submit it below to complete the lab.',
];
$stuckTip = 'Use a semicolon or && to chain commands, e.g. 127.0.0.1; whoami';
include '../../includes/stuck_widget.php';
?>
</body>
</html>
