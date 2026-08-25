<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("You must be logged in to edit a group.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $group_id = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;
    $group_name = trim($_POST['group_name']);
    $description = trim($_POST['group_desc']);
    $user_id = $_SESSION['user_id']; 

    if ($group_id <= 0 || empty($group_name) || empty($description)) {
        die("Invalid request. Group name and description are required.");
    }

    // Verify ownership/creator privilege
    $stmt_owner = $conn->prepare("SELECT CreatedBy FROM studygroup WHERE GroupID = ?");
    $stmt_owner->bind_param("i", $group_id);
    $stmt_owner->execute();
    $group = $stmt_owner->get_result()->fetch_assoc();
    $stmt_owner->close();

    if (!$group) {
        die("Group not found.");
    }

    if ($group['CreatedBy'] != $user_id) {
        die("Access Denied: You do not have permission to edit this group.");
    }

    // Check if the new group name is already taken by ANOTHER group
    $stmt_check = $conn->prepare("SELECT GroupID FROM studygroup WHERE GroupName = ? AND GroupID != ?");
    $stmt_check->bind_param("si", $group_name, $group_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        header("Location: edit_group.php?group_id=" . $group_id . "&error=duplicate_name");
        exit;
    }
    $stmt_check->close();

    // Update studygroup details
    $stmt_update = $conn->prepare("UPDATE studygroup SET GroupName = ?, Description = ? WHERE GroupID = ?");
    $stmt_update->bind_param("ssi", $group_name, $description, $group_id);
    
    if ($stmt_update->execute()) {
        header("Location: group_page.php?group_id=" . $group_id);
        exit;
    } else {
        echo "Error: Could not update the group. " . $stmt_update->error;
    }
    $stmt_update->close();
    $conn->close();
}
?>
