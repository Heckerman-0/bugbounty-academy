<?php 
require_once 'includes/auth.php';
if (!isLoggedIn()) redirect('login.php');

// Leaderboard: rank users by total points earned (sum of completed content points)
$stmt = $pdo->query("
    SELECT u.id, u.username, u.streak,
           COUNT(up.id) AS items_done,
           COALESCE(SUM(c.points), 0) AS total_points
    FROM users u
    LEFT JOIN user_progress up ON up.user_id = u.id AND up.status = 'completed'
    LEFT JOIN content c ON c.id = up.content_id
    GROUP BY u.id
    ORDER BY total_points DESC, items_done DESC, u.username ASC
    LIMIT 50
");
$rankings = $stmt->fetchAll();

// Overall champion: highest total points across ALL content
$champion = $rankings[0] ?? null;

// Current user's rank
$my_rank = 0;
foreach ($rankings as $i => $r) {
    if ($r['id'] == $_SESSION['user_id']) {
        $my_rank = $i + 1;
        break;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Leaderboard | Bug Bounty Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .lb-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .lb-table th { text-align: left; background: rgba(0,242,254,0.08); padding: 12px 16px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #a0a0d0; }
        .lb-table td { padding: 14px 16px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .lb-table tr:hover td { background: rgba(0,242,254,0.03); }
        .lb-table .me td { background: rgba(254,0,254,0.08); border-left: 3px solid #fe00fe; }
        .rank-medal { font-size: 1.3rem; }
        .champion-card {
            background: linear-gradient(135deg, rgba(0,242,254,0.12), rgba(254,0,254,0.12));
            border: 1px solid rgba(0,242,254,0.3);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            margin: 20px 0 30px 0;
        }
        .champion-card .trophy { font-size: 3.5rem; }
        .champion-card .name { font-size: 1.6rem; font-weight: 800; }
        .champion-card .score { color: #00f2fe; font-weight: 700; }
    </style>
</head>
<body>
<div class="container">
    <?php include 'includes/nav.php'; ?>

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:20px;">
        <h1>🏆 Leaderboard</h1>
        <a href="dashboard.php" style="background:rgba(255,255,255,0.05); padding:8px 20px; border-radius:50px; font-size:0.9rem; border:1px solid rgba(255,255,255,0.1);">⬅ Back</a>
    </div>

    <?php if ($champion): ?>
        <div class="champion-card">
            <div class="trophy">🏆</div>
            <div class="name"><?= htmlspecialchars($champion['username']) ?></div>
            <p style="color:#888;">Current Champion</p>
            <div class="score">⚡ <?= (int)$champion['total_points'] ?> XP &nbsp;·&nbsp; 📦 <?= (int)$champion['items_done'] ?> items</div>
        </div>
    <?php endif; ?>

    <?php if ($my_rank > 0): ?>
        <p style="color:#888; margin-bottom:10px;">Your rank: <strong style="color:#00f2fe;">#<?= $my_rank ?></strong></p>
    <?php endif; ?>

    <table class="lb-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Hunter</th>
                <th>🔥 Streak</th>
                <th>📦 Items</th>
                <th>⚡ XP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rankings as $i => $r): ?>
                <tr class="<?= ($r['id'] == $_SESSION['user_id']) ? 'me' : '' ?>">
                    <td>
                        <?php 
                            if ($i == 0) echo '<span class="rank-medal">🥇</span>';
                            elseif ($i == 1) echo '<span class="rank-medal">🥈</span>';
                            elseif ($i == 2) echo '<span class="rank-medal">🥉</span>';
                            else echo $i + 1;
                        ?>
                    </td>
                    <td><strong><?= htmlspecialchars($r['username']) ?></strong><?= ($r['id'] == $_SESSION['user_id']) ? ' <span style="color:#fe00fe; font-size:0.8rem;">(you)</span>' : '' ?></td>
                    <td><?= (int)$r['streak'] ?> 🔥</td>
                    <td><?= (int)$r['items_done'] ?></td>
                    <td style="color:#00f2fe; font-weight:700;">⚡ <?= (int)$r['total_points'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (count($rankings) == 0): ?>
                <tr><td colspan="5" style="text-align:center; color:#666; padding:30px;">No hunters yet. Be the first!</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
