<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    exit;
}

$user_id = $_SESSION['user_id'];
$search_term = trim($_POST['search_term']);
$search_type = trim($_POST['search_type']);

$valid_types = ['member', 'group', 'material', 'question'];

if (!empty($search_term) && in_array($search_type, $valid_types)) {
    $log_stmt = $conn->prepare("INSERT INTO searchhistory (UserID, SearchTerm, SearchType) VALUES (?, ?, ?)");
    $log_stmt->bind_param("iss", $user_id, $search_term, $search_type);
    $log_stmt->execute();
    $log_stmt->close();
}
?>