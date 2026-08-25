<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("You must be logged in to edit material.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $material_id = isset($_POST['material_id']) ? (int)$_POST['material_id'] : 0;
    $group_id = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;
    $title = trim($_POST['material_title']);
    $user_id = $_SESSION['user_id'];

    if ($material_id <= 0 || $group_id <= 0 || empty($title)) {
        die("Invalid request. Title is required.");
    }

    // Verify ownership
    $stmt_check = $conn->prepare("SELECT UploadedBy FROM material WHERE MaterialID = ?");
    $stmt_check->bind_param("i", $material_id);
    $stmt_check->execute();
    $material = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if (!$material) {
        die("Content not found.");
    }

    if ($material['UploadedBy'] != $user_id) {
        die("Access Denied: You do not have permission to edit this content.");
    }

    // Update title
    $stmt_update = $conn->prepare("UPDATE material SET Title = ? WHERE MaterialID = ?");
    $stmt_update->bind_param("si", $title, $material_id);
    
    if ($stmt_update->execute()) {
        header("Location: group_page.php?group_id=" . $group_id);
        exit;
    } else {
        die("Error: Could not update the content title.");
    }
    $stmt_update->close();
    $conn->close();
}
?>
