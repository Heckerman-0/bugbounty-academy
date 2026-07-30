<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM content WHERE id=? AND type='lesson'");
$stmt->execute([$id]);
$lesson = $stmt->fetch();
if (!$lesson) die("Lesson not found");
markComplete($_SESSION['user_id'], $id);
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<h1><?= htmlspecialchars($lesson['title']) ?></h1>
<div><?= $lesson['body_html'] ?></div>
<a href="<?= BASE_URL ?>dashboard.php">Back</a>
</div></body></html>