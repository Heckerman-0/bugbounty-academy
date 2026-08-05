<?php 
require_once '../includes/auth.php';
if (!isLoggedIn() || !isAdmin()) redirect('dashboard.php');

$msg = "";
$error = "";

// --- DELETE CONTENT ---
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

// --- ADD CONTENT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_content'])) {
    if (!verifyCsrfToken()) { $error = "❌ Invalid security token."; }
    else {
        $slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
        $stmt = $pdo->prepare("INSERT INTO content (type, title, description, body_html, difficulty, points, module_group, slug) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $_POST['type'],
            $_POST['title'],
            $_POST['desc'],
            $_POST['body'],
            $_POST['diff'],
            (int)$_POST['points'],
            $_POST['module_group'] ?: null,
            $slug ?: null
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
        $slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
        $stmt = $pdo->prepare("UPDATE content SET type=?, title=?, description=?, body_html=?, difficulty=?, points=?, module_group=?, slug=? WHERE id=?");
        $stmt->execute([
            $_POST['type'],
            $_POST['title'],
            $_POST['desc'],
            $_POST['body'],
            $_POST['diff'],
            (int)$_POST['points'],
            $_POST['module_group'] ?: null,
            $slug ?: null,
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

// --- USER MANAGEMENT ---
if (isset($_GET['user_action'])) {
    if (!verifyCsrfTokenGet()) { $error = "❌ Invalid security token."; }
    else {
        $uid = (int)$_GET['uid'];
        $action = $_GET['user_action']; // promote | demote | delete
        if ($action == 'promote') {
            $pdo->prepare("UPDATE users SET role='admin' WHERE id=?")->execute([$uid]);
            $msg = "✅ User #$uid promoted to admin.";
        } elseif ($action == 'demote') {
            $pdo->prepare("UPDATE users SET role='user' WHERE id=?")->execute([$uid]);
            $msg = "⬇️ User #$uid demoted to user.";
        } elseif ($action == 'delete') {
            $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
            $msg = "🗑️ User #$uid deleted.";
        }
    }
}

// --- List all content ---
$allContent = $pdo->query("SELECT * FROM content ORDER BY id DESC")->fetchAll();

// --- Pending + all writeups ---
$pendingWriteups = $pdo->query("SELECT w.*, u.username FROM writeups w JOIN users u ON w.user_id = u.id WHERE w.status='pending' ORDER BY w.created_at ASC")->fetchAll();
$allWriteups = $pdo->query("SELECT w.*, u.username FROM writeups w JOIN users u ON w.user_id = u.id ORDER BY w.created_at DESC LIMIT 50")->fetchAll();

// --- Users with completion counts ---
$users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM user_progress up WHERE up.user_id = u.id AND up.status='completed') AS completed_count FROM users u ORDER BY u.id ASC")->fetchAll();

// --- Stats ---
$totalUsers = count($users);
$totalContent = count($allContent);
$totalLabs = $pdo->query("SELECT COUNT(*) FROM content WHERE type='lab'")->fetchColumn();
$pendingCount = count($pendingWriteups);
$totalCompleted = $pdo->query("SELECT COUNT(*) FROM user_progress WHERE status='completed'")->fetchColumn();
$totalXp = $pdo->query("SELECT COALESCE(SUM(c.points),0) FROM user_progress up JOIN content c ON up.content_id = c.id WHERE up.status='completed'")->fetchColumn();

// --- Overview data ---
$recentWriteups = $pdo->query("SELECT w.*, u.username FROM writeups w JOIN users u ON w.user_id = u.id ORDER BY w.created_at DESC LIMIT 5")->fetchAll();
$recentUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$typeBreakdown = $pdo->query("SELECT type, COUNT(*) as cnt FROM content GROUP BY type")->fetchAll();

// --- Form defaults ---
$form_title = '➕ Add New Content';
$submit_name = 'add_content';
$type = $editItem ? $editItem['type'] : 'lesson';
$title = $editItem ? $editItem['title'] : '';
$desc = $editItem ? $editItem['description'] : '';
$body = $editItem ? $editItem['body_html'] : '';
$diff = $editItem ? $editItem['difficulty'] : 'Beginner';
$points = $editItem ? $editItem['points'] : 10;
$module_group = $editItem ? $editItem['module_group'] : '';
$slug = $editItem ? $editItem['slug'] : '';
$edit_id = $editItem ? $editItem['id'] : 0;

if ($editItem) {
    $form_title = '✏️ Edit: ' . htmlspecialchars($editItem['title']);
    $submit_name = 'update_content';
}

$typeIcons = ['lesson' => '📖', 'lab' => '💉', 'tool' => '🛠️'];
$diffColors = ['Beginner' => '#00e676', 'Intermediate' => '#ffb300', 'Advanced' => '#ff4d6d'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel | Bug Bounty Academy</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .admin-welcome { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:20px; }
        .admin-welcome .sub { color:#8888aa; font-size:0.95rem; margin-top:4px; }

        /* ---- TABS ---- */
        .admin-tabs {
            display: flex; gap: 8px; flex-wrap: wrap;
            margin: 25px 0 20px 0;
            padding: 6px;
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(0,242,254,0.1);
            border-radius: 50px;
            width: fit-content;
        }
        .admin-tabs button {
            background: transparent; border: none; box-shadow: none;
            color: #a0a0d0; padding: 10px 22px; border-radius: 50px;
            font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease;
            text-transform: none; letter-spacing: normal;
        }
        .admin-tabs button:hover { color: #00f2fe; transform: none; background: rgba(0,242,254,0.06); }
        .admin-tabs button.active {
            background: linear-gradient(135deg, #00f2fe, #4a00e0);
            color: #fff; box-shadow: 0 4px 20px rgba(0,242,254,0.3);
        }
        .tab-panel { display: none; animation: fadeIn 0.4s ease; }
        .tab-panel.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

        /* ---- STAT STATS ---- */
        .admin-stats { display:grid; grid-template-columns: repeat(auto-fit, minmax(150px,1fr)); gap:16px; margin: 20px 0; }
        .admin-stat {
            background: rgba(255,255,255,0.02); border:1px solid rgba(0,242,254,0.1);
            border-radius:16px; padding:20px 16px; text-align:center; transition: all 0.3s ease;
        }
        .admin-stat:hover { transform: translateY(-4px); border-color:#00f2fe; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .admin-stat .num { font-size:2rem; font-weight:800; background: linear-gradient(135deg,#00f2fe,#fe00fe); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .admin-stat .lbl { color:#8888aa; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; margin-top:4px; }

        /* ---- TABLES ---- */
        .admin-table { width:100%; border-collapse:collapse; margin-top:12px; }
        .admin-table th { text-align:left; background:rgba(0,242,254,0.1); padding:12px; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; }
        .admin-table td { padding:12px; border-bottom:1px solid rgba(255,255,255,0.05); font-size:0.92rem; }
        .admin-table tr:hover { background:rgba(255,255,255,0.02); }

        /* ---- BADGES / TAGS ---- */
        .badge-diff { padding:3px 12px; border-radius:20px; font-size:0.75rem; font-weight:600; }
        .badge-status { background:rgba(0,242,254,0.1); padding:3px 12px; border-radius:20px; font-size:0.75rem; }
        .module-tag { background:rgba(254,0,254,0.1); color:#fe00fe; padding:3px 12px; border-radius:20px; font-size:0.7rem; }
        .role-admin { color:#fe00fe; font-weight:600; }
        .role-user { color:#8888aa; }

        /* ---- ACTIONS ---- */
        .actions a { margin-right:12px; font-weight:600; border-bottom:none; }
        .actions .edit { color:#00f2fe; }
        .actions .delete { color:#ff6b6b; }
        .actions .approve { color:#00e676; }
        .actions .reject { color:#ffb300; }

        /* ---- FORM ---- */
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; background:rgba(255,255,255,0.02); padding:25px; border-radius:16px; border:1px solid rgba(0,242,254,0.1); margin:20px 0; }
        .form-grid .full-width { grid-column:1 / -1; }
        .form-grid label { font-size:0.8rem; color:#8888aa; text-transform:uppercase; letter-spacing:0.5px; }

        /* ---- WRITEUP CARD ---- */
        .writeup-card { background:rgba(0,242,254,0.04); border:1px solid rgba(0,242,254,0.15); border-radius:12px; padding:18px; margin:12px 0; }
        .writeup-card .w-meta { color:#888; font-size:0.85rem; margin-top:6px; }
        .writeup-card .w-preview { color:#c8c8f0; font-size:0.9rem; margin-top:8px; }

        /* ---- SEARCH ---- */
        .search-box { margin: 15px 0; }
        .search-box input { max-width: 320px; }

        /* ---- TOOLBAR ---- */
        .toolbar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin:20px 0 5px 0; }
        .toolbar h2 { margin:0; }

        /* ---- ALERTS ---- */
        .alert-success { background:rgba(0,242,254,0.1); border-left:4px solid #00f2fe; padding:15px; border-radius:8px; margin-bottom:20px; }
        .alert-error { background:rgba(254,0,0,0.1); border-left:4px solid #fe0000; padding:15px; border-radius:8px; margin-bottom:20px; color:#ff6b6b; }

        @media (max-width: 700px) {
            .form-grid { grid-template-columns: 1fr; }
            .admin-tabs { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<div class="container">
    <?php include '../includes/nav.php'; ?>

    <!-- Header -->
    <div class="admin-welcome">
        <div>
            <h1>⚙️ Admin Panel</h1>
            <div class="sub">Control center for content, writeups & users</div>
        </div>
        <a href="<?= BASE_URL ?>dashboard.php" class="btn" style="background:rgba(255,255,255,0.05); box-shadow:none;">⬅ Back to Dashboard</a>
    </div>

    <?php if ($msg): ?><div class="alert-success"><?= $msg ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert-error"><?= $error ?></div><?php endif; ?>

    <!-- Stats bar -->
    <div class="admin-stats">
        <div class="admin-stat"><div class="num"><?= $totalUsers ?></div><div class="lbl">👥 Users</div></div>
        <div class="admin-stat"><div class="num"><?= $totalContent ?></div><div class="lbl">📄 Content</div></div>
        <div class="admin-stat"><div class="num"><?= $totalLabs ?></div><div class="lbl">💉 Labs</div></div>
        <div class="admin-stat"><div class="num"><?= $pendingCount ?></div><div class="lbl">📝 Pending</div></div>
        <div class="admin-stat"><div class="num"><?= $totalCompleted ?></div><div class="lbl">✅ Completed</div></div>
        <div class="admin-stat"><div class="num"><?= number_format($totalXp) ?></div><div class="lbl">⚡ XP Awarded</div></div>
    </div>

    <!-- Tabs -->
    <div class="admin-tabs">
        <button class="active" data-tab="overview">📊 Overview</button>
        <button data-tab="content">📦 Content</button>
        <button data-tab="writeups">📝 Writeups</button>
        <button data-tab="users">👥 Users</button>
    </div>

    <!-- ================= OVERVIEW TAB ================= -->
    <div class="tab-panel active" id="tab-overview">
        <div class="stats-grid">
            <?php foreach ($typeBreakdown as $tb): ?>
                <div class="stat-card">
                    <div class="stat-number"><?= $tb['cnt'] ?></div>
                    <div class="stat-label"><?= ucfirst($tb['type']) ?>s</div>
                </div>
            <?php endforeach; ?>
        </div>

        <h3>🕐 Recent Writeups</h3>
        <?php if (count($recentWriteups) > 0): ?>
            <?php foreach ($recentWriteups as $w): 
                $color = $w['status']=='approved' ? '#00e676' : ($w['status']=='rejected' ? '#ff6b6b' : '#ffb300');
            ?>
                <div style="border-bottom:1px solid rgba(255,255,255,0.05); padding:12px 0;">
                    <strong><?= htmlspecialchars($w['title']) ?></strong>
                    <span style="font-size:0.8rem; color:#888;">by <?= htmlspecialchars($w['username']) ?></span>
                    <span class="badge-status" style="background:rgba(255,255,255,0.05); color:<?= $color ?>; margin-left:10px;"><?= $w['status'] ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:#666;">No writeups yet.</p>
        <?php endif; ?>

        <h3 style="margin-top:25px;">🆕 Recently Registered Users</h3>
        <?php if (count($recentUsers) > 0): ?>
            <table class="admin-table">
                <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
                <tbody>
                    <?php foreach ($recentUsers as $u): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="<?= $u['role']=='admin' ? 'role-admin' : 'role-user' ?>"><?= $u['role'] ?></span></td>
                            <td><?= htmlspecialchars($u['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- ================= CONTENT TAB ================= -->
    <div class="tab-panel" id="tab-content">
        <div class="toolbar">
            <h2><?= $form_title ?></h2>
            <?php if ($editItem): ?>
                <a href="index.php" class="btn" style="background:rgba(255,255,255,0.05); box-shadow:none;">✖ Cancel Edit</a>
            <?php endif; ?>
        </div>

        <form method="POST" class="form-grid">
            <?= csrfField() ?>
            <?php if ($editItem): ?>
                <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
            <?php endif; ?>

            <div>
                <label>Type</label>
                <select name="type" required>
                    <option value="lesson" <?= ($type == 'lesson') ? 'selected' : '' ?>>📖 Lesson</option>
                    <option value="lab" <?= ($type == 'lab') ? 'selected' : '' ?>>💉 Lab</option>
                    <option value="tool" <?= ($type == 'tool') ? 'selected' : '' ?>>🛠️ Tool</option>
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
                <input type="text" name="title" value="<?= htmlspecialchars($title) ?>" required placeholder="e.g. SQL Injection Playground">
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
                <label>Slug (routes to the right page — e.g. 'xss-playground', 'nmap-basics')</label>
                <input type="text" name="slug" value="<?= htmlspecialchars($slug) ?>" placeholder="Optional stable identifier for routing">
            </div>
            <div class="full-width">
                <button type="submit" name="<?= $submit_name ?>">
                    <?= $editItem ? '💾 Update Content' : '➕ Add Content' ?>
                </button>
            </div>
        </form>

        <div class="search-box">
            <input type="text" id="contentSearch" placeholder="🔍 Search content by title, type, module..." onkeyup="filterContent()">
        </div>

        <h2 style="margin-top:10px;">📦 All Content (<?= count($allContent) ?>)</h2>
        <table class="admin-table" id="contentTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Module</th>
                    <th>Slug</th>
                    <th>Diff</th>
                    <th>XP</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($allContent as $item):
                    $icon = $typeIcons[$item['type']] ?? '📄';
                    $dc = $diffColors[$item['difficulty']] ?? '#8888aa';
                ?>
                    <tr>
                        <td>#<?= $item['id'] ?></td>
                        <td><span class="badge-status"><?= $icon ?> <?= $item['type'] ?></span></td>
                        <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                        <td><?= $item['module_group'] ? '<span class="module-tag">'.htmlspecialchars($item['module_group']).'</span>' : '—' ?></td>
                        <td style="color:#8888aa; font-size:0.8rem;"><?= $item['slug'] ? htmlspecialchars($item['slug']) : '—' ?></td>
                        <td><span class="badge-diff" style="background:<?= $dc ?>22; color:<?= $dc ?>;"><?= $item['difficulty'] ?></span></td>
                        <td><?= $item['points'] ?></td>
                        <td style="text-align:center; white-space:nowrap;" class="actions">
                            <a href="<?= csrfUrl('?edit='.$item['id']) ?>" class="edit">✏️</a>
                            <a href="<?= csrfUrl('?delete='.$item['id']) ?>" class="delete" onclick="return confirm('⚠️ Permanently delete this item and its flags?')">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($allContent) == 0): ?>
                    <tr><td colspan="8" style="text-align:center; color:#666; padding:30px;">No content yet. Add your first lesson above!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ================= WRITEUPS TAB ================= -->
    <div class="tab-panel" id="tab-writeups">
        <h2>📝 Writeup Moderation</h2>

        <?php if (count($pendingWriteups) > 0): ?>
            <h3 style="color:#fe00fe; margin-top:15px;">⏳ Pending Review (<?= count($pendingWriteups) ?>)</h3>
            <?php foreach ($pendingWriteups as $w): ?>
                <div class="writeup-card">
                    <strong>#<?= $w['id'] ?> · <?= htmlspecialchars($w['title']) ?></strong>
                    <div class="w-meta">✍️ by <?= htmlspecialchars($w['username']) ?> · <?= htmlspecialchars($w['created_at']) ?></div>
                    <div class="w-preview"><?= nl2br(htmlspecialchars(mb_substr($w['content'], 0, 300))) ?>...</div>
                    <div style="margin-top:12px;" class="actions">
                        <a href="<?= csrfUrl('?w_action=approve&wid='.$w['id']) ?>" class="approve">✅ Approve</a>
                        <a href="<?= csrfUrl('?w_action=reject&wid='.$w['id']) ?>" class="reject">❌ Reject</a>
                        <a href="<?= csrfUrl('?w_action=delete&wid='.$w['id']) ?>" class="delete" onclick="return confirm('Delete this writeup?')">🗑️ Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:#666;">No writeups pending review. 🎉</p>
        <?php endif; ?>

        <?php if (count($allWriteups) > 0): ?>
            <h3 style="margin-top:30px;">📚 All Writeups (<?= count($allWriteups) ?>)</h3>
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
                    <?php foreach ($allWriteups as $w): 
                        $color = $w['status']=='approved' ? '#00e676' : ($w['status']=='rejected' ? '#ff6b6b' : '#ffb300');
                    ?>
                        <tr>
                            <td>#<?= $w['id'] ?></td>
                            <td><strong><?= htmlspecialchars($w['title']) ?></strong></td>
                            <td><?= htmlspecialchars($w['username']) ?></td>
                            <td><span class="badge-status" style="background:<?= $color ?>22; color:<?= $color ?>;"><?= $w['status'] ?></span></td>
                            <td><?= htmlspecialchars($w['created_at']) ?></td>
                            <td class="actions">
                                <?php if ($w['status'] != 'approved'): ?>
                                    <a href="<?= csrfUrl('?w_action=approve&wid='.$w['id']) ?>" class="approve">✅</a>
                                <?php endif; ?>
                                <?php if ($w['status'] != 'rejected'): ?>
                                    <a href="<?= csrfUrl('?w_action=reject&wid='.$w['id']) ?>" class="reject">❌</a>
                                <?php endif; ?>
                                <a href="<?= csrfUrl('?w_action=delete&wid='.$w['id']) ?>" class="delete" onclick="return confirm('Delete this writeup?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- ================= USERS TAB ================= -->
    <div class="tab-panel" id="tab-users">
        <h2>👥 User Management (<?= $totalUsers ?>)</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>🔥 Streak</th>
                    <th>✅ Completed</th>
                    <th>Joined</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="<?= $u['role']=='admin' ? 'role-admin' : 'role-user' ?>"><?= $u['role'] ?></span></td>
                        <td>🔥 <?= $u['streak'] ?></td>
                        <td><?= $u['completed_count'] ?></td>
                        <td><?= htmlspecialchars($u['created_at']) ?></td>
                        <td style="text-align:center; white-space:nowrap;" class="actions">
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <?php if ($u['role'] == 'user'): ?>
                                    <a href="<?= csrfUrl('?user_action=promote&uid='.$u['id']) ?>" class="edit" title="Make admin">⬆️</a>
                                <?php else: ?>
                                    <a href="<?= csrfUrl('?user_action=demote&uid='.$u['id']) ?>" class="reject" title="Remove admin">⬇️</a>
                                <?php endif; ?>
                                <a href="<?= csrfUrl('?user_action=delete&uid='.$u['id']) ?>" class="delete" title="Delete user" onclick="return confirm('⚠️ Delete this user and all their data?')">🗑️</a>
                            <?php else: ?>
                                <span style="color:#666;">(you)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    // Tab switching
    document.querySelectorAll('.admin-tabs button').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.admin-tabs button').forEach(function(b) { b.classList.remove('active'); });
            document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        });
    });

    // Content search filter
    function filterContent() {
        var q = document.getElementById('contentSearch').value.toLowerCase();
        var rows = document.querySelectorAll('#contentTable tbody tr');
        rows.forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
        });
    }

    // Auto-select tab based on URL hash (e.g. #content)
    if (window.location.hash) {
        var tab = window.location.hash.replace('#', '');
        var found = document.querySelector('.admin-tabs button[data-tab="' + tab + '"]');
        if (found) found.click();
    }
</script>
</body>
</html>
