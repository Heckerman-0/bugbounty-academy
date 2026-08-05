<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');

$quiz_id = (int)($_GET['quiz_id'] ?? 0);
if ($quiz_id <= 0) die("Quiz not found");

// Load the quiz
$quizStmt = $pdo->prepare("SELECT * FROM quizzes WHERE id=?");
$quizStmt->execute([$quiz_id]);
$quiz = $quizStmt->fetch();
if (!$quiz) die("Quiz not found");

// Determine the module this quiz belongs to (via the linked lesson/lab content)
$module_query = $pdo->prepare("SELECT module_group FROM content WHERE id=?");
$module_query->execute([$quiz['content_id']]);
$module_row = $module_query->fetch();
$module_group = $module_row['module_group'] ?? null;

// Gating: quiz unlocks only when the module's lesson AND lab are both complete
$unlocked = false;
$lesson_done = false;
$lab_done = false;
if ($module_group) {
    $items = $pdo->prepare("SELECT id, type, title FROM content WHERE module_group=? ORDER BY FIELD(type,'lesson','tool','lab')");
    $items->execute([$module_group]);
    $module_items = $items->fetchAll();
    $progress = getProgress($_SESSION['user_id']);
    foreach ($module_items as $mi) {
        $isDone = isset($progress[$mi['id']]) && $progress[$mi['id']] == 'completed';
        if ($mi['type'] == 'lesson' && $isDone) $lesson_done = true;
        if ($mi['type'] == 'lab' && $isDone) $lab_done = true;
    }
    // A module is "complete" for the quiz when there is at least ONE lesson+lab combo done
    if ($lesson_done && $lab_done) $unlocked = true;
}

// Load the questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id=?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

$score = 0;
$total = count($questions);
$graded = false;
$perfect = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['answers'])) {
    $graded = true;
    foreach ($questions as $q) {
        $user_ans = $_POST['answers'][$q['id']] ?? '';
        if ($user_ans == $q['correct_answer']) $score++;
    }
    if ($score == $total && $total > 0) {
        $perfect = true;
        if ($quiz['content_id']) markComplete($_SESSION['user_id'], $quiz['content_id']);
        awardBadge($_SESSION['user_id'], 3);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($quiz['title']) ?> | Bug Bounty Academy</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .quiz-wrap { max-width: 760px; margin: 0 auto; }
        .quiz-header { text-align:center; margin-bottom: 25px; }
        .quiz-header h1 { font-size: 1.8rem; margin-bottom: 6px; }
        .quiz-meta { color: #8888aa; font-size: 0.9rem; }
        .lock-card {
            text-align:center; padding: 40px 25px; border-radius: 16px;
            background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);
        }
        .lock-card .lock-icon { font-size: 3rem; margin-bottom: 10px; }
        .lock-card h2 { margin: 0 0 8px 0; }
        .lock-card p { color: #8888aa; }
        .lock-steps { display:flex; gap:15px; justify-content:center; flex-wrap:wrap; margin: 20px 0; }
        .lock-step {
            background: rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08);
            padding: 14px 20px; border-radius: 12px; min-width: 130px;
        }
        .lock-step .step-label { font-size:0.75rem; color:#8888aa; text-transform:uppercase; letter-spacing:1px; }
        .lock-step .step-state { font-weight:700; margin-top:4px; }
        .lock-step.done { border-color: rgba(0,230,118,0.4); }
        .lock-step.pending { border-color: rgba(255,255,255,0.1); }
        .quiz-form { display:flex; flex-direction:column; gap: 20px; }
        .question-card {
            background: rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.07);
            border-radius: 14px; padding: 20px;
        }
        .question-card .q-num { font-size:0.75rem; color:#00f2fe; letter-spacing:1px; text-transform:uppercase; }
        .question-card .q-text { font-weight:600; margin: 8px 0 14px 0; font-size:1.05rem; }
        .option { display:flex; align-items:center; gap:10px; padding:10px 14px; margin:6px 0; border-radius:10px; border:1px solid rgba(255,255,255,0.06); cursor:pointer; transition:0.2s; }
        .option:hover { background: rgba(0,242,254,0.05); border-color: rgba(0,242,254,0.3); }
        .option input { accent-color: #00f2fe; }
        .option.correct { border-color: rgba(0,230,118,0.5); background: rgba(0,230,118,0.08); }
        .option.incorrect { border-color: rgba(255,82,82,0.5); background: rgba(255,82,82,0.08); }
        .submit-row { text-align:center; margin-top: 10px; }
        .result-card {
            text-align:center; padding: 30px; border-radius: 16px; margin-top: 25px;
            background: rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.08);
        }
        .result-card .big-score { font-size: 3rem; font-weight: 800; line-height:1.1; }
        .result-card .perfect { color: #00e676; }
        .result-card .good { color: #00f2fe; }
        .result-card .low { color: #ff5252; }
        .btn-secondary { background:rgba(255,255,255,0.05); box-shadow:none; }
    </style>
</head>
<body>
<div class="container">
    <?php include '../../includes/nav.php'; ?>

    <div class="quiz-wrap">
        <div class="quiz-header">
            <h1>📝 <?= htmlspecialchars($quiz['title']) ?></h1>
            <div class="quiz-meta"><?= $total ?> question<?= $total == 1 ? '' : 's' ?> · Pass with 100%</div>
        </div>

        <?php if (!$unlocked): ?>
            <!-- LOCKED STATE -->
            <div class="lock-card">
                <div class="lock-icon">🔒</div>
                <h2>Quiz Locked</h2>
                <p>Complete the module's <strong>lesson</strong> and <strong>lab</strong> first to unlock this quiz.</p>
                <div class="lock-steps">
                    <div class="lock-step <?= $lesson_done ? 'done' : 'pending' ?>">
                        <div class="step-label">📖 Lesson</div>
                        <div class="step-state"><?= $lesson_done ? '✅ Done' : '⬜ Not done' ?></div>
                    </div>
                    <div class="lock-step <?= $lab_done ? 'done' : 'pending' ?>">
                        <div class="step-label">💉 Lab</div>
                        <div class="step-state"><?= $lab_done ? '✅ Done' : '⬜ Not done' ?></div>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>dashboard.php" class="btn">⬅ Back to Dashboard</a>
            </div>
        <?php elseif (count($questions) == 0): ?>
            <div class="lock-card">
                <div class="lock-icon">📭</div>
                <h2>No Questions Yet</h2>
                <p>This quiz doesn't have any questions configured yet.</p>
                <a href="<?= BASE_URL ?>dashboard.php" class="btn">⬅ Back to Dashboard</a>
            </div>
        <?php elseif ($graded): ?>
            <!-- RESULT STATE -->
            <?php $pct = $total > 0 ? round(($score / $total) * 100) : 0; ?>
            <div class="result-card">
                <div class="big-score <?= $perfect ? 'perfect' : ($pct >= 70 ? 'good' : 'low') ?>"><?= $score ?>/<?= $total ?></div>
                <div style="color:#8888aa; margin-top:6px;"><?= $pct ?>% correct</div>
                <?php if ($perfect): ?>
                    <p style="color:#00e676; font-weight:600; margin-top:12px;">🎉 Perfect score! Module marked complete & Quiz Whiz badge earned!</p>
                <?php elseif ($pct >= 70): ?>
                    <p style="color:#00f2fe; margin-top:12px;">👍 Good job! You need 100% to complete the module. Try again!</p>
                <?php else: ?>
                    <p style="color:#ff5252; margin-top:12px;">😅 Review the lesson and try again. You need 100%!</p>
                <?php endif; ?>
                <div style="margin-top:20px; display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                    <a href="<?= BASE_URL ?>modules/quizzes/take.php?quiz_id=<?= $quiz_id ?>" class="btn btn-secondary">🔄 Retry Quiz</a>
                    <a href="<?= BASE_URL ?>dashboard.php" class="btn">⬅ Back to Dashboard</a>
                </div>
            </div>
        <?php else: ?>
            <!-- QUESTIONS STATE -->
            <form method="POST" class="quiz-form">
                <?php foreach ($questions as $i => $q): ?>
                    <div class="question-card">
                        <div class="q-num">Question <?= $i + 1 ?> of <?= $total ?></div>
                        <div class="q-text"><?= htmlspecialchars($q['question']) ?></div>
                        <?php 
                            $options = ['a' => $q['option_a'], 'b' => $q['option_b'], 'c' => $q['option_c'], 'd' => $q['option_d']];
                            foreach ($options as $key => $val):
                        ?>
                            <label class="option">
                                <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $key ?>" required>
                                <span><strong><?= strtoupper($key) ?>.</strong> <?= htmlspecialchars($val) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                <div class="submit-row">
                    <button type="submit" class="btn" style="padding:14px 40px; font-size:1rem;">✅ Submit Quiz</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
