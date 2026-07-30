<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Bug Bounty Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>🛡️ Bug Bounty Academy</h1>
        <p>Learn web security, practice labs, and earn badges.</p>
        <?php if (isLoggedIn()): ?>
            <a href="dashboard.php">Go to Dashboard</a> | <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a> | <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</body>
</html>