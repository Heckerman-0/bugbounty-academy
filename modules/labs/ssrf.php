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
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<h1>SSRF Lab</h1>
<p>This tool fetches a URL on the server side. Try accessing an internal resource like <code>http://127.0.0.1:8080/admin</code> to read the flag.</p>
<form method="GET">
    <input type="text" name="url" placeholder="http://example.com" value="http://example.com">
    <button type="submit">Fetch</button>
</form>
<?php if ($result): ?>
<div style="border:1px solid #ccc; padding:10px;"><pre><?= htmlspecialchars(mb_substr($result, 0, 2000)) ?></pre></div>
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
