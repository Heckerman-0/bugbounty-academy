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
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<h1>IDOR Lab</h1>
<p>You are logged in as user #<?= (int)$_SESSION['user_id'] ?>. Try to access another user's profile by changing the <code>user_id</code> parameter.</p>
<form method="GET">
    <input type="number" name="user_id" placeholder="user_id" value="<?= (int)$_SESSION['user_id'] ?>">
    <button type="submit">View Profile</button>
</form>
<?php if ($profile): ?>
<div style="border:1px solid #ccc; padding:10px; margin-top:10px;">
    <p><strong>Username:</strong> <?= htmlspecialchars($profile['username']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($profile['email']) ?></p>
    <?php if (!empty($profile['sensitive'])): ?>
        <p><strong>Sensitive:</strong> <code><?= htmlspecialchars($profile['sensitive']) ?></code></p>
    <?php endif; ?>
</div>
<?php endif; ?>
<div style="border:1px solid #ccc; padding:10px; margin-top:10px;"><?= $msg ?></div>

<form method="POST">
    <?= csrfField() ?>
    <h3>Found the flag? Submit it:</h3>
    <input type="text" name="flag" placeholder="Enter flag">
    <button type="submit">Submit Flag</button>
</form>
<?php if ($flag_correct): ?><h2 style="color:green;">✅ Lab Passed!</h2><?php endif; ?>
<a href="<?= BASE_URL ?>modules/labs/index.php">Back to Labs</a>
</div></body></html>
