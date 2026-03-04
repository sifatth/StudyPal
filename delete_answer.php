<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.html');
    exit;
}

$answer_id = isset($_GET['answer_id']) ? (int)$_GET['answer_id'] : 0;
$question_id = isset($_GET['question_id']) ? (int)$_GET['question_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($answer_id <= 0 || $question_id <= 0) {
    die("Invalid request.");
}

// check user = answerer
$stmt = $conn->prepare("SELECT AnsweredBy FROM answer WHERE AnswerID = ?");
$stmt->bind_param("i", $answer_id);
$stmt->execute();
$answer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($answer && $answer['AnsweredBy'] == $user_id) {
    $delete_stmt = $conn->prepare("DELETE FROM answer WHERE AnswerID = ?");
    $delete_stmt->bind_param("i", $answer_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    header("Location: view_question.php?question_id=" . $question_id);
    exit;
} else {
    die("Access Denied: You do not have permission to delete this answer.");
}
?>