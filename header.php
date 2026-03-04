<?php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}
?>
<style>
    body { padding-top: 70px; }
    .header { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); padding: 0 40px; display: flex; justify-content: space-between; align-items: center; height: 60px; position: fixed; top: 0; left: 0; right: 0; z-index: 1000; }
    .header h1 { font-size: 24px; font-weight: 700; color: #1877f2; margin: 0; }
    .header-title-link { text-decoration: none; }
    .header-nav { display: flex; align-items: center; }
    .header-nav a { text-decoration: none; }
    .admin-btn { color: #6f42c1; background-color: #f0f0f0; padding: 8px 15px; border-radius: 5px; font-weight: bold; margin-right: 15px; }
    .logout-btn { color: #555; background-color: #f0f0f0; padding: 8px 15px; border-radius: 5px; font-weight: bold; }
    .profile-icon { width: 40px; height: 40px; border-radius: 50%; background-color: #ddd; display: flex; justify-content: center; align-items: center; font-size: 20px; font-weight: bold; color: #333; margin-left: 20px; }
</style>
<header class="header">
    <a href="homepage.php" class="header-title-link"><h1>StudyPal</h1></a>
    <div class="header-nav">
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
            <!-- admin only -->
            <a href="admin_dashboard.php" class="admin-btn">Admin</a>
        <?php endif; ?>
        <a href="logout.php" class="logout-btn">Logout</a>
        <a href="profile.php" class="profile-icon"><span>P</span></a>
    </div>
</header>