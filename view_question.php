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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($question['Title']); ?> - StudyPal</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap');
        body { font-family: 'DM Sans', sans-serif; font-weight: 300; background: linear-gradient(135deg, #f7faff 0%, #eef4ff 100%); margin: 0; padding-top: 74px; color: #14213d; }
        .container { max-width: 820px; margin: 28px auto 48px; background: rgba(255,255,255,0.96); padding: 32px; border-radius: 24px; border: 1px solid #e4ebf7; box-shadow: 0 10px 35px rgba(15,23,42,0.06); }
        .question-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; }
        .question-header h2 { margin: 0 0 10px 0; font-size: 1.7rem; color: #0f172a; }
        .question-meta { color: #64748b; font-size: 14px; margin-bottom: 20px; }
        .question-meta a { color: #2563eb; text-decoration: none; font-weight: 400; }
        .question-description { line-height: 1.7; border-bottom: 1px solid #eef2f7; padding-bottom: 18px; margin-bottom: 18px; color: #475569; }
        .section-title { font-size: 1.1rem; margin-bottom: 16px; color: #0f172a; }
        .answer { border: 1px solid #e4ebf7; padding: 16px; border-radius: 16px; margin-bottom: 14px; background: #fbfdff; }
        .answer-header { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .answer-meta { font-size: 14px; color: #64748b; font-weight: 400; margin-bottom: 10px; }
        .answer-meta a { color: #2563eb; text-decoration: none; }
        .answer-content { line-height: 1.7; color: #475569; }
        .form-container { background: linear-gradient(135deg, #f8fbff, #f2f7ff); border-radius: 16px; margin-top: 24px; padding: 16px; border: 1px solid #e4ebf7; }
        .form-container textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #dbe4f0; border-radius: 10px; box-sizing: border-box; min-height: 100px; font-family: 'DM Sans', sans-serif; font-weight: 300; }
        .form-container button { background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; padding: 10px 18px; border: none; border-radius: 999px; cursor: pointer; font-weight: 400; font-family: 'DM Sans', sans-serif; font-weight: 300; }
        .delete-btn { background: #fee2e2; color: #b91c1c; font-size: 12px; padding: 6px 10px; border-radius: 999px; text-decoration: none; font-weight: 400; }
        .report-btn { background: #fef3c7; color: #92400e; font-size: 12px; padding: 6px 10px; border-radius: 999px; text-decoration: none; font-weight: 400; flex-shrink: 0; border: none; cursor: pointer; }
        .popup-overlay { display: none; position: fixed; inset: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.55); justify-content: center; align-items: center; z-index: 1000; padding: 20px; }
        .popup-content { background: #fff; padding: 28px; border-radius: 18px; width: 100%; max-width: 420px; box-shadow: 0 16px 40px rgba(15,23,42,0.2); }
        .popup-content h2 { margin-top: 0; color: #0f172a; }
        .popup-content textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #dbe4f0; border-radius: 10px; box-sizing: border-box; font-family: 'DM Sans', sans-serif; font-weight: 300; font-size: 14px; }
        .popup-content button { background: linear-gradient(135deg, #16a34a, #22c55e); color: white; padding: 10px 15px; border: none; border-radius: 999px; cursor: pointer; font-family: 'DM Sans', sans-serif; font-weight: 300; font-size: 14px; font-weight: 400; }
        .popup-close { float: right; cursor: pointer; font-size: 24px; font-weight: 400; color: #64748b; }
        .report-options label { display: inline-flex; margin-right: 24px; margin-bottom: 10px; color: #334155; }
        .report-options input { margin-right: 8px; }
        .back-link { display: block; text-align: left; margin-top: 24px; font-weight: 400; font-size: 15px; color: #2563eb; text-decoration: none; }
        .action-menu-container { position: relative; display: inline-block; }
        .action-menu-btn { background: none; border: none; font-size: 18px; font-weight: 500; color: #64748b; cursor: pointer; padding: 0 8px; border-radius: 6px; line-height: 1; margin-top: 2px; letter-spacing: 1px; }
        .action-menu-btn:hover { background: #f1f5f9; }
        .action-menu-dropdown { display: none; position: absolute; right: 0; top: 100%; margin-top: 6px; background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(15,23,42,0.1); border: 1px solid #e4ebf7; overflow: hidden; z-index: 100; min-width: 110px; }
        .action-menu-dropdown.show { display: flex; flex-direction: column; }
        .action-menu-dropdown .report-btn, .action-menu-dropdown .delete-btn, .action-menu-dropdown .edit-btn { border-radius: 0; padding: 10px 16px; width: 100%; text-align: left; background: white; box-sizing: border-box; color: #0f172a; text-decoration: none; font-size: 14px; font-weight: 400; font-family: 'DM Sans', sans-serif; cursor: pointer; border: none; display: block; }
        .action-menu-dropdown .report-btn:hover { background: #fef3c7; color: #92400e; }
        .action-menu-dropdown .delete-btn:hover { background: #fee2e2; color: #b91c1c; }
        .action-menu-dropdown .edit-btn:hover { background: #eff6ff; color: #1d4ed8; }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <div class="container">
        <div id="raw-title" style="display:none;"><?php echo htmlspecialchars($question['Title']); ?></div>
        <div id="raw-description" style="display:none;"><?php echo htmlspecialchars($question['Description']); ?></div>
        
        <div class="question-header">
            <div>
                <h2><?php echo htmlspecialchars($question['Title']); ?></h2>
                <p class="question-meta">
                    Asked by: <a href="view_profile.php?user_id=<?php echo $question['AskedBy']; ?>"><?php echo htmlspecialchars($question['AskerName']); ?></a>
                </p>
            </div>
            <?php if ($is_asker): ?>
                <div class="action-menu-container">
                    <button class="action-menu-btn" onclick="toggleActionMenu(this)">•••</button>
                    <div class="action-menu-dropdown">
                        <button class="edit-btn" onclick="openEditQuestionPopup()">Edit</button>
                        <a href="delete_question.php?question_id=<?php echo $question_id; ?>&group_id=<?php echo $group_id; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this question and all its answers?');">Delete</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="action-menu-container">
                    <button class="action-menu-btn" onclick="toggleActionMenu(this)">•••</button>
                    <div class="action-menu-dropdown">
                        <button class="report-btn" onclick="openReportPopup(<?php echo $question_id; ?>, 0, <?php echo $question['AskedBy']; ?>)">Report</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <p class="question-description"><?php echo nl2br(htmlspecialchars($question['Description'])); ?></p>

        <h3 class="section-title">Answers (<?php echo count($answers); ?>)</h3>
        <div class="answers-list">
            <?php if (!empty($answers)): foreach ($answers as $answer): ?>
                <div class="answer">
                    <div class="raw-answer-content" style="display:none;"><?php echo htmlspecialchars($answer['Content']); ?></div>
                    <div class="answer-header">
                        <p class="answer-meta">
                            <a href="view_profile.php?user_id=<?php echo $answer['AnsweredBy']; ?>"><?php echo htmlspecialchars($answer['AnswererName']); ?></a> replied:
                        </p>
                        <?php if ($answer['AnsweredBy'] == $user_id): ?>
                            <div class="action-menu-container">
                                <button class="action-menu-btn" onclick="toggleActionMenu(this)">•••</button>
                                <div class="action-menu-dropdown">
                                    <button class="edit-btn" onclick="openEditAnswerPopup(this, <?php echo $answer['AnswerID']; ?>)">Edit</button>
                                    <a href="delete_answer.php?answer_id=<?php echo $answer['AnswerID']; ?>&question_id=<?php echo $question_id; ?>" class="delete-btn" onclick="return confirm('Are you sure?');">Delete</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="action-menu-container">
                                <button class="action-menu-btn" onclick="toggleActionMenu(this)">•••</button>
                                <div class="action-menu-dropdown">
                                    <button class="report-btn" onclick="openReportPopup(<?php echo $question_id; ?>, <?php echo $answer['AnswerID']; ?>, <?php echo $answer['AnsweredBy']; ?>)">Report</button>
                                </div>
                            </div>
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

        <!-- edit question popup -->
        <div class="popup-overlay" id="editQuestionPopup">
            <div class="popup-content">
                <span class="popup-close" onclick="closeEditQuestionPopup()">&times;</span>
                <h2>Edit Question</h2>
                <form action="edit_question_process.php" method="POST">
                    <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                    <label style="display:block; margin-bottom: 6px; font-weight: 500; text-align: left;">Title:</label>
                    <input type="text" name="title" id="editQuestionTitleInput" style="width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid #dbe4f0; border-radius: 10px; box-sizing: border-box; font-family: 'DM Sans', sans-serif;" required>
                    <label style="display:block; margin-bottom: 6px; font-weight: 500; text-align: left;">Description:</label>
                    <textarea name="description" id="editQuestionDescInput" rows="6" placeholder="Describe your question..." style="width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid #dbe4f0; border-radius: 10px; box-sizing: border-box; font-family: 'DM Sans', sans-serif; resize: vertical;"></textarea>
                    <button type="submit">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- edit answer popup -->
        <div class="popup-overlay" id="editAnswerPopup">
            <div class="popup-content">
                <span class="popup-close" onclick="closeEditAnswerPopup()">&times;</span>
                <h2>Edit Answer</h2>
                <form action="edit_answer_process.php" method="POST">
                    <input type="hidden" name="answer_id" id="editAnswerIdInput">
                    <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                    <label style="display:block; margin-bottom: 6px; font-weight: 500; text-align: left;">Your Answer:</label>
                    <textarea name="answer_content" id="editAnswerContentInput" rows="6" placeholder="Write your answer..." style="width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid #dbe4f0; border-radius: 10px; box-sizing: border-box; font-family: 'DM Sans', sans-serif; resize: vertical;" required></textarea>
                    <button type="submit">Save Changes</button>
                </form>
            </div>
        </div>

        <script>
            function toggleActionMenu(btn) {
                event.stopPropagation();
                const menu = btn.nextElementSibling;
                document.querySelectorAll('.action-menu-dropdown.show').forEach(m => {
                    if (m !== menu) m.classList.remove('show');
                });
                menu.classList.toggle('show');
            }
            document.addEventListener('click', function() {
                document.querySelectorAll('.action-menu-dropdown.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            });

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

            // Edit Question popup logic
            const editQuestionPopup = document.getElementById('editQuestionPopup');
            const editQuestionTitleInput = document.getElementById('editQuestionTitleInput');
            const editQuestionDescInput = document.getElementById('editQuestionDescInput');

            function openEditQuestionPopup() {
                editQuestionTitleInput.value = document.getElementById('raw-title').textContent;
                editQuestionDescInput.value = document.getElementById('raw-description').textContent;
                editQuestionPopup.style.display = 'flex';
            }

            function closeEditQuestionPopup() {
                editQuestionPopup.style.display = 'none';
            }

            // Edit Answer popup logic
            const editAnswerPopup = document.getElementById('editAnswerPopup');
            const editAnswerIdInput = document.getElementById('editAnswerIdInput');
            const editAnswerContentInput = document.getElementById('editAnswerContentInput');

            function openEditAnswerPopup(btn, answerId) {
                const answerContainer = btn.closest('.answer');
                const rawContent = answerContainer.querySelector('.raw-answer-content').textContent;
                editAnswerIdInput.value = answerId;
                editAnswerContentInput.value = rawContent;
                editAnswerPopup.style.display = 'flex';
            }

            function closeEditAnswerPopup() {
                editAnswerPopup.style.display = 'none';
            }
        </script>
        <a href="javascript:history.back()" class="back-link">&larr; Back</a>
    </div>
</body>
</html>