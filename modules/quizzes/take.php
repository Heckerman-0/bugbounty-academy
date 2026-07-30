<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$quiz_id = $_GET['quiz_id'];
$score = 0;
$total = 0;
$graded = false;

$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id=?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $graded = true;
    $total = count($questions);
    foreach ($questions as $q) {
        $user_ans = $_POST['q_'.$q['id']] ?? '';
        if ($user_ans == $q['correct_answer']) $score++;
    }
    if ($score == $total) {
        $qchk = $pdo->prepare("SELECT content_id FROM quizzes WHERE id=?");
        $qchk->execute([$quiz_id]);
        $link = $qchk->fetch();
        if ($link) markComplete($_SESSION['user_id'], $link['content_id']);
        awardBadge($_SESSION['user_id'], 3);
    }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<h1>Quiz</h1>
<form method="POST">
<?php foreach($questions as $q): ?>
    <fieldset>
        <legend><?= htmlspecialchars($q['question']) ?></legend>
        <label><input type="radio" name="q_<?= $q['id'] ?>" value="a"> A. <?= $q['option_a'] ?></label><br>
        <label><input type="radio" name="q_<?= $q['id'] ?>" value="b"> B. <?= $q['option_b'] ?></label><br>
        <label><input type="radio" name="q_<?= $q['id'] ?>" value="c"> C. <?= $q['option_c'] ?></label><br>
        <label><input type="radio" name="q_<?= $q['id'] ?>" value="d"> D. <?= $q['option_d'] ?></label><br>
    </fieldset>
<?php endforeach; ?>
<button type="submit">Submit</button>
</form>
<?php if ($graded): ?>
    <h2>Your Score: <?= $score ?>/<?= $total ?></h2>
    <?php if ($score == $total): ?><p style="color:green;">🎉 Perfect! Quiz Whiz Badge Earned!</p><?php endif; ?>
<?php endif; ?>
<a href="<?= BASE_URL ?>dashboard.php">Back</a>
</div></body></html>