<?php require_once 'includes/auth.php'; 
if (!isLoggedIn()) redirect('login.php');

$progress = getProgress($_SESSION['user_id']);
$badges = getBadges($_SESSION['user_id']);
$user = $pdo->prepare("SELECT streak FROM users WHERE id=?");
$user->execute([$_SESSION['user_id']]);
$streak = $user->fetchColumn();

// Fetch all content grouped by module
$content = $pdo->query("SELECT * FROM content ORDER BY FIELD(module_group, 'http', 'nmap', NULL), id")->fetchAll();

// Helper to check completion
function isComplete($id) {
    global $progress;
    return (isset($progress[$id]) && $progress[$id] == 'completed');
}

// Separate into groups
$modules = [];
foreach ($content as $item) {
    $group = $item['module_group'] ?? 'bonus';
    if (!isset($modules[$group])) $modules[$group] = [];
    $modules[$group][] = $item;
}

// Calculate overall progress
$total_items = 0;
$done_items = 0;
foreach ($modules as $group => $items) {
    foreach ($items as $item) {
        $total_items++;
        if (isComplete($item['id'])) $done_items++;
    }
}
$percent = ($total_items > 0) ? round(($done_items / $total_items) * 100) : 0;
$completed_count = $done_items;
$total_modules = $total_items;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard | Bug Bounty Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .module-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(0, 242, 254, 0.1);
            border-radius: 16px;
            padding: 20px 25px;
            margin: 20px 0;
            transition: 0.3s;
        }
        .module-card:hover {
            border-color: #00f2fe;
            box-shadow: 0 5px 30px rgba(0,0,0,0.3);
        }
        .module-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #00f2fe, #fe00fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .module-items {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 10px;
        }
        .module-item {
            background: rgba(255,255,255,0.03);
            padding: 10px 20px;
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.2s;
        }
        .module-item a {
            border-bottom: none;
        }
        .module-item .status {
            font-size: 1.2rem;
        }
        .bonus-tag {
            font-size: 0.7rem;
            background: rgba(254, 0, 254, 0.2);
            padding: 2px 12px;
            border-radius: 20px;
            color: #fe00fe;
            margin-left: 10px;
        }
    </style>
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
        <div class="stat-card"><div class="stat-number"><?= $completed_count ?>/<?= $total_modules ?></div><div class="stat-label">📦 Items Done</div></div>
        <div class="stat-card"><div class="stat-number"><?= count($badges) ?></div><div class="stat-label">🏅 Badges Earned</div></div>
        <div class="stat-card"><div class="stat-number"><?= $percent ?>%</div><div class="stat-label">📈 Completion</div></div>
    </div>

    <!-- Achievements Progress Bar -->
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

    <!-- Badge Grid -->
    <h3 style="margin-top:35px;">🏅 Your Badges</h3>
    <div style="display:flex; gap:15px; flex-wrap:wrap; margin:10px 0 30px 0;">
        <?php if(count($badges) > 0): ?>
            <?php foreach($badges as $b): ?>
                <span style="background:rgba(0,242,254,0.1); padding:8px 20px; border-radius:50px; border:1px solid rgba(0,242,254,0.3);">
                    🏅 <?= $b['name'] ?>
                </span>
            <?php endforeach; ?>
        <?php else: ?>
            <span style="color:#666;">Complete a lab to earn your first badge!</span>
        <?php endif; ?>
    </div>

    <!-- STRUCTURED MODULES -->
    <h2 style="margin-top:30px;">📚 Your Learning Path</h2>

    <?php foreach ($modules as $group => $items): ?>
        <?php if ($group == 'bonus'): ?>
            <div class="module-card" style="border-color: rgba(254,0,254,0.2);">
                <div class="module-title">🎯 Bonus Challenge <span class="bonus-tag">Side Quest</span></div>
        <?php else: ?>
            <div class="module-card">
                <div class="module-title">
                    <?php 
                        if ($group == 'http') echo '📦 Module 1: HTTP Basics';
                        elseif ($group == 'nmap') echo '📦 Module 2: Network Scanning (Nmap)';
                        else echo ucfirst($group);
                    ?>
                </div>
        <?php endif; ?>
        
        <div class="module-items">
            <?php foreach ($items as $item): 
                $type_icon = ['lesson' => '📖', 'lab' => '💉', 'tool' => '🛠️'];
                $type_label = ['lesson' => 'Lesson', 'lab' => 'Lab', 'tool' => 'Tool'];
                $icon = $type_icon[$item['type']] ?? '📄';
                $label = $type_label[$item['type']] ?? 'Item';
                $completed = isComplete($item['id']);
                $status_icon = $completed ? '✅' : '⬜';
                
                // Determine link
                $link = '#';
                if ($item['type'] == 'lesson') $link = 'modules/lessons/view.php?id=' . $item['id'];
                elseif ($item['type'] == 'tool') $link = 'modules/tools/nmap.php?id=' . $item['id'];
                elseif ($item['type'] == 'lab') {
                    if ($item['id'] == 4) $link = 'modules/labs/header_lab.php?id=' . $item['id']; // We'll create this below
                    elseif ($item['id'] == 5) $link = 'modules/tools/nmap.php?id=3'; // Nmap lab is integrated
                    else $link = 'modules/labs/sqli.php?id=' . $item['id'];
                }
            ?>
                <div class="module-item">
                    <span><?= $icon ?></span>
                    <a href="<?= $link ?>"><?= htmlspecialchars($item['title']) ?></a>
                    <span class="status"><?= $status_icon ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div style="margin-top:20px; text-align:center; color:#666; border-top:1px solid rgba(255,255,255,0.05); padding-top:20px;">
        ⚡ Complete all items in a module to master the topic!
    </div>
</div>
</body>
</html>