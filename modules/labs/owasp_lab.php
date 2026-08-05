<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 22;
$msg = "";
$flag_correct = false;

// The "correct category" for each scenario
$scenarios = [
    1 => ['desc' => 'A GET parameter inserts user input directly into a SQL query.', 'answer' => 'Injection'],
    2 => ['desc' => 'A user can access another user\'s private files by changing an ID number.', 'answer' => 'Broken Access Control'],
    3 => ['desc' => 'An error page leaks the full database connection string and stack trace.', 'answer' => 'Security Misconfiguration'],
    4 => ['desc' => 'A comment field stores raw HTML that executes in other users\' browsers.', 'answer' => 'XSS'],
];

$answered = null;
$correct_category = null;
if (isset($_GET['scenario'])) {
    $answered = (int)$_GET['scenario'];
    if (isset($scenarios[$answered])) {
        $correct_category = $scenarios[$answered]['answer'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['category']) && isset($_POST['scenario'])) {
        $scen_id = (int)$_POST['scenario'];
        $user_ans = trim($_POST['category']);
        if (isset($scenarios[$scen_id]) && strcasecmp($scenarios[$scen_id]['answer'], $user_ans) == 0) {
            $msg = "<b>Correct!</b> That scenario belongs to <strong>" . htmlspecialchars($scenarios[$scen_id]['answer']) . "</strong>.";
            $answered = $scen_id;
            $correct_category = $scenarios[$scen_id]['answer'];
        } else {
            $msg = "❌ Not quite. Review the OWASP Top 10 lesson and try again.";
        }
    } elseif (isset($_POST['flag'])) {
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
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>CyberCert | Security Training</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>includes/lab.css">
    <style>
        :root { --accent: #16a34a; --accent2: #166534; }
        .quiz-card { border:1px solid var(--border); border-radius:14px; padding:20px; margin:14px 0; background:#fafafa; }
        .quiz-card h4 { margin:0 0 12px; }
        .quiz-card select { background:#fff; }
        .quiz-card .correct { color:var(--ok); font-weight:700; margin-top:10px; }
        .progress { display:flex; gap:8px; margin-bottom:16px; }
        .progress .step { flex:1; height:8px; border-radius:20px; background:#e5e7eb; }
        .progress .step.on { background: linear-gradient(90deg,#16a34a,#166534); }
    </style>
</head>
<body>
<div class="lab-banner">
    <div><span class="brand">🛡️ BBA</span> Lab — OWASP Top 10</div>
    <div><a class="link" href="<?= BASE_URL ?>modules/labs/index.php">⬅ Back to Labs</a></div>
</div>

<header class="site-header">
    <div class="inner">
        <div class="logo">🎓 CyberCert <span>Security Training</span></div>
        <nav>
            <a href="#">Courses</a>
            <a href="#">Quiz</a>
            <a href="#">Certificate</a>
        </nav>
    </div>
</header>

<main class="site-main">
    <div class="site-card">
        <h1>OWASP Top 10 Assessment</h1>
        <p class="sub">Identify which security category each vulnerability scenario belongs to.</p>

        <div class="progress">
            <?php for ($i=1; $i<=4; $i++): ?>
                <div class="step <?= ($answered !== null && $i <= $answered) ? 'on' : '' ?>"></div>
            <?php endfor; ?>
        </div>

        <?php foreach ($scenarios as $num => $scen): ?>
            <div class="quiz-card">
                <h4>Scenario <?= $num ?></h4>
                <p style="color:var(--muted); margin:0 0 12px;"><?= htmlspecialchars($scen['desc']) ?></p>
                <form method="POST" class="site-form">
                    <?= csrfField() ?>
                    <input type="hidden" name="scenario" value="<?= $num ?>">
                    <select name="category">
                        <option value="">Select category...</option>
                        <option value="Injection">Injection</option>
                        <option value="Broken Access Control">Broken Access Control</option>
                        <option value="Security Misconfiguration">Security Misconfiguration</option>
                        <option value="XSS">Cross-Site Scripting (XSS)</option>
                    </select>
                    <button type="submit" class="btn-site">Check Answer</button>
                </form>
                <?php if ($answered === $num): ?>
                    <div class="correct">✅ Correct category: <strong><?= htmlspecialchars($correct_category) ?></strong></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <?php if ($msg): ?><div class="alert <?= (stripos($msg,'Correct')!==false || stripos($msg,'CORRECT')!==false) ? 'ok' : 'warn' ?>" style="margin-top:16px;"><?= $msg ?></div><?php endif; ?>
    </div>

    <div class="flag-box">
        <h3>🏴 Completed all scenarios? Submit the flag:</h3>
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
