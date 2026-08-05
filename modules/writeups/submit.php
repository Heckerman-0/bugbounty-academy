<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$msg = "";
$msg_class = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken()) {
        $msg = "Invalid form submission. Please try again.";
        $msg_class = 'error';
    } elseif (empty(trim($_POST['title'])) || empty(trim($_POST['content']))) {
        $msg = "Title and content are required.";
        $msg_class = 'error';
    } else {
        $stmt = $pdo->prepare("INSERT INTO writeups (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], trim($_POST['title']), trim($_POST['content'])]);
        $msg = "✅ Writeup submitted for review!";
        $msg_class = 'success';
    }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<?php include '../../includes/nav.php'; ?>
<h1>✍️ Submit a Writeup</h1>
<?php if($msg): ?>
    <div style="margin:15px 0; <?= $msg_class=='error' ? 'color:#ff6b6b;' : 'color:#00e676;' ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<form method="POST">
    <?= csrfField() ?>
    <input type="text" name="title" placeholder="Title" style="width:100%; margin-bottom:10px;" required><br>
    <textarea name="content" rows="10" cols="50" placeholder="Share your knowledge..." style="width:100%; margin-bottom:10px;" required></textarea><br>
    <button type="submit">Submit for Review</button>
</form>
<p style="margin-top:10px;"><a href="index.php">⬅ View Writeups</a> &nbsp; <a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></p>
</div></body></html>
