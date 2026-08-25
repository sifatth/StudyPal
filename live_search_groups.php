<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || !isset($_GET['query'])) {
    exit; 
}

$search_term = trim($_GET['query']);

// search groups
$sql = "SELECT `GroupID`, `GroupName`, `Description` 
        FROM `studygroup` 
        WHERE `IsActive` = 1 AND (`GroupName` LIKE ? OR `Description` LIKE ?)";

$like_term = "%" . $search_term . "%";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $like_term, $like_term);
$stmt->execute();
$result = $stmt->get_result();
$groups = $result->fetch_all(MYSQLI_ASSOC);

// HTML for the results and send it back
if (!empty($groups)) {
    foreach ($groups as $group) {
        echo "<div class='group-card'>";
        echo "<h3>" . htmlspecialchars($group['GroupName']) . "</h3>";
        echo "<p>Description: " . htmlspecialchars($group['Description']) . "</p>";
        echo "<a href='group_page.php?group_id=" . $group['GroupID'] . "' class='view-group-btn'>View Group</a>";
        echo "</div>";
    }
} else {
    echo "<div class='no-groups'>";
    echo "<h3>No study group found.</h3>";
    echo "<p>Try a different search or be the first to create one!</p>";
    echo "</div>";
}
?>