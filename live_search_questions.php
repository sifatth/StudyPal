<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || !isset($_GET['group_id']) || !isset($_GET['query'])) {
    exit;
}

$group_id = (int)$_GET['group_id'];
$search_term = trim($_GET['query']);

// search questions
$sql = "SELECT QuestionID, Title 
        FROM question 
        WHERE GroupID = ? AND (Title LIKE ? OR Description LIKE ?)";

$like_term = "%" . $search_term . "%";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $group_id, $like_term, $like_term);
$stmt->execute();
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);

if (!empty($questions)) {
    foreach ($questions as $question) {
        $question_id = $question['QuestionID'];
        $title = htmlspecialchars($question['Title']);
        echo "<li><a href='view_question.php?question_id={$question_id}'>{$title}</a></li>";
    }
} else {
    echo "<li>No question found matching your search.</li>";
}
?>