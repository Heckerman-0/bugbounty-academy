<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 18;
$msg = "";
$flag_correct = false;
$profile = null;

// Intentionally vulnerable IDOR: fetches any user's profile by ID without auth check
if (isset($_GET['user_id'])) {
    $uid = (int)$_GET['user_id'];
    $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE id=?");
    $stmt->execute([$uid]);
    $profile = $stmt->fetch();

    // Simulated "admin" target user with a sensitive field
    if ($uid === 1) {
        $msg = "<b>Exploit Success!</b> You accessed admin's data. The admin's password hash (old md5) is shown below.";
        $profile['sensitive'] = 'admin_hash_5f4dcc3b5aa765d61d8327deb882cf99';
    } else {
        $msg = "You accessed user profile #$uid.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['flag'])) {
    if (!verifyCsrfToken()) {
        $msg = "Invalid form submission. Please try again.";
    } else {
        $stmt = $pdo->prepare("SELECT flag_text FROM lab_flags WHERE content_id=?");
        $stmt->execute([$id]);
        $flagData = $stmt->fetch();
        if ($flagData && hash_equals($flagData['flag_text'], $_POST['flag'])) {
            markComplete($_SESSION['user_id'], $id);
            $flag_correct = true;
            $msg = "🎉 CORRECT FLAG! Lab Completed.";
        } else {
            $msg = "❌ Wrong flag. Try harder!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PeopleHub | User Profiles</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>includes/lab.css">
    <style>
        :root { --accent: #db2777; --accent2: #831843; }
        .profile-nav { display:flex; gap:8px; flex-wrap:wrap; }
        .profile-nav input { max-width: 160px; }
    </style>
</head>
<body>
<div class="lab-banner">
    <div><span class="brand">🛡️ BBA</span> Lab — IDOR</div>
    <div><a class="link" href="<?= BASE_URL ?>modules/labs/index.php">⬅ Back to Labs</a></div>
</div>

<header class="site-header">
    <div class="inner">
        <div class="logo">👥 PeopleHub <span>Profiles</span></div>
        <nav>
            <a href="#">Home</a>
            <a href="#">Explore</a>
            <a href="#">Messages</a>
        </nav>
    </div>
</header>

<main class="site-main">
    <div class="site-card">
        <h1>View Profile</h1>
        <p class="sub">You are logged in as user #<?= (int)$_SESSION['user_id'] ?>. Look up any member by their ID.</p>

        <form method="GET" class="site-form profile-nav">
            <input type="number" name="user_id" placeholder="User ID" value="<?= (int)$_SESSION['user_id'] ?>">
            <button type="submit" class="btn-site">View Profile</button>
        </form>

        <?php if ($profile): ?>
            <div class="profile-card">
                <div class="avatar"><?= strtoupper(substr(htmlspecialchars($profile['username']), 0, 1)) ?></div>
                <div class="info">
                    <h3 style="margin:0 0 4px;"><?= htmlspecialchars($profile['username']) ?></h3>
                    <p><strong>Email:</strong> <?= htmlspecialchars($profile['email'] ?? 'private') ?></p>
                    <?php if (!empty($profile['sensitive'])): ?>
                        <p><strong>Sensitive:</strong> <code><?= htmlspecialchars($profile['sensitive']) ?></code></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($msg): ?>
            <div class="alert <?= (stripos($msg,'Exploit')!==false) ? 'ok' : 'info' ?>"><?= $msg ?></div>
        <?php endif; ?>
    </div>

    <div class="flag-box">
        <h3>🏴 Found the flag? Submit it:</h3>
        <form method="POST" class="site-form">
            <?= csrfField() ?>
            <input type="text" name="flag" placeholder="Enter flag">
            <button type="submit" class="btn-site">Submit Flag</button>
        </form>
        <?php if ($flag_correct): ?><div class="flag-done">✅ Lab Passed!</div><?php endif; ?>
    </div>
</main>
</body>
</html>
