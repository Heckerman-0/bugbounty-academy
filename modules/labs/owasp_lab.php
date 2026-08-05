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
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<?php include '../../includes/nav.php'; ?>
<h1>🛡️ OWASP Top 10 Challenge Lab</h1>
<p>Identify which <strong>OWASP Top 10</strong> category each vulnerability scenario belongs to. Answer all 4 correctly, then submit the flag.</p>

<?php foreach ($scenarios as $num => $scen): ?>
    <div style="border:1px solid rgba(255,255,255,0.1); padding:15px; border-radius:10px; margin:15px 0;">
        <p><strong>Scenario <?= $num ?>:</strong> <?= htmlspecialchars($scen['desc']) ?></p>
        <form method="POST" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <?= csrfField() ?>
            <input type="hidden" name="scenario" value="<?= $num ?>">
            <select name="category" style="padding:8px; border-radius:6px; border:1px solid #444;">
                <option value="">Select category...</option>
                <option value="Injection">Injection</option>
                <option value="Broken Access Control">Broken Access Control</option>
                <option value="Security Misconfiguration">Security Misconfiguration</option>
                <option value="XSS">Cross-Site Scripting (XSS)</option>
            </select>
            <button type="submit" style="padding:8px 16px;">Check</button>
        </form>
        <?php if ($answered === $num): ?>
            <p style="color:green; margin-top:8px;">✅ Correct category: <strong><?= htmlspecialchars($correct_category) ?></strong></p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<div style="border:1px solid #ccc; padding:10px; margin-top:10px;"><?= $msg ?></div>

<form method="POST">
    <?= csrfField() ?>
    <h3>Completed all scenarios? Submit the flag:</h3>
    <input type="text" name="flag" placeholder="Enter flag">
    <button type="submit">Submit Flag</button>
</form>
<?php if ($flag_correct): ?><h2 style="color:green;">✅ Lab Passed!</h2><?php endif; ?>
<a href="<?= BASE_URL ?>modules/labs/index.php" style="display:inline-block; margin-top:15px;">Back to Labs</a>
</div></body></html>
