<?php
session_start();
require_once 'db_connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}

// fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT `Name`, `Email`, `Gender`, `DateOfBirth`, `University` FROM `userprofile` WHERE `UserID` = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Error: User profile not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - StudyPal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; margin: 0; }
        .header { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); padding: 0 40px; display: flex; justify-content: space-between; align-items: center; height: 60px; }
        .header h1 { font-size: 24px; font-weight: 700; color: #1877f2; margin: 0; }
        .header-title-link { text-decoration: none; }
        .header-nav { display: flex; align-items: center; }
        .header-nav a { text-decoration: none; }
        .logout-btn { color: #555; background-color: #f0f0f0; padding: 8px 15px; border-radius: 5px; font-weight: bold; text-decoration: none; }
        .profile-container { max-width: 500px; margin: 50px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .profile-container h2 { text-align: center; margin-bottom: 30px; font-size: 28px; color: #333; }
        .profile-info { margin-bottom: 25px; }
        .info-item { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #eee; }
        .info-item:last-child { border-bottom: none; }
        .info-item label { font-weight: 600; color: #555; }
        .info-item span { color: #333; }
        .back-link { display: inline-block; margin-top: 10px; font-weight: 500; font-size: 16px; color: #1877f2; text-decoration: none; }
        .action-links { display: flex; justify-content: space-between; align-items: center; margin-top: 30px; font-weight: bold; font-size: 16px; }
        .edit-btn { background-color: #1877f2; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; }
    </style>
</head>
<body>
    <header class="header">
        <a href="homepage.php" class="header-title-link"><h1>StudyPal</h1></a>
        <div class="header-nav">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="profile-container">
        <h2>Your Profile</h2>

        <div class="profile-info">
            <div class="info-item">
                <label>Full Name:</label>
                <span><?php echo htmlspecialchars($user['Name']); ?></span>
            </div>
            <div class="info-item">
                <label>Email Address:</label>
                <span><?php echo htmlspecialchars($user['Email']); ?></span>
            </div>
             <div class="info-item">
                <label>University:</label>
                <span><?php echo htmlspecialchars($user['University']); ?></span>
            </div>
            <div class="info-item">
                <label>Date of Birth:</label>
                <span><?php echo htmlspecialchars($user['DateOfBirth']); ?></span>
            </div>
            <div class="info-item">
                <label>Gender:</label>
                <span><?php echo htmlspecialchars(ucfirst($user['Gender'])); ?></span>
            </div>
        </div>
        
        <div class="action-links">
            <a href="homepage.php" class="back-link">&larr; Home</a>
            <a href="edit_profile.php" class="edit-btn">Edit Profile</a>
        </div>
    </div>
</body>
</html>