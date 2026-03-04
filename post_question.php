<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin'])) die("You must be logged in.");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $group_id = (int)$_POST['group_id'];
    $user_id = $_SESSION['user_id'];

    // check user = group member
    $member_check_stmt = $conn->prepare("SELECT 1 FROM groupmembership WHERE GroupID = ? AND UserID = ?");
    $member_check_stmt->bind_param("ii", $group_id, $user_id);
    $member_check_stmt->execute();
    if ($member_check_stmt->get_result()->num_rows === 0) {
        die("Access Denied: Only group members can ask questions.");
    }
    $member_check_stmt->close();

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($title)) {
        die("Error: A question title is required.");
    }

    // insert question
    $stmt = $conn->prepare("INSERT INTO question (GroupID, AskedBy, Title, Description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $group_id, $user_id, $title, $description);
    
    if ($stmt->execute()) {
        header("Location: group_page.php?group_id=" . $group_id);
        exit;
    } else {
        die("Error: Could not post your question.");
    }
}
?>