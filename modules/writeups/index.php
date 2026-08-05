<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');

// Approved writeups (public)
$stmt = $pdo->query("SELECT w.*, u.username FROM writeups w JOIN users u ON w.user_id = u.id WHERE w.status='approved' ORDER BY w.created_at DESC");
$writeups = $stmt->fetchAll();

// The user's own submissions (with status)
$mine = $pdo->prepare("SELECT w.*, u.username FROM writeups w JOIN users u ON w.user_id = u.id WHERE w.user_id = ? ORDER BY w.created_at DESC");
$mine->execute([$_SESSION['user_id']]);
$myWriteups = $mine->fetchAll();
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<?php include '../../includes/nav.php'; ?>
<h1>📝 Community Writeups</h1>
<a href="submit.php" class="btn" style="display:inline-block; margin:10px 0 20px 0;">✍️ Submit Your Own</a>

<?php if (count($writeups) == 0): ?>
    <p style="color:#666;">No writeups yet. Be the first to share your knowledge!</p>
<?php endif; ?>

<?php foreach($writeups as $w): ?>
    <div style="border-bottom:1px solid rgba(255,255,255,0.05); padding:15px 0;">
        <h3><?= htmlspecialchars($w['title']) ?></h3>
        <p style="color:#888; font-size:0.85rem;">By: <?= htmlspecialchars($w['username']) ?> · <?= htmlspecialchars($w['created_at']) ?></p>
        <p><?= nl2br(htmlspecialchars($w['content'])) ?></p>
    </div>
<?php endforeach; ?>

<?php if (count($myWriteups) > 0): ?>
    <h2 style="margin-top:40px;">📂 My Submissions</h2>
    <?php foreach($myWriteups as $w): 
        $color = $w['status']=='approved' ? '#00e676' : ($w['status']=='rejected' ? '#ff6b6b' : '#ffb300');
    ?>
        <div style="border-bottom:1px solid rgba(255,255,255,0.05); padding:12px 0;">
            <p style="margin:0;">
                <strong><?= htmlspecialchars($w['title']) ?></strong>
                <span style="color:<?= $color ?>; font-size:0.8rem; margin-left:10px;">
                    [<?= $w['status'] ?>]
                </span>
            </p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<a href="<?= BASE_URL ?>dashboard.php" style="display:inline-block; margin-top:20px;">⬅ Back to Dashboard</a>
</div></body></html>
