<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}

$profile_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$current_user_id = $_SESSION['user_id'];

$is_own_profile = ($current_user_id == $profile_user_id);

if ($profile_user_id <= 0) {
    die("Invalid user profile requested.");
}

// fetch user data
$stmt = $conn->prepare("SELECT `Name`, `Email`, `Gender`, `DateOfBirth`, `University` FROM userprofile WHERE UserID = ?");
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User profile not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($user['Name']); ?>'s Profile - StudyPal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; margin: 0; padding-top: 70px; }
        .header { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); padding: 0 40px; display: flex; justify-content: space-between; align-items: center; height: 60px; position: fixed; top: 0; left: 0; right: 0; z-index: 1000; }
        .header h1 { font-size: 24px; font-weight: 700; color: #1877f2; margin: 0; }
        .header-title-link { text-decoration: none; }
        .header-nav { display: flex; align-items: center; }
        .header-nav a { text-decoration: none; }
        .logout-btn { color: #555; background-color: #f0f0f0; padding: 8px 15px; border-radius: 5px; font-weight: bold; }
        .profile-container { max-width: 500px; margin: 50px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .profile-container h2 { text-align: center; margin-bottom: 30px; font-size: 28px; color: #333; }
        .profile-info { margin-bottom: 25px; }
        .info-item { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #eee; }
        .info-item:last-child { border-bottom: none; }
        .info-item label { font-weight: 600; color: #555; }
        .info-item span { color: #333; }
        .back-link { display: block; text-align: left; margin-top: 30px; font-weight: 500; font-size: 16px; color: #1877f2; text-decoration: none; }
        .report-btn { background: #fffbeb; color: #d97706; font-size: 13px; margin-top: 32px; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-weight: bold; flex-shrink: 0; border: none; cursor: pointer; }
        .action-links { display: flex; justify-content: space-between; align-items: center; margin-top: 30px; }
        .popup-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .popup-content { background: #fff; padding: 30px; border-radius: 8px; width: 400px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .popup-content h2 { margin-top: 0; }
        .popup-content textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-family: Inter, sans-serif; font-size: 14px; }
        .popup-content button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-family: Inter, sans-serif; font-size: 14px; }
        .popup-close { float: right; cursor: pointer; font-size: 24px; font-weight: bold; }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <h2><?php echo htmlspecialchars($user['Name']); ?>'s Profile</h2>
        </div>

        <div class="profile-info">
            <div class="info-item">
                <label>University:</label>
                <span><?php echo htmlspecialchars($user['University']); ?></span>
            </div>
            <div class="info-item">
                <label>Email Address:</label>
                <span><?php echo htmlspecialchars($user['Email']); ?></span>
            </div>
            <div class="info-item">
                <label>Gender:</label>
                <span><?php echo htmlspecialchars(ucfirst($user['Gender'])); ?></span>
            </div>
        </div>
        <div class="action-links">
            <a href="javascript:history.back()" class="back-link">&larr; Back</a>
            <?php if (!$is_own_profile): ?>
                <button class="report-btn" onclick="openReportPopup()">Report</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- report popup -->
    <div class="popup-overlay" id="reportPopup">
        <div class="popup-content">
            <span class="popup-close" onclick="closeReportPopup()">&times;</span>
            <h2>Report User</h2>
            <form action="report_process.php" method="POST">
                <input type="hidden" name="reported_user_id" value="<?php echo $profile_user_id; ?>">
                <input type="hidden" name="target_type" value="user">
                <textarea name="reason" placeholder="Please provide a reason for your report..." required></textarea>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <script>
        const reportPopup = document.getElementById('reportPopup');

        function openReportPopup() {
            reportPopup.style.display = 'flex';
        }

        function closeReportPopup() {
            reportPopup.style.display = 'none';
        }
    </script>
</body>
</html>