<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}

$group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($group_id <= 0) {
    die("Invalid group ID.");
}

// check if user = creator
$stmt = $conn->prepare("SELECT CreatedBy FROM studygroup WHERE GroupID = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();
$group = $result->fetch_assoc();

if ($group && $group['CreatedBy'] == $user_id) {
    $delete_stmt = $conn->prepare("DELETE FROM studygroup WHERE GroupID = ?");
    $delete_stmt->bind_param("i", $group_id);
    
    if ($delete_stmt->execute()) {
        header("Location: homepage.php");
        exit;
    } else {
        die("Error: Could not delete the group.");
    }
    $delete_stmt->close();
} else {
    die("Access Denied: You do not have permission to delete this group.");
}

$stmt->close();
$conn->close();
?>