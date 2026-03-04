<?php
// --- Sign-Up Processing with OTP Step 1 ---
session_start();
require_once 'db_connect.php';

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
    // In a real application, you would email this OTP to the user.
    $otp = rand(100000, 999999); 
    $to = $email;
    $subject = "Your StudyPal Verification Code";
    $message = "Your OTP code is: " . $otp;
    $headers = "From: husain.sifat@northsouth.edu";

    mail($to, $subject, $message, $headers);

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

    header("Location: verify_otp.php?email=" . urlencode($email));
    exit;
} else {
    echo "Invalid request method.";
}
?>