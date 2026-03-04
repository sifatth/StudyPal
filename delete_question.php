<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}

$question_id = isset($_GET['question_id']) ? (int)$_GET['question_id'] : 0;
$group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($question_id <= 0 || $group_id <= 0) {
    die("Invalid request.");
}

// check user = asker
$stmt = $conn->prepare("SELECT AskedBy FROM question WHERE QuestionID = ?");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$question = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($question && $question['AskedBy'] == $user_id) {
    $delete_stmt = $conn->prepare("DELETE FROM question WHERE QuestionID = ?");
    $delete_stmt->bind_param("i", $question_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    header("Location: group_page.php?group_id=" . $group_id);
    exit;
} else {
    die("Access Denied: You do not have permission to delete this question.");
}
?>