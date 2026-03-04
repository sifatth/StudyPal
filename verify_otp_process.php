<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['registration_data']) || !isset($_SESSION['otp'])) {
    header("Location: signup.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submitted_otp = trim($_POST['otp_code']);
    $stored_otp = $_SESSION['otp'];

    // check if OTP is correct
    if ($submitted_otp == $stored_otp) {
        $user_data = $_SESSION['registration_data'];
        
        // insert into users table
        $stmt_users = $conn->prepare("INSERT INTO users (Email, Passwords) VALUES (?, ?)");
        $stmt_users->bind_param("ss", $user_data['email'], $user_data['password']);
        
        if ($stmt_users->execute()) {
            $new_user_id = $conn->insert_id;

            // insert OTP log
            $otp_log_stmt = $conn->prepare("INSERT INTO otpverification (UserID, OTPCode, IsUsed) VALUES (?, ?, 1)");
            $otp_log_stmt->bind_param("is", $new_user_id, $stored_otp);
            $otp_log_stmt->execute();
            $otp_log_stmt->close();

            // Insert into userprofile table
            $stmt_profile = $conn->prepare("INSERT INTO userprofile (UserID, `Name`, Gender, DateOfBirth, University, Email) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_profile->bind_param("isssss", $new_user_id, $user_data['name'], $user_data['gender'], $user_data['dob'], $user_data['university'], $user_data['email']);
            
            if ($stmt_profile->execute()) {
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $new_user_id;

                unset($_SESSION['registration_data']);
                unset($_SESSION['otp']);
                unset($_SESSION['otp_email']);

                header("Location: homepage.php");
                exit;
            }
        }
        die("Error: Could not complete registration.");

    } else {
        die("Error: Incorrect OTP. Please go back and try again.");
    }
}
?>