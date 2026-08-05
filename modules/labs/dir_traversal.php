<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 23;
$msg = "";
$file_content = "";
$broke = false;
$flag_correct = false;

// Intentionally vulnerable: reads a file based on user input without sanitisation
$base = __DIR__ . '/files/';
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    $path = $base . $file;
    if (file_exists($path)) {
        $file_content = file_get_contents($path);
        // Detect if they escaped the base dir
        if (strpos($path, '..') !== false) {
            $broke = true;
        }
    } else {
        $file_content = "File not found.";
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
    <title>FileViewer | Document Management</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>includes/lab.css">
    <style>
        :root { --accent: #475569; --accent2: #1e293b; }
        .doc-side { background:#f8fafc; border:1px solid var(--border); border-radius:12px; overflow:hidden; }
        .doc-side .doc { padding:12px 16px; border-bottom:1px solid var(--border); font-size:0.9rem; cursor:pointer; }
        .doc-side .doc:hover { background:#eff6ff; }
        .doc-side .doc.active { background:#e0f2fe; border-left:3px solid var(--accent); font-weight:600; }
    </style>
</head>
<body>
<div class="lab-banner">
    <div><span class="brand">🛡️ BBA</span> Lab — Directory Traversal</div>
    <div><a class="link" href="<?= BASE_URL ?>modules/labs/index.php">⬅ Back to Labs</a></div>
</div>

<header class="site-header">
    <div class="inner">
        <div class="logo">📂 FileViewer <span>Document Cloud</span></div>
        <nav>
            <a href="#">My Files</a>
            <a href="#">Shared</a>
            <a href="#">Trash</a>
        </nav>
    </div>
</header>

<main class="site-main">
    <div class="site-card">
        <h1>Open a Document</h1>
        <p class="sub">Select a file or open one by name. This service fetches files from a local directory.</p>

        <form method="GET" class="site-form">
            <label for="file">Filename</label>
            <div style="display:flex; gap:10px;">
                <input type="text" name="file" id="file" placeholder="notes.txt" value="<?= htmlspecialchars($_GET['file'] ?? '') ?>">
                <button type="submit" class="btn-site">Open</button>
            </div>
        </form>

        <div class="grid-2" style="margin-top:20px;">
            <div class="doc-side">
                <div class="doc active">📄 notes.txt</div>
                <div class="doc">📄 secret.txt</div>
                <div class="doc">🖼️ banner.jpg</div>
                <div class="doc">📂 invoices/</div>
            </div>

            <div style="background:#f8fafc; border:1px solid var(--border); border-radius:12px; padding:16px;">
                <?php if ($file_content !== ""): ?>
                    <?php if ($broke): ?>
                        <div class="alert ok" style="margin-bottom:10px;"><b>Exploit Success!</b> You escaped the directory!</div>
                    <?php endif; ?>
                    <pre><?= htmlspecialchars(mb_substr($file_content, 0, 2000)) ?></pre>
                <?php else: ?>
                    <p style="color:var(--muted); margin:0;">Select a file to preview its contents.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($msg): ?><div class="alert warn" style="margin-top:16px;"><?= $msg ?></div><?php endif; ?>
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

<?php
$stuckSteps = [
    'This is a document viewer. It fetches and displays files from a folder on the server.',
    'The server builds the file path by joining your input onto a base directory.',
    'It does not prevent you from using ".." to climb out of that directory.',
    'That is the bug: Directory Traversal (also called Path Traversal).',
    'Browse to a document name, e.g. notes.txt, to see how the viewer works.',
    'Now try to escape the folder using .. sequences to read a file outside it.',
    'Enter something like:  ../../secret.txt  to read the hidden secret file.',
    'The file contains the flag — submit it below to complete the lab.',
];
$stuckTip = 'Use  ../  to climb directories, e.g.  ../../secret.txt  reads the flag file outside the folder.';
include '../../includes/stuck_widget.php';
?>
</body>
</html>
