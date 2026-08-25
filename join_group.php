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

// check if user is already a member
$check_stmt = $conn->prepare("SELECT MembershipID FROM groupmembership WHERE GroupID = ? AND UserID = ?");
$check_stmt->bind_param("ii", $group_id, $user_id);
$check_stmt->execute();
$is_already_member = $check_stmt->get_result()->num_rows > 0;
$check_stmt->close();

if (!$is_already_member) {
    $insert_stmt = $conn->prepare("INSERT INTO groupmembership (UserID, GroupID) VALUES (?, ?)");
    $insert_stmt->bind_param("ii", $user_id, $group_id);
    $insert_stmt->execute();
    $insert_stmt->close();
}

header("Location: group_page.php?group_id=" . $group_id);
exit;
?>