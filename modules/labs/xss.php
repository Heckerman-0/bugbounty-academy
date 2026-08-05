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
<html>
<head>
    <title>DevForum | Community</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>includes/lab.css">
    <style>
        :root { --accent: #7c3aed; --accent2: #4c1d95; }
        .thread { display: flex; gap: 14px; padding: 14px 0; border-bottom: 1px solid var(--border); }
        .thread .avatar { width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg,#7c3aed,#4c1d95); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; flex-shrink:0; }
        .thread .meta { font-size: 0.8rem; color: var(--muted); margin-bottom: 4px; }
        .comment-box { background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 12px; padding: 16px; margin-top: 12px; }
    </style>
</head>
<body>
<div class="lab-banner">
    <div><span class="brand">🛡️ BBA</span> Lab — Reflected XSS</div>
    <div><a class="link" href="<?= BASE_URL ?>modules/labs/index.php">⬅ Back to Labs</a></div>
</div>

<header class="site-header">
    <div class="inner">
        <div class="logo">DevForum <span>/ community</span></div>
        <nav>
            <a href="#">Home</a>
            <a href="#">Topics</a>
            <a href="#">Profile</a>
        </nav>
    </div>
</header>

<main class="site-main">
    <div class="site-card">
        <h1>Post a comment</h1>
        <p class="sub">Share your thoughts on the latest security news.</p>

        <form method="GET" class="site-form">
            <input type="text" name="comment" placeholder="Write a comment...">
            <button type="submit" class="btn-site">💬 Post Comment</button>
        </form>

        <?php if ($output !== ""): ?>
            <div class="comment-box">
                <div class="thread">
                    <div class="avatar">Yo</div>
                    <div>
                        <div class="meta">Anonymous · just now</div>
                        <div><?= $output ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($xss_triggered): ?>
            <div class="alert ok" style="margin-top:16px;">🚨 XSS executed! Now submit the flag below.</div>
        <?php endif; ?>
    </div>

    <div class="flag-box">
        <h3>🏴 Found the flag?</h3>
        <form method="POST" class="site-form">
            <?= csrfField() ?>
            <input type="text" name="flag" placeholder="Enter flag">
            <button type="submit" class="btn-site">Submit Flag</button>
        </form>
        <?php if ($flag_msg): ?><div style="margin-top:10px;"><?= htmlspecialchars($flag_msg) ?></div><?php endif; ?>
        <?php if ($flag_correct): ?><div class="flag-done">✅ Lab Passed!</div><?php endif; ?>
    </div>
</main>
</body>
</html>
