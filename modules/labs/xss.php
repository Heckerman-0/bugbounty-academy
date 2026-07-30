<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = $_GET['id'];
$output = "";
$flag_correct = false;

if (isset($_GET['comment'])) {
    $output = "You said: " . $_GET['comment'];
    if (strpos($_GET['comment'], "<script>alert") !== false) {
        $flag_correct = true;
        markComplete($_SESSION['user_id'], $id);
    }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<h1>XSS Lab</h1>
<p>Inject a JavaScript alert into the comment box.</p>
<form method="GET">
    <input type="text" name="comment" placeholder="Type something...">
    <button type="submit">Post</button>
</form>
<div><?= $output ?></div>
<?php if ($flag_correct): ?><h2 style="color:green;">✅ XSS Executed! Lab Passed.</h2><?php endif; ?>
<a href="<?= BASE_URL ?>dashboard.php">Back</a>
</div></body></html>