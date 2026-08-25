<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: error_invalid_credentials.html");
        exit;
    }

    // login info
    $stmt = $conn->prepare("SELECT UserID, passwords, IsAdmin FROM users WHERE Email = ? AND IsActive = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($password === $user['passwords']) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['is_admin'] = ($user['IsAdmin'] == 1);
            
            $update_stmt = $conn->prepare("UPDATE users SET LastLogin = NOW() WHERE UserID = ?");
            $update_stmt->bind_param("i", $user['UserID']);
            $update_stmt->execute();
            $update_stmt->close();

            header("Location: homepage.php");
            exit;
        }
    }
    header("Location: error_invalid_credentials.html");
    exit;
}
?>