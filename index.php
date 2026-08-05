<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>🛡️ Bug Bounty Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <?php include 'includes/nav.php'; ?>
    
    <div class="hero">
        <h1>🛡️ Bug Bounty<br>Academy</h1>
        <p>Learn web security. Hack labs. Earn badges. Become a hunter.</p>
        
        <div class="cta-buttons">
            <?php if (isLoggedIn()): ?>
                <a href="dashboard.php" class="btn">🚀 Launch Dashboard</a>
                <a href="logout.php" style="background:rgba(255,255,255,0.05); padding:12px 32px; border-radius:50px;">Logout</a>
            <?php else: ?>
                <a href="register.php" class="btn">⚡ Get Started Free</a>
                <a href="login.php" style="background:rgba(255,255,255,0.05); padding:12px 32px; border-radius:50px;">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 🔥 NOW ALL CARDS ARE CLICKABLE LINKS -->
    <div class="feature-grid">
        <a href="dashboard.php" style="text-decoration:none; border-bottom:none;">
            <div class="feature-item"><span class="icon">📚</span> Theory Lessons</div>
        </a>
        <a href="dashboard.php" style="text-decoration:none; border-bottom:none;">
            <div class="feature-item"><span class="icon">💻</span> Live Vulnerable Labs</div>
        </a>
        <a href="dashboard.php" style="text-decoration:none; border-bottom:none;">
            <div class="feature-item"><span class="icon">🧠</span> Quizzes & Challenges</div>
        </a>
        <a href="dashboard.php" style="text-decoration:none; border-bottom:none;">
            <div class="feature-item"><span class="icon">🏅</span> Badges & Streaks</div>
        </a>
        <a href="dashboard.php" style="text-decoration:none; border-bottom:none;">
            <div class="feature-item"><span class="icon">📝</span> Community Writeups</div>
        </a>
        <a href="dashboard.php" style="text-decoration:none; border-bottom:none;">
            <div class="feature-item"><span class="icon">🛠️</span> Tool Simulators</div>
        </a>
    </div>

    <div style="text-align:center; margin-top:30px; color:#666;">
        ⚠️ Educational use only. Contains intentionally vulnerable code.
    </div>
</div>
</body>
</html>
