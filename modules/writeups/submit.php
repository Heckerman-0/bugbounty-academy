<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO writeups (user_id, title, content) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['title'], $_POST['content']]);
    $msg = "Writeup submitted for review!";
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<h1>Submit a Writeup</h1>
<form method="POST">
    <input type="text" name="title" placeholder="Title" required><br>
    <textarea name="content" rows="10" cols="50" placeholder="Share your knowledge..." required></textarea><br>
    <button type="submit">Submit</button>
</form>
<p><?= $msg ?></p>
<a href="index.php">View Writeups</a>
</div></body></html>