<?php 
require_once '../includes/auth.php';
if (!isLoggedIn() || !isAdmin()) redirect('dashboard.php');

$msg = "";
$error = "";

// --- DELETE ---
if (isset($_GET['delete'])) {
    if (!verifyCsrfTokenGet()) { $error = "❌ Invalid security token."; }
    else {
        $id = (int)$_GET['delete'];
        try {
            $stmt = $pdo->prepare("DELETE FROM content WHERE id = ?");
            $stmt->execute([$id]);
            $msg = "✅ Content ID $id deleted successfully.";
        } catch(PDOException $e) {
            $error = "❌ Cannot delete: " . $e->getMessage();
        }
    }
}

// --- ADD ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_content'])) {
    if (!verifyCsrfToken()) { $error = "❌ Invalid security token."; }
    else {
        $stmt = $pdo->prepare("INSERT INTO content (type, title, description, body_html, difficulty, points, module_group) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([
            $_POST['type'],
            $_POST['title'],
            $_POST['desc'],
            $_POST['body'],
            $_POST['diff'],
            (int)$_POST['points'],
            $_POST['module_group'] ?: null
        ]);
        $msg = "✅ Content added successfully!";
    }
}

// --- EDIT (Fetch data for form) ---
$editItem = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM content WHERE id = ?");
    $stmt->execute([$id]);
    $editItem = $stmt->fetch();
    if (!$editItem) {
        $error = "❌ Item not found.";
    }
}

// --- EDIT (Update) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_content'])) {
    if (!verifyCsrfToken()) { $error = "❌ Invalid security token."; }
    else {
        $id = (int)$_POST['edit_id'];
        $stmt = $pdo->prepare("UPDATE content SET type=?, title=?, description=?, body_html=?, difficulty=?, points=?, module_group=? WHERE id=?");
        $stmt->execute([
            $_POST['type'],
            $_POST['title'],
            $_POST['desc'],
            $_POST['body'],
            $_POST['diff'],
            (int)$_POST['points'],
            $_POST['module_group'] ?: null,
            $id
        ]);
        $msg = "✅ Content ID $id updated successfully!";
        $editItem = null; // Clear edit mode
    }
}

// --- WRITEUP MODERATION ---
if (isset($_GET['w_action'])) {
    if (!verifyCsrfTokenGet()) { $error = "❌ Invalid security token."; }
    else {
        $wid = (int)$_GET['wid'];
        $action = $_GET['w_action']; // approve | reject | delete
        if ($action == 'approve') {
            $pdo->prepare("UPDATE writeups SET status='approved' WHERE id=?")->execute([$wid]);
            $msg = "✅ Writeup #$wid approved.";
        } elseif ($action == 'reject') {
            $pdo->prepare("UPDATE writeups SET status='rejected' WHERE id=?")->execute([$wid]);
            $msg = "❌ Writeup #$wid rejected.";
        } elseif ($action == 'delete') {
            $pdo->prepare("DELETE FROM writeups WHERE id=?")->execute([$wid]);
            $msg = "🗑️ Writeup #$wid deleted.";
        }
    }
}

// --- List all content ---
$allContent = $pdo->query("SELECT * FROM content ORDER BY id DESC")->fetchAll();

// --- Pending + all writeups ---
$pendingWriteups = $pdo->query("SELECT w.*, u.username FROM writeups w JOIN users u ON w.user_id = u.id WHERE w.status='pending' ORDER BY w.created_at ASC")->fetchAll();
$allWriteups = $pdo->query("SELECT w.*, u.username FROM writeups w JOIN users u ON w.user_id = u.id ORDER BY w.created_at DESC LIMIT 50")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel | Bug Bounty Academy</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .admin-table th { text-align: left; background: rgba(0,242,254,0.1); padding: 10px; }
        .admin-table td { padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .admin-table tr:hover { background: rgba(255,255,255,0.02); }
        .actions a { margin-right: 12px; font-weight: 600; }
        .actions .edit { color: #00f2fe; }
        .actions .delete { color: #ff6b6b; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: rgba(255,255,255,0.02); padding: 25px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); margin: 20px 0; }
        .form-grid .full-width { grid-column: 1 / -1; }
        .badge-status { background: rgba(0,242,254,0.1); padding: 2px 12px; border-radius: 20px; font-size: 0.8rem; }
        .module-tag { background: rgba(254,0,254,0.1); color: #fe00fe; padding: 2px 12px; border-radius: 20px; font-size: 0.7rem; }
    </style>
</head>
<body>
<div class="container">
    <?php include '../includes/nav.php'; ?>
    
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:20px;">
        <h1>⚙️ Admin Panel</h1>
        <a href="<?= BASE_URL ?>dashboard.php" style="background:rgba(255,255,255,0.05); padding:8px 20px; border-radius:50px;">⬅ Back to Dashboard</a>
    </div>

    <?php if ($msg): ?><div style="background:rgba(0,242,254,0.1); border-left:4px solid #00f2fe; padding:15px; border-radius:8px; margin-bottom:20px;"><?= $msg ?></div><?php endif; ?>
    <?php if ($error): ?><div style="background:rgba(254,0,0,0.1); border-left:4px solid #fe0000; padding:15px; border-radius:8px; margin-bottom:20px; color:#ff6b6b;"><?= $error ?></div><?php endif; ?>

    <!-- ========== ADD / EDIT FORM ========== -->
    <?php 
    $form_title = 'Add New Content';
    $submit_name = 'add_content';
    $type = $editItem ? $editItem['type'] : 'lesson';
    $title = $editItem ? $editItem['title'] : '';
    $desc = $editItem ? $editItem['description'] : '';
    $body = $editItem ? $editItem['body_html'] : '';
    $diff = $editItem ? $editItem['difficulty'] : 'Beginner';
    $points = $editItem ? $editItem['points'] : 10;
    $module_group = $editItem ? $editItem['module_group'] : '';
    $edit_id = $editItem ? $editItem['id'] : 0;

    if ($editItem) {
        $form_title = '✏️ Edit: ' . htmlspecialchars($editItem['title']);
        $submit_name = 'update_content';
    }
    ?>

<h2><?= $form_title ?></h2>
    <form method="POST" class="form-grid">
        <?= csrfField() ?>
        <?php if ($editItem): ?>
            <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
        <?php endif; ?>

        <div>
            <label>Type</label>
            <select name="type" required>
                <option value="lesson" <?= ($type == 'lesson') ? 'selected' : '' ?>>Lesson</option>
                <option value="lab" <?= ($type == 'lab') ? 'selected' : '' ?>>Lab</option>
                <option value="tool" <?= ($type == 'tool') ? 'selected' : '' ?>>Tool</option>
            </select>
        </div>
        <div>
            <label>Difficulty</label>
            <select name="diff">
                <option value="Beginner" <?= ($diff == 'Beginner') ? 'selected' : '' ?>>Beginner</option>
                <option value="Intermediate" <?= ($diff == 'Intermediate') ? 'selected' : '' ?>>Intermediate</option>
                <option value="Advanced" <?= ($diff == 'Advanced') ? 'selected' : '' ?>>Advanced</option>
            </select>
        </div>
        <div class="full-width">
            <label>Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($title) ?>" required>
        </div>
        <div class="full-width">
            <label>Description (short)</label>
            <input type="text" name="desc" value="<?= htmlspecialchars($desc) ?>" placeholder="Brief summary">
        </div>
        <div class="full-width">
            <label>Full HTML Content</label>
            <textarea name="body" rows="6" placeholder="Use <h2>, <p>, <ul>, <code> etc."><?= htmlspecialchars($body) ?></textarea>
        </div>
        <div>
            <label>Points (XP)</label>
            <input type="number" name="points" value="<?= $points ?>">
        </div>
        <div>
            <label>Module Group (e.g. 'http', 'nmap', or blank for bonus)</label>
            <input type="text" name="module_group" value="<?= htmlspecialchars($module_group) ?>" placeholder="e.g. http, nmap, xss">
        </div>
        <div class="full-width">
            <button type="submit" name="<?= $submit_name ?>">
                <?= $editItem ? '💾 Update Content' : '➕ Add Content' ?>
            </button>
            <?php if ($editItem): ?>
                <a href="index.php" style="margin-left:15px; background:rgba(255,255,255,0.05); padding:12px 32px; border-radius:50px;">Cancel Edit</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- ========== EXISTING CONTENT LIST ========== -->
    <h2 style="margin-top:40px;">📦 All Content</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Title</th>
                <th>Module</th>
                <th>Diff</th>
                <th>XP</th>
                <th style="text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($allContent as $item): ?>
                <tr>
                    <td>#<?= $item['id'] ?></td>
                    <td><span class="badge-status"><?= $item['type'] ?></span></td>
                    <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                    <td><?= $item['module_group'] ? '<span class="module-tag">'.htmlspecialchars($item['module_group']).'</span>' : '—' ?></td>
                    <td><?= $item['difficulty'] ?></td>
                    <td><?= $item['points'] ?></td>
<td style="text-align:center; white-space:nowrap;" class="actions">
                        <a href="<?= csrfUrl('?edit='.$item['id']) ?>" class="edit">✏️ Edit</a>
                        <a href="<?= csrfUrl('?delete='.$item['id']) ?>" class="delete" onclick="return confirm('⚠️ Permanently delete this item and its flags?')">🗑️ Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (count($allContent) == 0): ?>
                <tr><td colspan="7" style="text-align:center; color:#666; padding:30px;">No content yet. Add your first lesson above!</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ========== WRITEUP MODERATION ========== -->
    <h2 style="margin-top:50px;">📝 Writeup Moderation</h2>

    <?php if (count($pendingWriteups) > 0): ?>
        <h3 style="color:#fe00fe;">⏳ Pending Review (<?= count($pendingWriteups) ?>)</h3>
        <?php foreach ($pendingWriteups as $w): ?>
            <div style="background:rgba(0,242,254,0.04); border:1px solid rgba(0,242,254,0.15); border-radius:12px; padding:18px; margin:12px 0;">
                <strong><?= htmlspecialchars($w['title']) ?></strong>
                <span style="color:#888; font-size:0.85rem; margin-left:12px;">by <?= htmlspecialchars($w['username']) ?></span>
                <p style="color:#c8c8f0; font-size:0.9rem; margin-top:8px;"><?= nl2br(htmlspecialchars(mb_substr($w['content'], 0, 300))) ?>...</p>
<a href="<?= csrfUrl('?w_action=approve&wid='.$w['id']) ?>" style="color:#00e676; margin-right:15px;">✅ Approve</a>
                <a href="<?= csrfUrl('?w_action=reject&wid='.$w['id']) ?>" style="color:#ffb300; margin-right:15px;">❌ Reject</a>
                <a href="<?= csrfUrl('?w_action=delete&wid='.$w['id']) ?>" style="color:#ff6b6b;">🗑️ Delete</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color:#666;">No writeups pending review. 🎉</p>
    <?php endif; ?>

    <?php if (count($allWriteups) > 0): ?>
        <h3 style="margin-top:25px;">📚 All Writeups</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allWriteups as $w): ?>
                    <tr>
                        <td>#<?= $w['id'] ?></td>
                        <td><strong><?= htmlspecialchars($w['title']) ?></strong></td>
                        <td><?= htmlspecialchars($w['username']) ?></td>
                        <td>
                            <span class="badge-status" style="background:<?= $w['status']=='approved' ? 'rgba(0,230,118,0.15)':'rgba(255,255,255,0.05)' ?>; color:<?= $w['status']=='approved' ? '#00e676':'#888' ?>;">
                                <?= $w['status'] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($w['created_at']) ?></td>
<td class="actions">
                            <?php if ($w['status'] != 'approved'): ?>
                                <a href="<?= csrfUrl('?w_action=approve&wid='.$w['id']) ?>" class="edit">✅ Approve</a>&nbsp;
                            <?php endif; ?>
                            <?php if ($w['status'] != 'rejected'): ?>
                                <a href="<?= csrfUrl('?w_action=reject&wid='.$w['id']) ?>" class="delete">❌ Reject</a>&nbsp;
                            <?php endif; ?>
                            <a href="<?= csrfUrl('?w_action=delete&wid='.$w['id']) ?>" class="delete" onclick="return confirm('Delete this writeup?')">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
