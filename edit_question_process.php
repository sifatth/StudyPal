<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("You must be logged in to edit a question.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_id = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id'];

    if ($question_id <= 0 || empty($title)) {
        die("Invalid request. Question title is required.");
    }

    // Verify ownership
    $stmt_check = $conn->prepare("SELECT AskedBy FROM question WHERE QuestionID = ?");
    $stmt_check->bind_param("i", $question_id);
    $stmt_check->execute();
    $question = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if (!$question) {
        die("Question not found.");
    }

    if ($question['AskedBy'] != $user_id) {
        die("Access Denied: You do not have permission to edit this question.");
    }

    // Update question
    $stmt_update = $conn->prepare("UPDATE question SET Title = ?, Description = ? WHERE QuestionID = ?");
    $stmt_update->bind_param("ssi", $title, $description, $question_id);
    
    if ($stmt_update->execute()) {
        header("Location: view_question.php?question_id=" . $question_id);
        exit;
    } else {
        die("Error: Could not update the question.");
    }
    $stmt_update->close();
    $conn->close();
}
?>
