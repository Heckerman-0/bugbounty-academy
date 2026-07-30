<?php require_once 'includes/auth.php'; 
if (!isLoggedIn()) redirect('login.php');
$progress = getProgress($_SESSION['user_id']);
$badges = getBadges($_SESSION['user_id']);
$user = $pdo->prepare("SELECT streak FROM users WHERE id=?");
$user->execute([$_SESSION['user_id']]);
$streak = $user->fetchColumn();

// Calculate total modules and progress
$total_modules = 3; // Lesson 1, Lab 2, Tool 3
$completed_count = 0;
foreach($progress as $status) { if ($status == 'completed') $completed_count++; }
$percent = ($total_modules > 0) ? round(($completed_count / $total_modules) * 100) : 0;

// Get ALL badges to show locked/unlocked
$all_badges = $pdo->query("SELECT * FROM badges ORDER BY id")->fetchAll();
$earned_ids = array_column($badges, 'id');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard | Bug Bounty Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <?php include 'includes/nav.php'; ?>
    
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
        <h1>👋 Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-number"><?= $streak ?></div><div class="stat-label">🔥 Day Streak</div></div>
        <div class="stat-card"><div class="stat-number"><?= $completed_count ?>/<?= $total_modules ?></div><div class="stat-label">📦 Modules Done</div></div>
        <div class="stat-card"><div class="stat-number"><?= count($badges) ?></div><div class="stat-label">🏅 Badges Earned</div></div>
        <div class="stat-card"><div class="stat-number"><?= $percent ?>%</div><div class="stat-label">📈 Completion</div></div>
    </div>

    <!-- Achievements PROGRESS BAR -->
    <div style="margin: 30px 0 10px 0;">
        <div style="display:flex; justify-content:space-between; font-weight:600; font-size:0.9rem; color:#a0a0d0; margin-bottom:6px;">
            <span>🏆 Achievement Progress</span>
            <span><?= $percent ?>%</span>
        </div>
        <div style="width:100%; height:10px; background:rgba(255,255,255,0.05); border-radius:20px; overflow:hidden; box-shadow: inset 0 0 10px rgba(0,0,0,0.5);">
            <div style="width:<?= $percent ?>%; height:100%; background: linear-gradient(90deg, #00f2fe, #fe00fe, #00f2fe); background-size: 200% 100%; border-radius:20px; animation: shimmer 3s infinite linear;"></div>
        </div>
        <style>
            @keyframes shimmer {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }
        </style>
    </div>

    <!-- BADGE GRID (ALL BADGES - LOCKED/UNLOCKED) -->
    <h3 style="margin-top:35px;">🏅 All Achievements</h3>
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:20px; margin:15px 0 30px 0;">
        <?php foreach($all_badges as $b): ?>
            <?php $earned = in_array($b['id'], $earned_ids); ?>
            <div style="text-align:center; padding:20px 10px; border-radius:16px; background:<?= $earned ? 'rgba(0,242,254,0.08)' : 'rgba(255,255,255,0.02)' ?>; border:1px solid <?= $earned ? 'rgba(0,242,254,0.4)' : 'rgba(255,255,255,0.05)' ?>; transition: all 0.3s ease; filter: <?= $earned ? 'none' : 'grayscale(0.8) opacity(0.4)' ?>; <?= $earned ? 'box-shadow: 0 0 20px rgba(0,242,254,0.1);' : '' ?>">
                <div style="font-size:2.5rem; margin-bottom:5px;"><?= $earned ? '🏅' : '🔒' ?></div>
                <div style="font-weight:700; font-size:0.9rem; color:<?= $earned ? '#00f2fe' : '#666' ?>;"><?= htmlspecialchars($b['name']) ?></div>
                <div style="font-size:0.7rem; color:#666; margin-top:4px;"><?= $earned ? '✅ Unlocked' : '🔒 Locked' ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Learning Path -->
    <h3>📚 Learning Path</h3>
    <ul class="module-list">
        <li><span><a href="modules/lessons/view.php?id=1">📖 Lesson: HTTP Basics</a></span><span class="checkmark"><?= (isset($progress[1]) && $progress[1]=='completed') ? '✅' : '⬜' ?></span></li>
        <li><span><a href="modules/labs/sqli.php?id=2">💉 Lab: SQL Injection</a></span><span class="checkmark"><?= (isset($progress[2]) && $progress[2]=='completed') ? '✅' : '⬜' ?></span></li>
        <li><span><a href="modules/tools/nmap.php?id=3">🛠️ Tool: Nmap Simulator</a></span><span class="checkmark"><?= (isset($progress[3]) && $progress[3]=='completed') ? '✅' : '⬜' ?></span></li>
        <li><span><a href="modules/quizzes/take.php?quiz_id=1">🧠 Quiz: HTTP Basics</a></span><span class="checkmark">⬜</span></li>
        <li><span><a href="modules/writeups/index.php">📝 Community Writeups</a></span><span class="checkmark">➡️</span></li>
        <?php if (isAdmin()): ?><li><span>⚙️ <a href="admin/index.php">Admin Panel</a></span></li><?php endif; ?>
    </ul>
</div>
</body>
</html>