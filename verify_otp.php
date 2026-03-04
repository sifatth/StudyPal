<?php
session_start();

if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email'])) {
    header("Location: signup.html");
    exit;
}
$email_for_display = htmlspecialchars($_SESSION['otp_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email - StudyPal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 400px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 20px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; text-align: center; font-size: 18px; }
        button { width: 100%; padding: 12px; background-color: #28a745; border: none; border-radius: 6px; color: white; font-size: 16px; cursor: pointer; }
        .hint { font-size: 12px; color: #777; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Check Your Email</h1>
        <p>We've sent a 6-digit verification code to <strong><?php echo $email_for_display; ?></strong>. Please enter it below.</p>
        <form action="verify_otp_process.php" method="POST">
            <input type="text" name="otp_code" placeholder="_ _ _ _ _ _" maxlength="6" required>
            <button type="submit">Verify & Create Account</button>
        </form>
        <p class="hint">(Your OTP is <?php echo $_SESSION['otp']; ?>)</p>       <!-- send email didn't work -->
        <!-- <p class="hint">Didn't receive the code?<br>Please check your spam folder or <a href="signup.html">try signing up again</a>.</p> -->
    </div>
</body>
</html>