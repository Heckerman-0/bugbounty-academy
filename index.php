<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>🛡️ Bug Bounty Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
.hero h1 {
            font-size: 4.2rem;
            /* Solid neon fallback so the heading is ALWAYS visible,
               even if background-clip: text is not supported. */
            color: #00f2fe;
            background: none;
            -webkit-text-fill-color: inherit;
        }
        @supports (-webkit-background-clip: text) or (background-clip: text) {
            .hero h1 {
                background: linear-gradient(135deg, #00f2fe, #fe00fe, #00f2fe);
                background-size: 200% 200%;
                -webkit-background-clip: text;
                background-clip: text;
                -webkit-text-fill-color: transparent;
                animation: gradientMove 4s ease infinite;
            }
        }
        .hero .tagline {
            font-size: 1.25rem;
            color: #a0a0d0;
            max-width: 640px;
            margin: 18px auto 0;
        }
        .hero .badges-line {
            margin-top: 16px;
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .hero .badges-line span {
            background: rgba(0,242,254,0.08);
            border: 1px solid rgba(0,242,254,0.2);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            color: #c8c8f0;
        }

        /* Feature cards */
        .feature-grid { margin-top: 50px; }
        .feature-item {
            cursor: pointer;
            padding: 28px 20px;
            text-align: center;
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 18px;
            transition: all 0.3s ease;
        }
        .feature-item:hover {
            transform: translateY(-6px) scale(1.02);
            border-color: #00f2fe;
            box-shadow: 0 12px 40px rgba(0,242,254,0.15);
        }
        .feature-item .icon { font-size: 2.6rem; display: block; margin-bottom: 12px; }
        .feature-item .ftitle { font-size: 1rem; font-weight: 700; color: #e0e0ff; }
        .feature-item .fsub { font-size: 0.8rem; color: #8888aa; margin-top: 6px; }

        /* Section banners */
        .hero-cta-note {
            margin-top: 12px;
            font-size: 0.85rem;
            color: #666;
        }

        /* Stats strip on landing */
        .landing-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin-top: 45px;
        }
        .landing-stat {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(0,242,254,0.1);
            border-radius: 16px;
            padding: 22px 12px;
            text-align: center;
        }
        .landing-stat .num {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #00f2fe, #fe00fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .landing-stat .lbl { color: #8888aa; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }

        @media (max-width: 700px) {
            .hero h1 { font-size: 2.8rem; }
        }
    </style>
</head>
<body>
<div class="container">
    <?php include 'includes/nav.php'; ?>

    <div class="hero">
        <h1>🛡️ Bug Bounty<br>Academy</h1>
        <p class="tagline">Learn web security. Hack labs. Earn badges. Become a hunter.</p>
        <div class="badges-line">
            <span>📚 11+ Modules</span>
            <span>💉 12 Vulnerable Labs</span>
            <span>🧠 Quizzes</span>
            <span>🏅 Badges & Streaks</span>
        </div>

        <div class="cta-buttons">
            <?php if (isLoggedIn()): ?>
                <a href="dashboard.php" class="btn">🚀 Launch Dashboard</a>
                <a href="modules/labs/index.php" class="btn" style="background:rgba(255,255,255,0.05); box-shadow:none;">💻 Explore Labs</a>
                <a href="logout.php" style="background:rgba(255,255,255,0.05); padding:12px 32px; border-radius:50px;">🚪 Logout</a>
            <?php else: ?>
                <a href="register.php" class="btn">⚡ Get Started Free</a>
                <a href="login.php" style="background:rgba(255,255,255,0.05); padding:12px 32px; border-radius:50px;">🔐 Login</a>
            <?php endif; ?>
        </div>
        <p class="hero-cta-note">
            <?= isLoggedIn() ? 'Pick up where you left off in your dashboard.' : 'Create a free account to start earning XP and badges.' ?>
        </p>
    </div>

    <!-- Feature cards link to the right place -->
    <div class="feature-grid">
        <?php
            $features = [
                ['icon' => '📚', 'title' => 'Theory Lessons', 'sub' => 'Structured security modules', 'link' => isLoggedIn() ? 'dashboard.php' : 'register.php'],
                ['icon' => '💻', 'title' => 'Live Vulnerable Labs', 'sub' => 'Attack buggy targets', 'link' => isLoggedIn() ? 'modules/labs/index.php' : 'register.php'],
                ['icon' => '🧠', 'title' => 'Quizzes & Challenges', 'sub' => 'Test your knowledge', 'link' => isLoggedIn() ? 'dashboard.php' : 'register.php'],
                ['icon' => '🏅', 'title' => 'Badges & Streaks', 'sub' => 'Track your progress', 'link' => isLoggedIn() ? 'profile.php' : 'register.php'],
                ['icon' => '📝', 'title' => 'Community Writeups', 'sub' => 'Share your findings', 'link' => isLoggedIn() ? 'modules/writeups/index.php' : 'register.php'],
                ['icon' => '🛠️', 'title' => 'Tool Simulators', 'sub' => 'Nmap & more', 'link' => isLoggedIn() ? 'modules/tools/nmap.php' : 'register.php'],
            ];
            foreach ($features as $f):
        ?>
        <a href="<?= BASE_URL . $f['link'] ?>" style="text-decoration:none; border-bottom:none;">
            <div class="feature-item">
                <span class="icon"><?= $f['icon'] ?></span>
                <div class="ftitle"><?= $f['title'] ?></div>
                <div class="fsub"><?= $f['sub'] ?></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Landing stats -->
    <div class="landing-stats">
        <?php
            $totUsers = 0; $totLabs = 0; $totLessons = 0; $totWriteups = 0;
            try {
                $totUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                $totLabs = (int)$pdo->query("SELECT COUNT(*) FROM content WHERE type='lab'")->fetchColumn();
                $totLessons = (int)$pdo->query("SELECT COUNT(*) FROM content WHERE type='lesson'")->fetchColumn();
                $totWriteups = (int)$pdo->query("SELECT COUNT(*) FROM writeups WHERE status='approved'")->fetchColumn();
            } catch (Exception $e) {}
        ?>
        <div class="landing-stat"><div class="num"><?= $totUsers ?></div><div class="lbl">👥 Students</div></div>
        <div class="landing-stat"><div class="num"><?= $totLabs + $totLessons ?></div><div class="lbl">📚 Modules</div></div>
        <div class="landing-stat"><div class="num"><?= $totLabs ?></div><div class="lbl">💻 Labs</div></div>
        <div class="landing-stat"><div class="num"><?= $totWriteups ?></div><div class="lbl">📝 Writeups</div></div>
    </div>

    <div style="text-align:center; margin-top:35px; color:#666; font-size:0.85rem;">
        ⚠️ Educational use only. Contains intentionally vulnerable code.
    </div>
</div>
</body>
</html>
