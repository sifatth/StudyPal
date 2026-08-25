<?php
session_start();
require_once 'db_connect.php';

// admin only
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Access Denied: You do not have permission to perform this action.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $report_id = (int)$_POST['report_id'];
    $action_type = $_POST['action_type'];
    $admin_id = $_SESSION['user_id'];

    // get report and target
    $stmt = $conn->prepare(
        "SELECT r.ReportedUserID, t.* FROM report r 
         JOIN target t ON r.TID = t.TID 
         WHERE r.ReportID = ?"
    );
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $target_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$target_info) {
        die("Error: Report not found.");
    }

    $conn->begin_transaction();
    try {
        // insert in admin action
        $log_stmt = $conn->prepare("INSERT INTO adminaction (AdminID, ReportID, ActionType) VALUES (?, ?, ?)");
        $log_stmt->bind_param("iis", $admin_id, $report_id, $action_type);
        $log_stmt->execute();
        $log_stmt->close();

        // resolved status update
        $update_report_stmt = $conn->prepare("UPDATE report SET Status = 'resolved' WHERE ReportID = ?");
        $update_report_stmt->bind_param("i", $report_id);
        $update_report_stmt->execute();
        $update_report_stmt->close();
        
        // delete, ban, ignore
        switch ($action_type) {
            case 'delete':
                $target_type = $target_info['TargetType'];
                $target_column = ucfirst($target_type) . 'ID';
                $target_id = $target_info[$target_column];
                $tid = $target_info['TID'];

                if ($target_id && $tid) {
                    $decouple_stmt = $conn->prepare("UPDATE `target` SET `{$target_column}` = NULL WHERE `TID` = ?");
                    $decouple_stmt->bind_param("i", $tid);
                    $decouple_stmt->execute();
                    $decouple_stmt->close();

                    // delete from material
                    if ($target_type === 'material') {
                        $file_stmt = $conn->prepare("SELECT FilePath FROM material WHERE MaterialID = ?");
                        $file_stmt->bind_param("i", $target_id);
                        $file_stmt->execute();
                        $file_result = $file_stmt->get_result()->fetch_assoc();
                        $file_stmt->close();

                        if ($file_result && !empty($file_result['FilePath'])) {
                            $filePath = $file_result['FilePath'];
                            if (file_exists($filePath)) {
                                unlink($filePath);      // Deletes the file
                            }
                        }
                    }

                    // delete permanently
                    $delete_stmt = $conn->prepare("DELETE FROM `{$target_type}` WHERE `{$target_column}` = ?");
                    $delete_stmt->bind_param("i", $target_id);
                    $delete_stmt->execute();
                    $delete_stmt->close();
                }
                break;

            case 'ban_user':
                $reported_user_id = $target_info['ReportedUserID'];
                $ban_stmt = $conn->prepare("UPDATE users SET IsActive = 0 WHERE UserID = ?");
                $ban_stmt->bind_param("i", $reported_user_id);
                $ban_stmt->execute();
                $ban_stmt->close();
                break;
            
            case 'ignore':
                break;
        }
        $conn->commit();

    } catch (mysqli_sql_exception $exception) {
        // action failure check
        $conn->rollback();
        die("Error processing action: " . $exception->getMessage());
    }

    header("Location: admin_dashboard.php");
    exit;
}
?>