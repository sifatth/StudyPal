<?php
session_start();
require_once 'db_connect.php';

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

$error_message = '';
if (isset($_GET['error']) && $_GET['error'] == 'incorrect_password') {
    $error_message = 'The current password you entered is incorrect. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - StudyPal</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap');
        * { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; font-weight: 300; background: linear-gradient(135deg, #f7faff 0%, #eef4ff 100%); margin: 0; color: #14213d; min-height: 100vh; }
        .profile-container { max-width: 560px; margin: 28px auto 48px; background: rgba(255,255,255,0.96); padding: 32px; border-radius: 24px; border: 1px solid #e4ebf7; box-shadow: 0 10px 35px rgba(15, 23, 42, 0.06); }
        .profile-container h2 { text-align: center; margin-bottom: 28px; font-size: 1.6rem; color: #0f172a; }
        label { font-weight: 400; display: block; margin-bottom: 8px; color: #334155; }
        input[type="text"], input[type="date"], input[type="password"] { width: 100%; padding: 12px 14px; margin-bottom: 18px; border-radius: 12px; border: 1px solid #dbe4f0; box-sizing: border-box; font-size: 15px; font-family: 'DM Sans', sans-serif; font-weight: 300; outline: none; background: #f8fbff; }
        input[type="text"]:focus, input[type="date"]:focus, input[type="password"]:focus { border-color: #60a5fa; box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.16); }
        .readonly-field { width: 100%; padding: 12px 14px; margin-bottom: 18px; border-radius: 12px; border: 1px solid #dbe4f0; box-sizing: border-box; font-size: 15px; background-color: #f1f5f9; color: #64748b; cursor: not-allowed; }
        .gender-selection-container { margin-bottom: 18px; }
        .gender-options { display: flex; align-items: center; justify-content: space-evenly; margin-top: -28px; }
        .gender-options label { font-weight: normal; margin-bottom: 0; color: #475569; }
        .form-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 24px; gap: 12px; flex-wrap: wrap; }
        button { padding: 12px 24px; background: linear-gradient(135deg, #16a34a, #22c55e); color: white; border: none; border-radius: 999px; cursor: pointer; font-size: 15px; font-weight: 400; font-family: 'DM Sans', sans-serif; font-weight: 300; box-shadow: 0 8px 20px rgba(34, 197, 94, 0.18); }
        button:hover { filter: brightness(1.02); }
        a { color: #2563eb; text-decoration: none; font-weight: 400; font-size: 15px; }
        hr { border: none; border-top: 1px solid #eef2f7; margin: 24px 0; }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <div class="profile-container">
        <h2>Edit Your Profile</h2>

        <form action="update_profile.php" method="POST">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['Name']); ?>" required>

            <label for="email">Email Address:</label>
            <div class="readonly-field"><?php echo htmlspecialchars($user['Email']); ?></div>
            
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user['DateOfBirth']); ?>" required>

            <div class="gender-selection-container">
                <label>Gender:</label>
                <div class="gender-options">
                    <label><input type="radio" name="gender" value="male" <?php if($user['Gender'] == 'male') echo 'checked'; ?> required> Male</label>
                    <label><input type="radio" name="gender" value="female" <?php if($user['Gender'] == 'female') echo 'checked'; ?>> Female</label>
                    <label><input type="radio" name="gender" value="other" <?php if($user['Gender'] == 'other') echo 'checked'; ?>> Other</label>
                </div>
            </div>

            <label for="university">University:</label>
            <input type="text" id="university" name="university" value="<?php echo htmlspecialchars($user['University']); ?>" required>
            
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" placeholder="Enter your current password">

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter a new password">

            <div class="form-actions">
                <a href="profile.php">&larr; Cancel</a>
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</body>
</html>
