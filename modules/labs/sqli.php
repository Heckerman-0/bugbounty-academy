<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = $_GET['id'];
$msg = "";
$flag_correct = false;

if (isset($_GET['search'])) {
    $input = $_GET['search'];
    if (strpos($input, "' OR '1'='1") !== false) {
        $msg = "<b>Exploit Success!</b> You dumped all users: <br> Admin | password123 <br> John | qwerty";
    } elseif (strpos($input, "admin") !== false) {
        $msg = "Found user: Admin (password hash: ...)";
    } else {
        $msg = "No users found.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['flag'])) {
    $stmt = $pdo->prepare("SELECT flag_text FROM lab_flags WHERE content_id=?");
    $stmt->execute([$id]);
    $flagData = $stmt->fetch();
    if ($flagData && $_POST['flag'] === $flagData['flag_text']) {
        markComplete($_SESSION['user_id'], $id);
        $flag_correct = true;
        $msg = "🎉 CORRECT FLAG! Lab Completed.";
    } else {
        $msg = "❌ Wrong flag. Try harder!";
    }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<h1>SQL Injection Lab</h1>
<p>Try to break the search. Hint: Use <code>' OR '1'='1</code></p>
<form method="GET">
    <input type="text" name="search" placeholder="Search for user...">
    <button type="submit">Search</button>
</form>
<div style="border:1px solid #ccc; padding:10px;"><?= $msg ?></div>

<form method="POST">
    <h3>Found the Admin Password? Submit it:</h3>
    <input type="text" name="flag" placeholder="Enter flag">
    <button type="submit">Submit Flag</button>
</form>
<?php if ($flag_correct): ?><h2 style="color:green;">✅ Lab Passed!</h2><?php endif; ?>
<a href="<?= BASE_URL ?>dashboard.php">Back</a>
</div></body></html>