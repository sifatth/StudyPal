<?php
session_start();
require_once 'db_connect.php';

$question_id = isset($_GET['question_id']) ? (int)$_GET['question_id'] : 0;
$user_id = $_SESSION['user_id']; 
if ($question_id <= 0) die("Invalid question ID.");

// fetch question
$stmt = $conn->prepare("SELECT q.Title, q.Description, q.AskedBy, q.GroupID, up.Name AS AskerName FROM question AS q JOIN userprofile AS up ON q.AskedBy = up.UserID WHERE q.QuestionID = ?");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$question = $stmt->get_result()->fetch_assoc();
if (!$question) die("Question not found.");

$is_asker = ($user_id == $question['AskedBy']);
$group_id = $question['GroupID'];

// check user = group member
$member_check_stmt = $conn->prepare("SELECT 1 FROM groupmembership WHERE GroupID = ? AND UserID = ?");
$member_check_stmt->bind_param("ii", $group_id, $user_id);
$member_check_stmt->execute();
$is_member = $member_check_stmt->get_result()->num_rows > 0;
$member_check_stmt->close();
$stmt->close();

// fetch answers
$answers_stmt = $conn->prepare("SELECT a.AnswerID, a.Content, a.AnsweredBy, up.Name AS AnswererName FROM answer AS a JOIN userprofile AS up ON a.AnsweredBy = up.UserID WHERE a.QuestionID = ? ORDER BY a.PostedAt ASC");
$answers_stmt->bind_param("i", $question_id);
$answers_stmt->execute();
$answers = $answers_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($question['Title']); ?> - StudyPal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; margin: 0; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; padding: 50px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .question-header { display: flex; justify-content: space-between; align-items: flex-start; }
        .question-header h2 { margin: 0 0 10px 0; font-size: 28px; }
        .question-meta { color: #666; font-size: 14px; margin-bottom: 20px; }
        .question-meta a { color: #666; text-decoration: none; font-weight: 500; }
        .question-meta a:hover { text-decoration: none; }
        .question-description { line-height: 1.6; border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; margin-bottom: 20px; }
        .section-title { font-size: 22px; margin-bottom: 20px; }
        .answer { border: 1px solid #eee; padding: 20px; border-radius: 8px; margin-bottom: 15px; }
        .answer:last-child { border-bottom: none; }
        .answer-header { display: flex; justify-content: space-between; align-items: center; }
        .answer-meta { font-size: 14px; color: #555; font-weight: 500; margin-bottom: 10px; }
        .answer-meta a { color: #666; text-decoration: none; font-weight: 500; }
        .answer-meta a:hover { text-decoration: none; }
        .answer-content { line-height: 1.6; }
        .form-container { background-color: #f9f9f9; border-radius: 8px; margin-top: 30px; }
        .form-container textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; min-height: 100px; font-family: 'Inter', sans-serif; }
        .form-container button { background-color: #1877f2; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-family: 'Inter', sans-serif; }
        .delete-btn { background: #fee2e2; color: #ef4444; font-size: 12px; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-weight: bold; }
        .report-btn { background: #fffbeb; color: #d97706; font-size: 12px; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-weight: bold; flex-shrink: 0; border: none; cursor: pointer; }
        .popup-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .popup-content { background: #fff; padding: 30px; border-radius: 8px; width: 400px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .popup-content h2 { margin-top: 0; }
        .popup-content textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-family: Inter, sans-serif; font-size: 14px; }
        .popup-content button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-family: Inter, sans-serif; font-size: 14px; }
        .popup-close { float: right; cursor: pointer; font-size: 24px; font-weight: bold; }
        .report-options label { display: inline-flex; margin-right: 40px; margin-bottom: 10px; }
        .report-options input { margin-right: 8px; }
        .back-link { display: block; text-align: left; margin-top: 30px; font-weight: 500; font-size: 16px; color: #1877f2; text-decoration: none; }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <div class="container">
        <div class="question-header">
            <div>
                <h2><?php echo htmlspecialchars($question['Title']); ?></h2>
                <p class="question-meta">
                    Asked by: <a href="view_profile.php?user_id=<?php echo $question['AskedBy']; ?>"><?php echo htmlspecialchars($question['AskerName']); ?></a>
                </p>
            </div>
            <?php if ($is_asker): ?>
                <a href="delete_question.php?question_id=<?php echo $question_id; ?>&group_id=<?php echo $group_id; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this question and all its answers?');">Delete</a>
                <?php else: ?>
                    <button class="report-btn" onclick="openReportPopup(<?php echo $question_id; ?>, 0, <?php echo $question['AskedBy']; ?>)">Report</button>
                <?php endif; ?>
        </div>
        <p class="question-description"><?php echo nl2br(htmlspecialchars($question['Description'])); ?></p>

        <h3 class="section-title">Answers (<?php echo count($answers); ?>)</h3>
        <div class="answers-list">
            <?php if (!empty($answers)): foreach ($answers as $answer): ?>
                <div class="answer">
                    <div class="answer-header">
                        <p class="answer-meta">
                            <a href="view_profile.php?user_id=<?php echo $answer['AnsweredBy']; ?>"><?php echo htmlspecialchars($answer['AnswererName']); ?></a> replied:
                        </p>
                        <?php if ($answer['AnsweredBy'] == $user_id): ?>
                                <a href="delete_answer.php?answer_id=<?php echo $answer['AnswerID']; ?>&question_id=<?php echo $question_id; ?>" class="delete-btn" onclick="return confirm('Are you sure?');">Delete</a>
                            <?php else: ?>
                                <button class="report-btn" onclick="openReportPopup(<?php echo $question_id; ?>, <?php echo $answer['AnswerID']; ?>, <?php echo $answer['AnsweredBy']; ?>)">Report</button>
                            <?php endif; ?>
                    </div>
                    <p class="answer-content">
                        <?php echo nl2br(htmlspecialchars($answer['Content'])); ?>
                    </p>
                </div>
            <?php endforeach; else: ?>
                <p>No answers yet. Be the first to reply!</p>
            <?php endif; ?>
        </div>

        <?php if ($is_member): ?>
        <div class="form-container">
            <h3>Post Your Answer</h3>
            <form action="post_answer.php" method="POST">
                <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                <textarea name="answer_content" placeholder="Write your answer here..." required></textarea>
                <button type="submit">Submit Answer</button>
            </form>
        </div>
        <?php else: ?>
            <p style="margin-top: 20px; color: #333;">You must be a member of this group to post an answer.</p>
        <?php endif; ?>

        <!-- report popup -->
        <div class="popup-overlay" id="reportPopup">
            <div class="popup-content">
                <span class="popup-close" onclick="closeReportPopup()">&times;</span>
                <h2>Report Content</h2>
                <form action="report_process.php" method="POST">
                    <input type="hidden" name="question_id" id="reportQuestionId">
                    <input type="hidden" name="answer_id" id="reportAnswerId">
                    <input type="hidden" name="reported_user_id" id="reportUserId">
                    <textarea name="reason" rows="4" placeholder="Reason for reporting..." required></textarea>
                    <button type="submit">Submit</button>
                </form>
            </div>
        </div>

        <script>
            const reportPopup = document.getElementById('reportPopup');
            const reportQuestionIdInput = document.getElementById('reportQuestionId');
            const reportAnswerIdInput = document.getElementById('reportAnswerId');
            const reportUserIdInput = document.getElementById('reportUserId');

            function openReportPopup(questionId, answerId, reportedUserId) {
                reportQuestionIdInput.value = questionId;
                reportAnswerIdInput.value = answerId;
                reportUserIdInput.value = reportedUserId;
                reportPopup.style.display = 'flex';
            }

            function closeReportPopup() {
                reportPopup.style.display = 'none';
            }
        </script>
        <a href="javascript:history.back()" class="back-link">&larr; Back</a>
    </div>
</body>
</html>