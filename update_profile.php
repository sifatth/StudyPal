<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];

    // update profile
    $name = trim($_POST['name']);
    $university = trim($_POST['university']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];

    $stmt_profile = $conn->prepare("UPDATE userprofile SET Name = ?, University = ?, DateOfBirth = ?, Gender = ? WHERE UserID = ?");
    $stmt_profile->bind_param("ssssi", $name, $university, $dob, $gender, $user_id);
    if (!$stmt_profile->execute()) {
        die("Error updating profile: " . $stmt_profile->error);
    }
    $stmt_profile->close();

    // change password
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    if (!empty($current_password) && !empty($new_password)) {
        $stmt_pass = $conn->prepare("SELECT passwords FROM users WHERE UserID = ?");
        $stmt_pass->bind_param("i", $user_id);
        $stmt_pass->execute();
        $result_pass = $stmt_pass->get_result();
        $user_data = $result_pass->fetch_assoc();
        $stmt_pass->close();

        if ($current_password === $user_data['passwords']) {
            $stmt_update_pass = $conn->prepare("UPDATE users SET passwords = ? WHERE UserID = ?");
            $stmt_update_pass->bind_param("si", $new_password, $user_id);
            $stmt_update_pass->execute();
            $stmt_update_pass->close();
        } else {
            header("Location: error_incorrect_password.html");
            exit;
        }
    }
    
    $conn->close();
    header("Location: profile.php");
    exit;
}
?>