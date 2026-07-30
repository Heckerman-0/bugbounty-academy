<?php require_once 'includes/auth.php'; 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (loginUser($_POST['username'], $_POST['password'])) {
        header("Location: dashboard.php"); exit;
    } else { echo "Invalid credentials."; }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="assets/css/style.css"></head>
<body><div class="container">
<form method="POST">
    <input type="text" name="username" placeholder="Username or Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Login</button>
</form>
<a href="index.php">Back</a>
</div></body></html>