<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 17;
$msg = "";
$result = "";
$flag_correct = false;

if (isset($_GET['url'])) {
    $url = $_GET['url'];
    // Intentionally vulnerable SSRF: fetches arbitrary URL server-side
    $result = @file_get_contents($url);
    if ($result === false) {
        $result = "Could not fetch URL.";
    } else {
        $msg = "<b>Exploit Success!</b> The server fetched the URL for you.";
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
    <title>FetchIO | URL Inspector</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>includes/lab.css">
    <style>
        :root { --accent: #0d9488; --accent2: #134e4a; }
        .endpoint { background:#f0fdfa; border:1px solid #99f6e4; border-radius:10px; padding:12px 16px; font-family:'Consolas',monospace; font-size:0.85rem; color:#134e4a; margin:10px 0; }
    </style>
</head>
<body>
<div class="lab-banner">
    <div><span class="brand">🛡️ BBA</span> Lab — SSRF</div>
    <div><a class="link" href="<?= BASE_URL ?>modules/labs/index.php">⬅ Back to Labs</a></div>
</div>

<header class="site-header">
    <div class="inner">
        <div class="logo">🌐 FetchIO <span>URL Inspector API</span></div>
        <nav>
            <a href="#">Docs</a>
            <a href="#">Console</a>
            <a href="#">Status</a>
        </nav>
    </div>
</header>

<main class="site-main">
    <div class="site-card">
        <h1>Fetch a URL</h1>
        <p class="sub">Enter a URL and our service will fetch its contents server-side.</p>

        <form method="GET" class="site-form">
            <label for="url">Target URL</label>
            <input type="text" name="url" id="url" placeholder="http://example.com" value="http://example.com">
            <button type="submit" class="btn-site">🔍 Fetch URL</button>
        </form>

        <div class="endpoint">GET <?= htmlspecialchars($_GET['url'] ?? 'http://example.com') ?></div>

        <?php if ($result): ?>
            <div class="terminal">
                <div class="term-head">Response body (first 2000 chars)</div>
                <?= htmlspecialchars(mb_substr($result, 0, 2000)) ?>
            </div>
        <?php endif; ?>

        <?php if ($msg): ?>
            <div class="alert ok"><?= $msg ?></div>
        <?php endif; ?>
    </div>

    <div class="flag-box">
        <h3>🏴 Found the flag? Submit it:</h3>
        <form method="POST" class="site-form">
            <?= csrfField() ?>
            <input type="text" name="flag" placeholder="Enter flag">
            <button type="submit" class="btn-site">Submit Flag</button>
        </form>
        <?php if ($flag_correct): ?><div class="flag-done">✅ Lab Passed!</div><?php endif; ?>
    </div>
</main>
</body>
</html>
