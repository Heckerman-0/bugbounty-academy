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
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<?php include '../../includes/nav.php'; ?>
<h1>📁 Directory Traversal Lab</h1>
<p>This app serves files from a directory. Try to escape it using <code>../</code> to read files outside the web root (e.g. <code>../../../../etc/passwd</code> on Linux, or a secret file).</p>

<form method="GET">
    <input type="text" name="file" placeholder="notes.txt" style="width:300px;">
    <button type="submit">Open File</button>
</form>

<?php if ($file_content !== ""): ?>
    <div style="border:1px solid #ccc; padding:10px; margin-top:10px;">
        <?php if ($broke): ?>
            <p style="color:green;"><b>Exploit Success!</b> You escaped the directory!</p>
        <?php endif; ?>
        <pre><?= htmlspecialchars(mb_substr($file_content, 0, 2000)) ?></pre>
    </div>
<?php endif; ?>
<div style="border:1px solid #ccc; padding:10px; margin-top:10px;"><?= $msg ?></div>

<form method="POST">
    <?= csrfField() ?>
    <h3>Found the flag? Submit it:</h3>
    <input type="text" name="flag" placeholder="Enter flag">
    <button type="submit">Submit Flag</button>
</form>
<?php if ($flag_correct): ?><h2 style="color:green;">✅ Lab Passed!</h2><?php endif; ?>
<a href="<?= BASE_URL ?>modules/labs/index.php" style="display:inline-block; margin-top:15px;">Back to Labs</a>
</div></body></html>
