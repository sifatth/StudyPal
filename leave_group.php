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

// Check if user is the creator of this group
$creator_check = $conn->prepare("SELECT CreatedBy FROM studygroup WHERE GroupID = ?");
$creator_check->bind_param("i", $group_id);
$creator_check->execute();
$group = $creator_check->get_result()->fetch_assoc();
$creator_check->close();

// If the creator is leaving, remove their creator status
if ($group && $group['CreatedBy'] == $user_id) {
    $update_stmt = $conn->prepare("UPDATE studygroup SET CreatedBy = NULL WHERE GroupID = ?");
    $update_stmt->bind_param("i", $group_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// delete membership
$delete_stmt = $conn->prepare("DELETE FROM groupmembership WHERE GroupID = ? AND UserID = ?");
$delete_stmt->bind_param("ii", $group_id, $user_id);
$delete_stmt->execute();
$delete_stmt->close();

header("Location: group_page.php?group_id=" . $group_id);
exit;
?>