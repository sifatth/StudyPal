<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || !isset($_GET['group_id']) || !isset($_GET['query'])) {
    exit;
}

$group_id = (int)$_GET['group_id'];
$search_term = trim($_GET['query']);
$user_id = $_SESSION['user_id'];

// search materials
$sql = "SELECT MaterialID, Title, FilePath, LinkURL, UploadedBy 
        FROM material 
        WHERE GroupID = ? AND Title LIKE ?";

$like_term = "%" . $search_term . "%";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $group_id, $like_term);
$stmt->execute();
$result = $stmt->get_result();
$materials = $result->fetch_all(MYSQLI_ASSOC);

if (!empty($materials)) {
    foreach ($materials as $material) {
        $link = htmlspecialchars($material['FilePath'] ?: $material['LinkURL']);
        $title = htmlspecialchars($material['Title']);
        $material_id = $material['MaterialID'];
        
        echo "<li>";
        echo "<a href='{$link}' target='_blank' class='material-title'>{$title}</a>";
        
        if ($material['UploadedBy'] == $user_id) {
            echo "<a href='delete_material.php?material_id={$material_id}&group_id={$group_id}' class='delete-material-btn' onclick=\"return confirm('Are you sure?');\">Delete</a>";
        }
        echo "</li>";
    }
} else {
    echo "<li>No materials found matching your search.</li>";
}
?>