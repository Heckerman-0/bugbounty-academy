<?php 
require_once 'includes/auth.php';
if (!isLoggedIn()) redirect('login.php');

$msg = '';
$error = '';

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    if (!verifyCsrfToken()) {
        $error = '❌ Invalid security token. Please try again.';
    } else {
        $old = $_POST['old_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        if ($new !== $confirm) {
            $error = '❌ New passwords do not match.';
        } elseif (strlen($new) < 6) {
            $error = '❌ Password must be at least 6 characters.';
        } else {
            $result = changePassword($_SESSION['user_id'], $old, $new);
            if ($result === true) {
                $msg = '✅ Password updated successfully!';
            } else {
                $error = '❌ ' . $result;
            }
        }
    }
}

// Handle Email Change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_email'])) {
    if (!verifyCsrfToken()) {
        $error = '❌ Invalid security token. Please try again.';
    } else {
        $new_email = $_POST['new_email'];
        if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $result = changeEmail($_SESSION['user_id'], $new_email);
            if ($result === true) {
                $msg = '✅ Email updated successfully!';
                $_SESSION['email'] = $new_email; // Update session
            } else {
                $error = '❌ ' . $result;
            }
        } else {
            $error = '❌ Invalid email address.';
        }
    }
}

// Get current user data
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Account | Bug Bounty Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <?php include 'includes/nav.php'; ?>
    
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:20px;">
        <h1 style="font-size:2.5rem;">👤 Account Settings</h1>
        <a href="dashboard.php" style="background:rgba(255,255,255,0.05); padding:8px 20px; border-radius:50px; font-size:0.9rem; border:1px solid rgba(255,255,255,0.1);">⬅ Back</a>
    </div>

    <?php if ($msg): ?><div style="background:rgba(0,242,254,0.1); border-left:4px solid #00f2fe; padding:15px; border-radius:8px; margin-bottom:20px;"><?= $msg ?></div><?php endif; ?>
    <?php if ($error): ?><div style="background:rgba(254,0,0,0.1); border-left:4px solid #fe0000; padding:15px; border-radius:8px; margin-bottom:20px; color:#ff6b6b;"><?= $error ?></div><?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px;">
        <!-- Change Email -->
        <div style="background:rgba(255,255,255,0.02); padding:25px; border-radius:16px; border:1px solid rgba(255,255,255,0.05);">
            <h3 style="margin-top:0;">📧 Change Email</h3>
            <p style="color:#888; font-size:0.9rem;">Current: <strong style="color:#e0e0ff;"><?= htmlspecialchars($user['email']) ?></strong></p>
<form method="POST">
                <?= csrfField() ?>
                <input type="email" name="new_email" placeholder="New Email Address" required>
                <button type="submit" name="change_email" style="width:100%;">Update Email</button>
            </form>
        </div>

        <!-- Change Password -->
        <div style="background:rgba(255,255,255,0.02); padding:25px; border-radius:16px; border:1px solid rgba(255,255,255,0.05);">
            <h3 style="margin-top:0;">🔒 Change Password</h3>
<form method="POST">
                <?= csrfField() ?>
                <input type="password" name="old_password" placeholder="Current Password" required>
                <input type="password" name="new_password" placeholder="New Password (min 6 chars)" required>
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                <button type="submit" name="change_password" style="width:100%;">Update Password</button>
            </form>
        </div>
    </div>

    <div style="margin-top:30px; padding-top:20px; border-top:1px solid rgba(255,255,255,0.05); text-align:center; color:#666; font-size:0.9rem;">
        ⚠️ Keep your credentials safe. This is a training environment.
    </div>
</div>
</body>
</html>