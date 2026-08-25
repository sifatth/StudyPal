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
        die("Access Denied: Only group members can upload materials.");
    }
    $member_check_stmt->close();

    $title = trim($_POST['title']);
    $link_url = trim($_POST['link_url']);

    $file_path = null;
    $material_type = null;

    // file upload
    if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = uniqid() . '-' . basename($_FILES['material_file']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['material_file']['tmp_name'], $target_file)) {
            $file_path = $target_file;
            $material_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $link_url = null; 
        } else {
            die("Error: There was a problem uploading your file.");
        }
    } elseif (!empty($link_url)) {
        $material_type = 'Link';
        $file_path = null;
    }

    if (empty($title) || (empty($file_path) && empty($link_url))) {
        die("Error: Please provide a title and either a file or a link.");
    }

    // detect material type
    $stmt = $conn->prepare("INSERT INTO material (GroupID, UploadedBy, Title, FilePath, LinkURL, MaterialType) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $group_id, $user_id, $title, $file_path, $link_url, $material_type);
    
    if ($stmt->execute()) {
        header("Location: group_page.php?group_id=" . $group_id);
        exit;
    } else {
        die("Error: Could not save material to database. " . $stmt->error);
    }
}
?>