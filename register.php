<?php require_once 'includes/auth.php'; 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $res = registerUser($_POST['username'], $_POST['email'], $_POST['password']);
    if ($res) { echo "Registered! <a href='login.php'>Login</a>"; } 
    else { echo "Username/Email exists."; }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="assets/css/style.css"></head>
<body><div class="container">
<form method="POST">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Register</button>
</form>
<a href="index.php">Back</a>
</div></body></html>