<?php require_once 'includes/auth.php'; 
if (!isLoggedIn()) redirect('login.php');
$progress = getProgress($_SESSION['user_id']);
$badges = getBadges($_SESSION['user_id']);
$user = $pdo->prepare("SELECT streak FROM users WHERE id=?");
$user->execute([$_SESSION['user_id']]);
$streak = $user->fetchColumn();

$completed_count = 0;
foreach($progress as $status) { if ($status == 'completed') $completed_count++; }
$total_modules = 3;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard | Bug Bounty Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
        <h1>👋 Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
        <a href="logout.php" style="background:rgba(255,255,255,0.05); padding:8px 20px; border-radius:50px; font-size:0.9rem;">Logout</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $streak ?></div>
            <div class="stat-label">🔥 Day Streak</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $completed_count ?>/<?= $total_modules ?></div>
            <div class="stat-label">📦 Modules Done</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count($badges) ?></div>
            <div class="stat-label">🏅 Badges Earned</div>
        </div>
    </div>

    <h3>🏅 Your Badges</h3>
    <div style="display:flex; gap:15px; flex-wrap:wrap; margin:10px 0 25px 0;">
        <?php if(count($badges) > 0): ?>
            <?php foreach($badges as $b): ?>
                <span style="background:rgba(0,242,254,0.1); padding:8px 20px; border-radius:50px; border:1px solid rgba(0,242,254,0.3);">
                    🏅 <?= $b['name'] ?>
                </span>
            <?php endforeach; ?>
        <?php else: ?>
            <span style="color:#666;">Complete your first lab to earn a badge!</span>
        <?php endif; ?>
    </div>

    <h3>📚 Learning Path</h3>
    <ul class="module-list">
        <li>
            <span><a href="modules/lessons/view.php?id=1">📖 Lesson: HTTP Basics</a></span>
            <span class="checkmark"><?= (isset($progress[1]) && $progress[1]=='completed') ? '✅' : '⬜' ?></span>
        </li>
        <li>
            <span><a href="modules/labs/sqli.php?id=2">💉 Lab: SQL Injection</a></span>
            <span class="checkmark"><?= (isset($progress[2]) && $progress[2]=='completed') ? '✅' : '⬜' ?></span>
        </li>
        <li>
            <span><a href="modules/tools/nmap.php?id=3">🛠️ Tool: Nmap Simulator</a></span>
            <span class="checkmark"><?= (isset($progress[3]) && $progress[3]=='completed') ? '✅' : '⬜' ?></span>
        </li>
        <li>
            <span><a href="modules/quizzes/take.php?quiz_id=1">🧠 Quiz: HTTP Basics</a></span>
            <span class="checkmark">⬜</span>
        </li>
        <li>
            <span><a href="modules/writeups/index.php">📝 Community Writeups</a></span>
            <span class="checkmark">➡️</span>
        </li>
        <?php if (isAdmin()): ?>
            <li><span>⚙️ <a href="admin/index.php">Admin Panel</a></span></li>
        <?php endif; ?>
    </ul>
</div>
</body>
</html>