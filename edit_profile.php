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
    <title>Edit Profile - StudyPal</title>
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
        label { font-weight: 600; display: block; margin-bottom: 8px; color: #333; }
        input[type="text"], input[type="date"], input[type="password"] { width: 100%; padding: 12px; margin-bottom: 20px; border-radius: 6px; border: 1px solid #ddd; box-sizing: border-box; font-size: 16px; font-family: 'Inter', sans-serif; }
        .readonly-field { width: 100%; padding: 12px; margin-bottom: 20px; border-radius: 6px; border: 1px solid #ddd; box-sizing: border-box; font-size: 16px; background-color: #ffffffff; color: #000000ff; cursor: not-allowed; }
        .gender-selection-container { margin-bottom: 20px; }
        .gender-options { display: flex; align-items: left; justify-content: space-evenly; margin-top: -28px; }
        .gender-options label { font-weight: normal; margin-bottom: 0; }
        .form-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
        button { padding: 12px 30px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; font-family: 'Inter', sans-serif; }
        button:hover { background-color: #218838; }
        a { color: #1877f2; text-decoration: none; font-weight: 500; }
        hr { border: none; border-top: 1px solid #eee; margin: 30px 0; }
    </style>
</head>
<body>
    <header class="header">
        <a href="homepage.php" class="header-title-link"><h1>StudyPal</h1></a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </header>

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
                <button type="submit">Save Changes</button>
                <a href="javascript:history.back()">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>