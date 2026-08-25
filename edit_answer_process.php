<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("You must be logged in to edit an answer.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $answer_id = isset($_POST['answer_id']) ? (int)$_POST['answer_id'] : 0;
    $question_id = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;
    $content = trim($_POST['answer_content']);
    $user_id = $_SESSION['user_id'];

    if ($answer_id <= 0 || $question_id <= 0 || empty($content)) {
        die("Invalid request. Answer content cannot be empty.");
    }

    // Verify ownership
    $stmt_check = $conn->prepare("SELECT AnsweredBy FROM answer WHERE AnswerID = ?");
    $stmt_check->bind_param("i", $answer_id);
    $stmt_check->execute();
    $answer = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if (!$answer) {
        die("Answer not found.");
    }

    if ($answer['AnsweredBy'] != $user_id) {
        die("Access Denied: You do not have permission to edit this answer.");
    }

    // Update answer
    $stmt_update = $conn->prepare("UPDATE answer SET Content = ? WHERE AnswerID = ?");
    $stmt_update->bind_param("si", $content, $answer_id);
    
    if ($stmt_update->execute()) {
        header("Location: view_question.php?question_id=" . $question_id);
        exit;
    } else {
        die("Error: Could not update the answer.");
    }
    $stmt_update->close();
    $conn->close();
}
?>
