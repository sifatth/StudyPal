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

// delete membership
$delete_stmt = $conn->prepare("DELETE FROM groupmembership WHERE GroupID = ? AND UserID = ?");
$delete_stmt->bind_param("ii", $group_id, $user_id);
$delete_stmt->execute();
$delete_stmt->close();

header("Location: group_page.php?group_id=" . $group_id);
exit;
?>