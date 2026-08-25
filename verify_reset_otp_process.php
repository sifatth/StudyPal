<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submitted_otp = trim($_POST['otp_code']);
    $email = $_SESSION['reset_email'];

    // Check if the OTP exists, is for this email, and is unused
    $stmt_check = $conn->prepare("SELECT OTP_ID, GeneratedAt FROM otpverification WHERE Email = ? AND OTPCode = ? AND IsUsed = 0 ORDER BY GeneratedAt DESC LIMIT 1");
    $stmt_check->bind_param("si", $email, $submitted_otp);
    $stmt_check->execute();
    $otp_record = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if ($otp_record) {
        // Check if the OTP has expired (2 minutes limit)
        $stmt_expiry = $conn->prepare("SELECT OTP_ID FROM otpverification WHERE OTP_ID = ? AND GeneratedAt >= NOW() - INTERVAL 2 MINUTE");
        $stmt_expiry->bind_param("i", $otp_record['OTP_ID']);
        $stmt_expiry->execute();
        $is_valid = $stmt_expiry->get_result()->num_rows > 0;
        $stmt_expiry->close();

        if (!$is_valid) {
            showErrorPage("OTP Expired", "The verification code has expired (it is only valid for 2 minutes). Please request a new link.", "forgot_password.html", "Back to Password Reset");
            exit;
        }

        // OTP verified — mark as used
        $otp_update = $conn->prepare("UPDATE otpverification SET IsUsed = 1 WHERE OTP_ID = ?");
        $otp_update->bind_param("i", $otp_record['OTP_ID']);
        $otp_update->execute();
        $otp_update->close();

        $_SESSION['reset_verified'] = true;
        unset($_SESSION['reset_otp']);

        header("Location: reset_password_form.php");
        exit;
    } else {
        showErrorPage("Incorrect OTP", "The verification code you entered is incorrect or has already been used. Please go back and try again.", "verify_reset_otp.php", "Try Again");
        exit;
    }
}

function showErrorPage($title, $message, $linkHref, $linkText) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - StudyPal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            font-weight: 300;
            background: linear-gradient(135deg, #f7faff 0%, #eef4ff 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .error-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            padding: 48px;
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(37, 99, 235, 0.12);
            width: 100%;
            max-width: 480px;
            text-align: center;
            border: 1px solid rgba(37, 99, 235, 0.2);
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            color: #2563eb;
            font-size: 28px;
            margin: 0 0 12px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;
            letter-spacing: -0.5px;
        }

        p {
            color: #475569;
            font-size: 16px;
            line-height: 1.6;
            margin: 12px 0;
        }

        .action-link {
            display: inline-block;
            margin-top: 28px;
            padding: 12px 28px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            text-decoration: none;
            font-weight: 300;
            border-radius: 999px;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2);
            font-family: 'DM Sans', sans-serif;
            transition: filter 0.2s ease, transform 0.2s ease;
        }

        .action-link:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1><?php echo htmlspecialchars($title); ?></h1>
        <p><?php echo htmlspecialchars($message); ?></p>
        <a href="<?php echo htmlspecialchars($linkHref); ?>" class="action-link"><?php echo htmlspecialchars($linkText); ?></a>
    </div>
</body>
</html>
<?php
}
?>
