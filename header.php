<?php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap');
    body { padding-top: 74px; font-family: 'DM Sans', sans-serif; font-weight: 300; }
    .header { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(200, 210, 230, 0.4); box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04); padding: 0 24px; display: flex; justify-content: space-between; align-items: center; height: 70px; position: fixed; top: 0; left: 0; right: 0; z-index: 1000; }
    .header h1 { font-size: 28px; font-weight: 400; color: #2563eb; margin: 0; font-family: 'Montserrat', sans-serif; letter-spacing: -0.5px; font-style: normal; font-variant: normal; }
    .header-title-link { text-decoration: none; }
    .header-nav { display: flex; align-items: center; gap: 10px; }
    .header-profile-container { position: relative; }
    .profile-icon { width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #2563eb, #60a5fa); display: flex; justify-content: center; align-items: center; font-size: 18px; font-weight: 400; color: white; cursor: pointer; font-family: 'DM Sans', sans-serif; user-select: none; }
    .dropdown-menu { display: none; position: absolute; top: 52px; right: 0; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.1); border: 1px solid #e4ebf7; overflow: hidden; width: 160px; z-index: 1001; }
    .dropdown-menu.show { display: block; }
    .dropdown-item { display: block; padding: 12px 16px; text-decoration: none; color: #334155; font-size: 14px; font-weight: 500; font-family: 'DM Sans', sans-serif; border-bottom: 1px solid #f1f5f9; transition: background 0.2s, color 0.2s; }
    .dropdown-item:last-child { border-bottom: none; }
    .dropdown-item:hover { background: #f8fbff; color: #2563eb; }
    .dropdown-item.admin-link { color: #7c3aed; }
    .dropdown-item.admin-link:hover { background: #f5f3ff; color: #6d28d9; }
</style>
<header class="header">
    <a href="homepage.php" class="header-title-link"><h1>StudyPal</h1></a>
    <div class="header-nav">
        <div class="header-profile-container" id="profileDropdownContainer">
            <div class="profile-icon" id="profileIcon">P</div>
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="profile.php" class="dropdown-item">Profile</a>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                    <a href="admin_dashboard.php" class="dropdown-item admin-link">Admin Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" class="dropdown-item">Logout</a>
            </div>
        </div>
    </div>
</header>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileIcon = document.getElementById('profileIcon');
        const dropdownMenu = document.getElementById('dropdownMenu');
        
        profileIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!dropdownMenu.contains(e.target) && e.target !== profileIcon) {
                dropdownMenu.classList.remove('show');
            }
        });
    });
</script>