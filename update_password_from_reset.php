<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        header("Location: error_passwords_do_not_match.html");
        exit;
    }

    // update password
    $stmt = $conn->prepare("UPDATE users SET passwords = ? WHERE Email = ?");
    $stmt->bind_param("ss", $new_password, $email);
    
    if ($stmt->execute()) {
        header("Location: password_reset_success.html");
        exit;
    } else {
        header("Location: error_reset_failed.html");
        exit;
    }
    $stmt->close();
    $conn->close();
}
?>