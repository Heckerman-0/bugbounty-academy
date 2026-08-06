<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("Lesson not found");
$stmt = $pdo->prepare("SELECT * FROM content WHERE id=? AND type='lesson'");
$stmt->execute([$id]);
$lesson = $stmt->fetch();
if (!$lesson) die("Lesson not found");

markComplete($_SESSION['user_id'], $id);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($lesson['title']) ?> | Bug Bounty Academy</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .progress-bar-bg {
            width: 100%;
            height: 6px;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            margin: 20px 0;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            width: 100%;
            background: linear-gradient(90deg, #00f2fe, #fe00fe);
            border-radius: 10px;
            animation: glowPulse 2s infinite alternate;
        }
        @keyframes glowPulse {
            0% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        .next-btn {
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div style="font-size:0.9rem; color:#8888aa; margin-bottom:10px;">
        <a href="<?= BASE_URL ?>dashboard.php" style="border-bottom:none;">🏠 Dashboard</a> 
        <span style="color:#555;">›</span> 
        <span style="color:#00f2fe;"><?= htmlspecialchars($lesson['title']) ?></span>
    </div>

    <div class="progress-bar-bg">
        <div class="progress-bar-fill"></div>
    </div>

    <div class="lesson-header">
        <span class="badge"><?= htmlspecialchars($lesson['difficulty']) ?></span>
        <span class="duration">⚡ +<?= $lesson['points'] ?> XP</span>
    </div>

    <h1><?= htmlspecialchars($lesson['title']) ?></h1>
    <div style="margin-top: 10px;">
        <?= $lesson['body_html'] ?>
    </div>

<?php
        // Find the quiz for this lesson's module (so the "Take Quiz" button is correct)
        $linkedQuiz = null;
        $moduleOfLesson = $lesson['module_group'] ?? null;
        if ($moduleOfLesson) {
            $linkedQuiz = getModuleQuiz($moduleOfLesson);
        }
    ?>
    <div class="nav-links">
        <a href="<?= BASE_URL ?>dashboard.php" class="btn" style="background:rgba(255,255,255,0.05); box-shadow:none;">⬅ Back to Dashboard</a>
        <?php if ($linkedQuiz): ?>
            <a href="<?= BASE_URL ?>modules/quizzes/take.php?quiz_id=<?= (int)$linkedQuiz['quiz_id'] ?>" class="btn next-btn">📝 Take Quiz ➡</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>