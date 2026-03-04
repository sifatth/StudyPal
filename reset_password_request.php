<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // check email
    $stmt = $conn->prepare("SELECT UserID FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        header("Location: reset_password_form.php?email=" . urlencode($email));
        exit;
    } else {
        header("Location: error_wrong_email_reset.html");
        exit;
    }
}
?>