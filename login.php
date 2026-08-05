<?php require_once 'includes/auth.php'; 
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken()) {
        $error = "Invalid form submission. Please try again.";
    } elseif (loginUser($_POST['username'], $_POST['password'])) {
        header("Location: " . BASE_URL . "dashboard.php"); exit;
    } else { $error = "Invalid credentials."; }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="assets/css/style.css"></head>
<body><div class="container">
<?php include 'includes/nav.php'; ?>
<h1 style="font-size:2.5rem;">🔐 Login</h1>
<?php if($error) echo '<div style="color:#ff6b6b; margin-bottom:15px;">'.htmlspecialchars($error).'</div>'; ?>
<form method="POST">
    <?= csrfField() ?>
    <input type="text" name="username" placeholder="Username or Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" style="width:100%;">Login</button>
</form>
<p style="margin-top:15px; color:#888;">New here? <a href="register.php">Create an account</a></p>
</div></body></html>
