<?php
// Include this file in every page that needs the top navigation bar
// It assumes session is already started (config.php does that)
$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = isLoggedIn();
?>
<nav class="cyber-nav">
    <div class="nav-brand">
        <a href="<?= BASE_URL ?>index.php" style="border-bottom:none; font-size:1.4rem; font-weight:800; background: linear-gradient(135deg, #00f2fe, #fe00fe); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">🛡️ BBA</a>
    </div>
    <div class="nav-links">
        <?php if ($is_logged_in): ?>
<a href="<?= BASE_URL ?>dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>modules/labs/index.php">💻 Labs</a>
            <a href="<?= BASE_URL ?>modules/writeups/index.php">📝 Writeups</a>
<a href="<?= BASE_URL ?>leaderboard.php" class="<?= ($current_page == 'leaderboard.php') ? 'active' : '' ?>">🏆 Leaderboard</a>
            <a href="<?= BASE_URL ?>profile.php" class="<?= ($current_page == 'profile.php') ? 'active' : '' ?>">👤 Account</a>
            <?php if (isAdmin()): ?>
                <a href="<?= BASE_URL ?>admin/index.php" style="color:#fe00fe;">⚙️ Admin</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>logout.php" style="color:#fe00fe; border-bottom-color:#fe00fe;">🚪 Logout</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>login.php" class="<?= ($current_page == 'login.php') ? 'active' : '' ?>">🔐 Login</a>
            <a href="<?= BASE_URL ?>register.php" class="<?= ($current_page == 'register.php') ? 'active' : '' ?>">⚡ Register</a>
        <?php endif; ?>
    </div>
</nav>
<style>
.cyber-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0 15px 0;
    margin-bottom: 25px;
    border-bottom: 1px solid rgba(0, 242, 254, 0.1);
    flex-wrap: wrap;
    gap: 15px;
}
.cyber-nav .nav-links {
    display: flex;
    gap: 25px;
    flex-wrap: wrap;
    align-items: center;
}
.cyber-nav .nav-links a {
    font-size: 0.95rem;
    font-weight: 600;
    color: #a0a0d0;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
    padding-bottom: 4px;
}
.cyber-nav .nav-links a:hover {
    color: #00f2fe;
    border-bottom-color: #00f2fe;
}
.cyber-nav .nav-links a.active {
    color: #00f2fe;
    border-bottom-color: #00f2fe;
}
@media (max-width: 600px) {
    .cyber-nav { flex-direction: column; align-items: stretch; text-align: center; }
    .cyber-nav .nav-links { justify-content: center; gap: 15px; }
}
</style>