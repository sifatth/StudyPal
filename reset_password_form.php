<?php
session_start();

// Only allow access if OTP was verified
if (!isset($_SESSION['reset_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.html");
    exit;
}
$email = $_SESSION['reset_email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - StudyPal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap');
        * { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; font-weight: 300; background: linear-gradient(135deg, #f7faff 0%, #eef4ff 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .container { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); padding: 48px 42px; border-radius: 28px; box-shadow: 0 20px 60px rgba(37, 99, 235, 0.12); width: 100%; max-width: 460px; text-align: center; border: 1px solid rgba(37, 99, 235, 0.2); }
        h1 { color: #2563eb; font-size: 28px; margin: 0 0 24px; font-family: 'Montserrat', sans-serif; font-weight: 400; letter-spacing: -0.5px; }
        input[type="password"] { width: 100%; padding: 12px 16px; margin-bottom: 16px; border: 1px solid #dbe4f0; border-radius: 999px; box-sizing: border-box; font-family: 'DM Sans', sans-serif; font-weight: 300; font-size: 15px; outline: none; }
        input[type="password"]:focus { border-color: #60a5fa; box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.2); }
        button { width: 100%; padding: 12px 20px; background: linear-gradient(135deg, #2563eb, #3b82f6); border: none; border-radius: 999px; color: white; font-size: 16px; font-weight: 400; cursor: pointer; font-family: 'DM Sans', sans-serif; font-weight: 300; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2); margin-top: 8px; }
        button:hover { filter: brightness(1.05); }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create New Password</h1>
        <form action="update_password_from_reset.php" method="POST">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>