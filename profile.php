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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - StudyPal</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap');
        body { font-family: 'DM Sans', sans-serif; font-weight: 300; background: linear-gradient(135deg, #f7faff 0%, #eef4ff 100%); margin: 0; color: #14213d; min-height: 100vh; }
        .profile-container { max-width: 560px; margin: 28px auto 48px; background: rgba(255,255,255,0.96); padding: 32px; border-radius: 24px; border: 1px solid #e4ebf7; box-shadow: 0 10px 35px rgba(15, 23, 42, 0.06); }
        .profile-container h2 { text-align: center; margin-bottom: 24px; font-size: 1.6rem; color: #0f172a; }
        .profile-info { margin-bottom: 24px; }
        .info-item { display: flex; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid #eef2f7; gap: 12px; }
        .info-item:last-child { border-bottom: none; }
        .info-item label { font-weight: 400; color: #64748b; }
        .info-item span { color: #0f172a; text-align: right; }
        .back-link { display: inline-block; font-weight: 400; font-size: 15px; color: #2563eb; text-decoration: none; }
        .action-links { display: flex; justify-content: space-between; align-items: center; margin-top: 24px; gap: 12px; flex-wrap: wrap; }
        .edit-btn { background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; padding: 10px 18px; border-radius: 999px; text-decoration: none; font-weight: 400; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.18); }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

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
