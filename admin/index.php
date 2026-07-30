<?php 
require_once '../includes/auth.php';
if (!isLoggedIn() || !isAdmin()) redirect('dashboard.php');
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_content'])) {
    $stmt = $pdo->prepare("INSERT INTO content (type, title, description, body_html, difficulty, points) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$_POST['type'], $_POST['title'], $_POST['desc'], $_POST['body'], $_POST['diff'], $_POST['points']]);
    $msg = "Content added!";
}

if (isset($_GET['approve'])) {
    $stmt = $pdo->prepare("UPDATE writeups SET status='approved' WHERE id=?");
    $stmt->execute([$_GET['approve']]);
    $msg = "Writeup approved!";
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"></head>
<body><div class="container">
<h1>Admin Panel</h1>
<p><?= $msg ?></p>
<h3>Add New Lesson/Lab/Tool</h3>
<form method="POST">
    <select name="type"><option value="lesson">Lesson</option><option value="lab">Lab</option><option value="tool">Tool</option></select>
    <input type="text" name="title" placeholder="Title" required><br>
    <input type="text" name="desc" placeholder="Description"><br>
    <textarea name="body" rows="5" placeholder="Full HTML content"></textarea><br>
    <select name="diff"><option>Beginner</option><option>Intermediate</option><option>Advanced</option></select>
    <input type="number" name="points" placeholder="Points" value="10"><br>
    <button type="submit" name="add_content">Add Content</button>
</form>

<h3>Pending Writeups</h3>
<?php 
$pend = $pdo->query("SELECT * FROM writeups WHERE status='pending'");
while ($p = $pend->fetch()): ?>
    <div>
        <b><?= $p['title'] ?></b>
        <p><?= $p['content'] ?></p>
        <a href="?approve=<?= $p['id'] ?>">✅ Approve</a>
    </div>
<?php endwhile; ?>
<a href="<?= BASE_URL ?>dashboard.php">Back</a>
</div></body></html>