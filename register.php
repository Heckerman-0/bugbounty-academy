<?php require_once 'includes/auth.php'; 
$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $res = registerUser($_POST['username'], $_POST['email'], $_POST['password']);
    if ($res) { $msg = "✅ Registered! <a href='login.php'>Login now</a>"; } 
    else { $msg = "❌ Username or Email already exists."; }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="assets/css/style.css"></head>
<body><div class="container">
<?php include 'includes/nav.php'; ?>
<h1 style="font-size:2.5rem;">⚡ Register</h1>
<?php if($msg) echo '<div style="margin-bottom:15px;">'.$msg.'</div>'; ?>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password (min 6 chars)" required>
    <button type="submit" style="width:100%;">Create Account</button>
</form>
<p style="margin-top:15px; color:#888;">Already have an account? <a href="login.php">Login</a></p>
</div></body></html>