<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 19;
$msg = "";
$flag_correct = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
    $target = 'uploads/' . basename($_FILES['file']['name']);
    // Intentionally vulnerable: accepts any file type, including .php
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $msg = "✅ File uploaded to <code>" . htmlspecialchars($target) . "</code>! Try uploading a PHP webshell to execute commands.";
    } else {
        $msg = "❌ Upload failed.";
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
<h1>File Upload Lab</h1>
<p>This app lets you upload any file to the server. The goal is to upload a PHP webshell and execute a command to read the flag file.</p>

<form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
</form>

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
