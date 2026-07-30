<?php require_once 'includes/auth.php'; 
if (!isLoggedIn()) redirect('login.php');
$progress = getProgress($_SESSION['user_id']);
$badges = getBadges($_SESSION['user_id']);
$user = $pdo->prepare("SELECT streak FROM users WHERE id=?");
$user->execute([$_SESSION['user_id']]);
$streak = $user->fetchColumn();
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="assets/css/style.css"></head>
<body><div class="container">
<h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> 🔥</h1>
<p>Your Streak: <?= $streak ?> days</p>
<h3>Your Badges:</h3>
<?php foreach($badges as $b): ?>
    <span>🏅 <?= $b['name'] ?></span>
<?php endforeach; ?>

<h3>Modules:</h3>
<ul>
    <li><a href="modules/lessons/view.php?id=1">Lesson: HTTP Basics</a> <?= (isset($progress[1]) && $progress[1]=='completed') ? '✅' : '' ?></li>
    <li><a href="modules/labs/sqli.php?id=2">Lab: SQL Injection</a> <?= (isset($progress[2]) && $progress[2]=='completed') ? '✅' : '' ?></li>
    <li><a href="modules/tools/nmap.php?id=3">Tool: Nmap</a> <?= (isset($progress[3]) && $progress[3]=='completed') ? '✅' : '' ?></li>
    <li><a href="modules/quizzes/take.php?quiz_id=1">Quiz: HTTP Basics</a></li>
    <li><a href="modules/writeups/index.php">Community Writeups</a></li>
    <?php if (isAdmin()): ?><li><a href="admin/index.php">⚙️ Admin Panel</a></li><?php endif; ?>
</ul>
<a href="logout.php">Logout</a>
</div></body></html>