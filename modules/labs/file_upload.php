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
<html>
<head>
    <title>CloudDump | File Storage</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>includes/lab.css">
    <style>
        :root { --accent: #f59e0b; --accent2: #b45309; }
        .file-list { margin-top: 16px; }
        .file-row { display:flex; justify-content:space-between; align-items:center; padding:12px 14px; border:1px solid var(--border); border-radius:10px; margin:8px 0; }
        .file-row .name { font-weight:600; }
        .storage-bar { height:8px; background:#e5e7eb; border-radius:20px; overflow:hidden; margin-top:10px; }
        .storage-bar > div { height:100%; width:32%; background: linear-gradient(90deg,#f59e0b,#b45309); border-radius:20px; }
    </style>
</head>
<body>
<div class="lab-banner">
    <div><span class="brand">🛡️ BBA</span> Lab — File Upload</div>
    <div><a class="link" href="<?= BASE_URL ?>modules/labs/index.php">⬅ Back to Labs</a></div>
</div>

<header class="site-header">
    <div class="inner">
        <div class="logo">☁️ CloudDump <span>File Storage</span></div>
        <nav>
            <a href="#">My Files</a>
            <a href="#">Shared</a>
            <a href="#">Upgrade</a>
        </nav>
    </div>
</header>

<main class="site-main">
    <div class="site-card">
        <h1>Upload Files</h1>
        <p class="sub">Securely store and share your documents. 2GB free of 5GB used.</p>
        <div class="storage-bar"><div></div></div>

        <form method="POST" enctype="multipart/form-data" class="site-form" style="margin-top:20px;">
            <?= csrfField() ?>
            <div class="dropzone">
                <span class="dz-icon">📁</span>
                Click to choose a file to upload
                <input type="file" name="file" required style="display:none;" id="fileInput">
            </div>
            <button type="submit" class="btn-site block">⬆ Upload File</button>
        </form>

        <?php if ($msg): ?>
            <div class="alert ok" style="margin-top:16px;"><?= $msg ?></div>
        <?php endif; ?>

        <div class="file-list">
            <h3>Recent Files</h3>
            <div class="file-row"><div class="name">📄 notes.txt</div><div>2 KB</div></div>
            <div class="file-row"><div class="name">📄 secret.txt</div><div>1 KB</div></div>
            <div class="file-row"><div class="name">🖼️ avatar.png</div><div>48 KB</div></div>
        </div>
    </div>

    <div class="flag-box">
        <h3>🏴 Uploaded a webshell? Submit the flag:</h3>
        <form method="POST" class="site-form">
            <?= csrfField() ?>
            <input type="text" name="flag" placeholder="Enter flag">
            <button type="submit" class="btn-site">Submit Flag</button>
        </form>
        <?php if ($flag_correct): ?><div class="flag-done">✅ Lab Passed!</div><?php endif; ?>
    </div>
</main>
<script>
    document.getElementById("fileInput").addEventListener("change", function(e) {
        var name = e.target.files.length ? e.target.files[0].name : "";
        var dz = document.querySelector(".dropzone");
        dz.innerHTML = "<span class=\"dz-icon\">📁</span>" + (name || "Click to choose a file");
    });
</script>

<?php
$stuckSteps = [
    "This is a file storage site that lets you upload any file.",
    "The app only checks that a file was uploaded — it does NOT check the file type.",
    "That means you can upload a PHP file (webshell) even though it claims to accept documents.",
    "Create a file named  shell.php  containing:   <?php system(\$_GET[\"cmd\"]); ?>",
    "Upload that file. The app stores it on the server and shows you the path.",
    "Access the uploaded file in your browser and pass a command, e.g. the upload path + ?cmd=cat+secret.txt",
    "Running the command reads the hidden flag file on the server.",
    "Submit the revealed flag below to complete the lab.",
];
$stuckTip = "Upload a PHP webshell (shell.php) then call it with a command parameter to read the flag file.";
include "../../includes/stuck_widget.php";
?>
</body>
</html>