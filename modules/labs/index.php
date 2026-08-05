<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('../../login.php');

$progress = getProgress($_SESSION['user_id']);

// Fetch all labs with their flags (to show which are complete)
$stmt = $pdo->query("
    SELECT c.*, lf.flag_text
    FROM content c
    LEFT JOIN lab_flags lf ON lf.content_id = c.id
    WHERE c.type = 'lab'
    ORDER BY c.id ASC
");
$labs = $stmt->fetchAll();

// Route each lab by slug
function labLink($slug, $id) {
    if ($slug == 'header-manipulation') return 'header_lab.php?id=' . (int)$id;
    if ($slug == 'xss-playground') return 'xss.php?id=' . (int)$id;
    if ($slug == 'nmap-lab') return '../tools/nmap.php?id=' . (int)$id;
    if ($slug == 'cmd-injection') return 'cmd_inject.php?id=' . (int)$id;
    if ($slug == 'ssrf-lab') return 'ssrf.php?id=' . (int)$id;
    if ($slug == 'idor-lab') return 'idor.php?id=' . (int)$id;
    if ($slug == 'file-upload') return 'file_upload.php?id=' . (int)$id;
    return 'sqli.php?id=' . (int)$id;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Labs | Bug Bounty Academy</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .lab-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 20px; }
        .lab-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 16px;
            padding: 22px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: 0.3s;
            position: relative;
        }
        .lab-card:hover { border-color: #00f2fe; transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .lab-card.done { border-color: rgba(0,230,118,0.4); }
        .lab-card .diff { position: absolute; top: 16px; right: 16px; font-size: 0.7rem; background: rgba(0,242,254,0.1); padding: 3px 12px; border-radius: 20px; color: #00f2fe; }
        .lab-card .diff.advanced { background: rgba(254,0,0,0.15); color: #ff6b6b; }
        .lab-card .diff.intermediate { background: rgba(254,0,254,0.15); color: #fe00fe; }
        .lab-card h3 { margin: 0; font-size: 1.2rem; }
        .lab-card p { font-size: 0.9rem; color: #8888aa; flex-grow: 1; }
        .lab-card .meta { font-size: 0.85rem; color: #666; }
        .lab-card .enter { margin-top: 10px; }
        .status-pill { font-size: 0.75rem; padding: 4px 12px; border-radius: 20px; }
        .status-pill.complete { background: rgba(0,230,118,0.15); color: #00e676; }
        .status-pill.pending { background: rgba(255,255,255,0.05); color: #888; }
    </style>
</head>
<body>
<div class="container">
    <?php include '../../includes/nav.php'; ?>

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:10px;">
        <h1>💻 Attack Labs</h1>
        <a href="<?= BASE_URL ?>dashboard.php" style="background:rgba(255,255,255,0.05); padding:8px 20px; border-radius:50px; font-size:0.9rem; border:1px solid rgba(255,255,255,0.1);">⬅ Back</a>
    </div>
    <p style="color:#888; margin-bottom:20px;">Practice your skills against intentionally vulnerable targets. Each lab has a flag — capture it to earn XP and badges!</p>

    <?php if (count($labs) == 0): ?>
        <p style="color:#666; text-align:center; padding:40px;">No labs available yet. Check back soon!</p>
    <?php endif; ?>

    <div class="lab-grid">
        <?php foreach ($labs as $lab): 
            $done = isset($progress[$lab['id']]) && $progress[$lab['id']] == 'completed';
            $diffClass = strtolower($lab['difficulty']);
        ?>
            <div class="lab-card <?= $done ? 'done' : '' ?>">
                <span class="diff <?= $diffClass ?>"><?= htmlspecialchars($lab['difficulty']) ?></span>
                <h3><?= htmlspecialchars($lab['title']) ?></h3>
                <p><?= htmlspecialchars($lab['description']) ?></p>
                <div class="meta">
                    ⚡ <?= (int)$lab['points'] ?> XP
                    <span style="float:right;">
                        <?php if ($done): ?>
                            <span class="status-pill complete">✅ Completed</span>
                        <?php else: ?>
                            <span class="status-pill pending">⬜ Not started</span>
                        <?php endif; ?>
                    </span>
                </div>
                <a href="<?= labLink($lab['slug'], $lab['id']) ?>" class="btn enter"><?= $done ? '🔁 Retry' : '🚀 Enter Lab' ?></a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
