<?php
session_start();
require_once 'db_connect.php';
require_once 'send_otp_email.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // check email
    $stmt = $conn->prepare("SELECT UserID FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user_row = $result->fetch_assoc();
        $user_id = $user_row['UserID'];

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['reset_email'] = $email;

        // Insert OTP into database with UserID and Email
        $stmt_otp = $conn->prepare("INSERT INTO otpverification (UserID, OTPCode, Email, IsUsed, GeneratedAt) VALUES (?, ?, ?, 0, NOW())");
        $stmt_otp->bind_param("iis", $user_id, $otp, $email);
        if (!$stmt_otp->execute()) {
            die("Could not save OTP: " . htmlspecialchars($stmt_otp->error));
        }
        $stmt_otp->close();

        // Send OTP via email
        try {
            sendStudyPalOtp(
                $email,
                $otp,
                "Your StudyPal Password Reset Code",
                "Your password reset OTP code is: " . $otp . "\r\nThis code will expire in 2 minutes."
            );
        } catch (RuntimeException $exception) {
            die(htmlspecialchars($exception->getMessage()));
        }

        header("Location: verify_reset_otp.php");
        exit;
    } else {
        header("Location: error_wrong_email_reset.html");
        exit;
    }
}
?>