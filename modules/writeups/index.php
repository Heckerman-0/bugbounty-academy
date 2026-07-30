<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$stmt = $pdo->query("SELECT w.*, u.username FROM writeups w JOIN users u ON w.user_id = u.id WHERE w.status='approved' ORDER BY w.created_at DESC");
$writeups = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<h1>Community Writeups</h1>
<a href="submit.php">Submit Your Own</a><br><br>
<?php foreach($writeups as $w): ?>
    <div style="border-bottom:1px solid #ccc;">
        <h3><?= htmlspecialchars($w['title']) ?></h3>
        <p>By: <?= htmlspecialchars($w['username']) ?></p>
        <p><?= nl2br(htmlspecialchars($w['content'])) ?></p>
    </div>
<?php endforeach; ?>
<a href="<?= BASE_URL ?>dashboard.php">Back</a>
</div></body></html>