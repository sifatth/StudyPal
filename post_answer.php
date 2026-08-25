<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin'])) {
    die("You must be logged in to post an answer.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_id = (int)$_POST['question_id'];
    $user_id = $_SESSION['user_id'];
    $content = trim($_POST['answer_content']);

    if (empty($content)) {
        die("Error: Answer content cannot be empty.");
    }

    // check user = group member
    $group_check_stmt = $conn->prepare("SELECT GroupID FROM question WHERE QuestionID = ?");
    $group_check_stmt->bind_param("i", $question_id);
    $group_check_stmt->execute();
    $group_id_result = $group_check_stmt->get_result()->fetch_assoc();
    if (!$group_id_result) {
        die("Error: The question you are trying to answer does not exist.");
    }
    $group_id = $group_id_result['GroupID'];
    $group_check_stmt->close();

    $member_check_stmt = $conn->prepare("SELECT 1 FROM groupmembership WHERE GroupID = ? AND UserID = ?");
    $member_check_stmt->bind_param("ii", $group_id, $user_id);
    $member_check_stmt->execute();
    if ($member_check_stmt->get_result()->num_rows === 0) {
        die("Access Denied: Only group members can post answers.");
    }
    $member_check_stmt->close();

    // insert answer
    $stmt = $conn->prepare("INSERT INTO answer (QuestionID, AnsweredBy, Content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $question_id, $user_id, $content);
    
    if ($stmt->execute()) {
        header("Location: view_question.php?question_id=" . $question_id);
        exit;
    } else {
        die("Error: Could not post your answer. " . $stmt->error);
    }
}
?>