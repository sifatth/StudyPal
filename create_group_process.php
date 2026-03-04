<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("You must be logged in to create a group.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $group_name = trim($_POST['group_name']);
    $description = trim($_POST['group_desc']);
    $created_by = $_SESSION['user_id']; 

    if (empty($group_name) || empty($description)) {
        die("Group name and description are required.");
    }

    // group check
    $stmt_check = $conn->prepare("SELECT GroupID FROM studygroup WHERE GroupName = ?");
    $stmt_check->bind_param("s", $group_name);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        header("Location: homepage.php?error=duplicate_group");
        exit;
    }
    $stmt_check->close();

    // insert the new group
    $stmt = $conn->prepare("INSERT INTO studygroup (GroupName, Description, CreatedBy) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $group_name, $description, $created_by);
    
    if ($stmt->execute()) {
        // creator auto-join group
        $new_group_id = $conn->insert_id;

        $join_stmt = $conn->prepare("INSERT INTO groupmembership (UserID, GroupID) VALUES (?, ?)");
        $join_stmt->bind_param("ii", $created_by, $new_group_id);
        $join_stmt->execute();
        $join_stmt->close();
        
        header("Location: homepage.php");
        exit;
    } else {
        echo "Error: Could not create the group. " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>