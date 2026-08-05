<?php 
require_once '../../includes/auth.php';
if (!isLoggedIn()) redirect('login.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 2;
$msg = "";
$flag_correct = false;

if (isset($_GET['search'])) {
    $input = $_GET['search'];
    if (stripos($input, "' OR '1'='1") !== false) {
        $msg = "<b>Exploit Success!</b> You dumped all users: <br> Admin | admin_password_123 <br> John | qwerty";
    } elseif (stripos($input, "admin") !== false) {
        $msg = "Found user: Admin (password: admin_password_123)";
    } else {
        $msg = "No users found.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['flag'])) {
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
?>
<!DOCTYPE html>
<html>
<head>
    <title>AcmeCorp | Employee Directory</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>includes/lab.css">
    <style>
        :root { --accent: #2563eb; --accent2: #1e40af; }
        .emp-table td:first-child { font-weight: 600; }
        .searchbar { display: flex; gap: 10px; max-width: 520px; }
    </style>
</head>
<body>
<div class="lab-banner">
    <div><span class="brand">🛡️ BBA</span> Lab — SQL Injection</div>
    <div><a class="link" href="<?= BASE_URL ?>modules/labs/index.php">⬅ Back to Labs</a></div>
</div>

<header class="site-header">
    <div class="inner">
        <div class="logo">AcmeCorp <span>Employee Directory</span></div>
        <nav>
            <a href="#">Home</a>
            <a href="#">Directory</a>
            <a href="#">Admin</a>
        </nav>
    </div>
</header>

<main class="site-main">
    <div class="site-card">
        <h1>Find an Employee</h1>
        <p class="sub">Search our internal employee directory by name.</p>

        <form method="GET" class="searchbar">
            <input type="text" name="search" placeholder="Search employees..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="btn-site">Search</button>
        </form>

        <?php if ($msg): ?>
            <div class="alert <?= (stripos($msg,'No users')!==false) ? 'warn' : 'ok' ?>"><?= $msg ?></div>
        <?php endif; ?>

        <?php if (stripos($msg, 'Exploit') !== false): ?>
            <table class="site-table">
                <thead><tr><th>Name</th><th>Role</th><th>Password (leaked)</th></tr></thead>
                <tbody>
                    <tr><td>Admin</td><td>Administrator</td><td><code>admin_password_123</code></td></tr>
                    <tr><td>John</td><td>Support</td><td><code>qwerty</code></td></tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="flag-box">
        <h3>🏴 Found the admin password? Submit it as the flag:</h3>
        <form method="POST" class="site-form">
            <?= csrfField() ?>
            <input type="text" name="flag" placeholder="Enter flag">
            <button type="submit" class="btn-site">Submit Flag</button>
        </form>
        <?php if ($flag_correct): ?><div class="flag-done">✅ Lab Passed!</div><?php endif; ?>
    </div>
</main>
</body>
</html>
