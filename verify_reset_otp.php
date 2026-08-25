<?php
session_start();

if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.html");
    exit;
}
$email_for_display = htmlspecialchars($_SESSION['reset_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - StudyPal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap');
        * { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; font-weight: 300; background: linear-gradient(135deg, #f7faff 0%, #eef4ff 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .container { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); padding: 48px 42px; border-radius: 28px; box-shadow: 0 20px 60px rgba(37, 99, 235, 0.12); width: 100%; max-width: 480px; text-align: center; border: 1px solid rgba(37, 99, 235, 0.2); }
        h1 { color: #2563eb; font-size: 28px; margin: 0 0 12px; font-family: 'Montserrat', sans-serif; font-weight: 400; letter-spacing: -0.5px; }
        p { color: #475569; font-size: 15px; line-height: 1.6; margin: 12px 0; }
        strong { color: #0f172a; font-weight: 400; }
        input { width: 100%; padding: 12px 16px; margin: 24px 0; border: 1px solid #dbe4f0; border-radius: 999px; box-sizing: border-box; text-align: center; font-size: 18px; font-family: 'DM Sans', sans-serif; font-weight: 300; outline: none; letter-spacing: 2px; }
        input:focus { border-color: #60a5fa; box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.2); }
        button { width: 100%; padding: 12px 20px; background: linear-gradient(135deg, #2563eb, #3b82f6); border: none; border-radius: 999px; color: white; font-size: 16px; font-weight: 400; cursor: pointer; font-family: 'DM Sans', sans-serif; font-weight: 300; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2); }
        button:hover { filter: brightness(1.05); }
        .hint { font-size: 12px; color: #94a3b8; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verify Your Identity</h1>
        <p>We've sent a 6-digit verification code to <strong><?php echo $email_for_display; ?></strong>.</p>
        <p>Please enter it below to reset your password.</p>
        <form action="verify_reset_otp_process.php" method="POST">
            <input type="text" name="otp_code" placeholder="_ _ _ _ _ _" maxlength="6" required>
            <button type="submit">Verify & Continue</button>
        </form>
    </div>
</body>
</html>
