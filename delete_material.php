<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}

$material_id = isset($_GET['material_id']) ? (int)$_GET['material_id'] : 0;
$group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($material_id <= 0 || $group_id <= 0) {
    die("Invalid request.");
}

// check user = uploader
$stmt = $conn->prepare("SELECT FilePath, UploadedBy FROM material WHERE MaterialID = ?");
$stmt->bind_param("i", $material_id);
$stmt->execute();
$result = $stmt->get_result();
$material = $result->fetch_assoc();
$stmt->close();

if ($material && $material['UploadedBy'] == $user_id) {
    // delete permanently
    if (!empty($material['FilePath'])) {
        if (file_exists($material['FilePath'])) {
            unlink($material['FilePath']);
        }
    }

    // delete from database
    $delete_stmt = $conn->prepare("DELETE FROM material WHERE MaterialID = ?");
    $delete_stmt->bind_param("i", $material_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    header("Location: group_page.php?group_id=" . $group_id);
    exit;

} else {
    die("Access Denied: You do not have permission to delete this material.");
}
?>