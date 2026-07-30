<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>🛡️ Bug Bounty Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero {
            text-align: center;
            padding: 40px 0 20px 0;
        }
        .hero h1 {
            font-size: 4.5rem;
            line-height: 1.1;
            background: linear-gradient(135deg, #00f2fe, #fe00fe, #00f2fe);
            background-size: 200% 200%;
            animation: gradientMove 4s ease infinite;
        }
        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .hero p {
            font-size: 1.3rem;
            color: #a0a0d0;
            max-width: 600px;
            margin: 20px auto;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        .feature-item {
            background: rgba(255,255,255,0.03);
            padding: 25px 15px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: 0.3s;
        }
        .feature-item:hover {
            border-color: #00f2fe;
            transform: scale(1.02);
        }
        .feature-item .icon { font-size: 2.5rem; display: block; margin-bottom: 10px; }
        .cta-buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
<div class="container">
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

    <div class="feature-grid">
        <div class="feature-item"><span class="icon">📚</span> Theory Lessons</div>
        <div class="feature-item"><span class="icon">💻</span> Live Vulnerable Labs</div>
        <div class="feature-item"><span class="icon">🧠</span> Quizzes & Challenges</div>
        <div class="feature-item"><span class="icon">🏅</span> Badges & Streaks</div>
        <div class="feature-item"><span class="icon">📝</span> Community Writeups</div>
        <div class="feature-item"><span class="icon">🛠️</span> Tool Simulators</div>
    </div>

    <div style="text-align:center; margin-top:30px; color:#666;">
        ⚠️ Educational use only. Contains intentionally vulnerable code.
    </div>
</div>
</body>
</html>