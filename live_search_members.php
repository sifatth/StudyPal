<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || !isset($_GET['group_id']) || !isset($_GET['query'])) {
    exit; 
}

$group_id = (int)$_GET['group_id'];
$search_term = trim($_GET['query']);

// search members
$sql = "SELECT up.UserID, up.Name 
        FROM groupmembership AS gm
        JOIN userprofile AS up ON gm.UserID = up.UserID
        WHERE gm.GroupID = ? AND up.Name LIKE ?";

$like_term = "%" . $search_term . "%";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $group_id, $like_term);
$stmt->execute();
$result = $stmt->get_result();
$members = $result->fetch_all(MYSQLI_ASSOC);

// HTML for the results and send it back
if (!empty($members)) {
    foreach ($members as $member) {
        echo "<li><a href='view_profile.php?user_id=" . $member['UserID'] . "'>" . htmlspecialchars($member['Name']) . "</a></li>";
    }
} else {
    echo "<li>No members found matching your search.</li>";
}
?>