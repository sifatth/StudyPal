<?php
// --- Sign-Up Processing with OTP Step 1 ---
session_start();
require_once 'db_connect.php';
require_once 'send_otp_email.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. Retrieve and Sanitize Form Data ---
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $gender = $_POST['gender'];
    $university = trim($_POST['university']);
    $dob = $_POST['dob'];

    // --- 2. Validate the Data ---
    if (empty($name) || empty($email) || empty($password) || empty($gender) || empty($university) || empty($dob)) {
        die("Error: Please fill all the required fields.");
    }
    if ($password !== $confirm_password) {
        header("Location: error_ummatched_password.html");
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: error_invalid_email.html");
        exit;
    }

    // --- 2.5 Verify domain has active mail server records ---
    $email_parts = explode('@', $email);
    $domain = array_pop($email_parts);
    if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
        header("Location: error_invalid_email.html");
        exit;
    }


    // --- 3. Check if email already exists ---
    $stmt_check = $conn->prepare("SELECT UserID FROM users WHERE Email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        header("Location: error_duplicate_email.html");
        exit;
    }
    $stmt_check->close();

    // --- 4. Generate OTP and store data in session ---
    $otp = rand(100000, 999999); 

    // stores otp
    $_SESSION['registration_data'] = [
        'name' => $name,
        'email' => $email,
        'password' => $password, 
        'gender' => $gender,
        'university' => $university,
        'dob' => $dob
    ];
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;

    // Insert OTP into database
    $stmt_otp = $conn->prepare("INSERT INTO otpverification (UserID, OTPCode, Email, IsUsed, GeneratedAt) VALUES (NULL, ?, ?, 0, NOW())");
    $stmt_otp->bind_param("is", $otp, $email);
    if (!$stmt_otp->execute()) {
        die("Could not save OTP: " . htmlspecialchars($stmt_otp->error));
    }
    $stmt_otp->close();

    try {
        sendStudyPalOtp(
            $email,
            $otp,
            "Your StudyPal Verification Code",
            "Welcome to StudyPal!\r\nYour email verification OTP code is: " . $otp . "\r\nThis code will expire in 2 minutes."
        );
    } catch (RuntimeException $exception) {
        die(htmlspecialchars($exception->getMessage()));
    }

    header("Location: verify_otp.php?email=" . urlencode($email));
    exit;
} else {
    echo "Invalid request method.";
}
?>