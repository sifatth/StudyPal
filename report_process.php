<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reporter_id = $_SESSION['user_id'];
    $reported_user_id = isset($_POST['reported_user_id']) ? (int)$_POST['reported_user_id'] : 0;
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    $user_choice = isset($_POST['target_type']) ? $_POST['target_type'] : ''; 

    // get ids ; default 0
    $material_id = isset($_POST['material_id']) ? (int)$_POST['material_id'] : 0;
    $question_id = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;
    $answer_id = isset($_POST['answer_id']) ? (int)$_POST['answer_id'] : 0;
    
    // get groupid
    $group_id = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;

    if ($reported_user_id <= 0 || empty($reason)) {
        die("Invalid report request. A reason is required.");
    }

    // determine target
    $target_column = null;
    $target_id_value = null;
    $final_target_type = null;

    if ($user_choice == 'user') {
        $target_column = 'UserID';
        $target_id_value = $reported_user_id;
        $final_target_type = 'user';
    } else { 
        if ($material_id > 0) {
            $target_column = 'MaterialID';
            $target_id_value = $material_id;
            $final_target_type = 'material';
        } else if ($answer_id > 0) {
            $target_column = 'AnswerID';
            $target_id_value = $answer_id;
            $final_target_type = 'answer';
        } else if ($question_id > 0) {
            $target_column = 'QuestionID';
            $target_id_value = $question_id;
            $final_target_type = 'question';
        }
    }

    if ($target_id_value <= 0) {
        die("Invalid report: No valid content ID was provided.");
    }

    $conn->begin_transaction();

    try {
        // insert into target
        $sql_target = "INSERT INTO target ($target_column, Reason, TargetType) VALUES (?, ?, ?)";
        $stmt_target = $conn->prepare($sql_target);
        $stmt_target->bind_param("iss", $target_id_value, $reason, $final_target_type);
        $stmt_target->execute();
        
        $tid = $conn->insert_id;
        $stmt_target->close();

        // insert into report
        $stmt_report = $conn->prepare("INSERT INTO report (ReportedBy, TID, ReportedUserID) VALUES (?, ?, ?)");
        $stmt_report->bind_param("iii", $reporter_id, $tid, $reported_user_id);
        $stmt_report->execute();
        $stmt_report->close();

        $conn->commit();

        // redirect logic
        if ($question_id > 0) {
             // if the report came from a question page, go back there
             header("Location: view_question.php?question_id=" . $question_id . "&report=success");
        } else if ($group_id > 0) {
             // if the report came from a group page, go back there
             header("Location: group_page.php?group_id=" . $group_id . "&report=success");
        } else if ($final_target_type == 'user') {
             // if a user was reported, go back to their profile
             header("Location: view_profile.php?user_id=" . $reported_user_id . "&report=success");
        } else {
            header("Location: homepage.php");
        }
        exit;

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        die("Error: Could not submit your report. " . $exception->getMessage());
    }
}
?>