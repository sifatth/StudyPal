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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['Name']); ?>'s Profile - StudyPal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap');
        body { font-family: 'DM Sans', sans-serif; font-weight: 300; background: linear-gradient(135deg, #f7faff 0%, #eef4ff 100%); margin: 0; padding-top: 74px; color: #14213d; }
        .profile-container { max-width: 560px; margin: 28px auto 48px; background: rgba(255,255,255,0.96); padding: 32px; border-radius: 24px; border: 1px solid #e4ebf7; box-shadow: 0 10px 35px rgba(15,23,42,0.06); }
        .profile-container h2 { text-align: center; margin-bottom: 24px; font-size: 1.6rem; color: #0f172a; }
        .profile-info { margin-bottom: 24px; }
        .info-item { display: flex; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid #eef2f7; gap: 12px; }
        .info-item:last-child { border-bottom: none; }
        .info-item label { font-weight: 400; color: #64748b; }
        .info-item span { color: #0f172a; text-align: right; }
        .back-link { display: block; text-align: left; margin-top: 8px; font-weight: 400; font-size: 15px; color: #2563eb; text-decoration: none; }
        .report-btn { background: #fef3c7; color: #92400e; font-size: 13px; margin-top: 24px; padding: 8px 12px; border-radius: 999px; text-decoration: none; font-weight: 400; flex-shrink: 0; border: none; cursor: pointer; }
        .action-links { display: flex; justify-content: space-between; align-items: center; margin-top: 24px; gap: 12px; flex-wrap: wrap; }
        .popup-overlay { display: none; position: fixed; inset: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.55); justify-content: center; align-items: center; z-index: 1000; padding: 20px; }
        .popup-content { background: #fff; padding: 28px; border-radius: 18px; width: 100%; max-width: 420px; box-shadow: 0 16px 40px rgba(15,23,42,0.2); }
        .popup-content h2 { margin-top: 0; color: #0f172a; }
        .popup-content textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #dbe4f0; border-radius: 10px; box-sizing: border-box; font-family: 'DM Sans', sans-serif; font-weight: 300; font-size: 14px; }
        .popup-content button { background: linear-gradient(135deg, #16a34a, #22c55e); color: white; padding: 10px 15px; border: none; border-radius: 999px; cursor: pointer; font-family: 'DM Sans', sans-serif; font-weight: 300; font-size: 14px; font-weight: 400; }
        .popup-close { float: right; cursor: pointer; font-size: 24px; font-weight: 400; color: #64748b; }
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