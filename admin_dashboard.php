<?php
session_start();
require_once 'db_connect.php';

// admin only
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Access Denied: You do not have permission to view this page.");
}

$sql = "SELECT 
            r.ReportID, 
            r.Status,
            t.Reason, 
            reporter.Name AS ReporterName,
            reported.Name AS ReportedUserName,
            t.TargetType,
            m.Title AS MaterialTitle, m.LinkURL AS MaterialLink,
            q.Title AS QuestionTitle, q.Description AS QuestionDescription,
            a.Content AS AnswerContent
        FROM report AS r
        JOIN `target` AS t ON r.TID = t.TID
        JOIN userprofile AS reporter ON r.ReportedBy = reporter.UserID
        JOIN userprofile AS reported ON r.ReportedUserID = reported.UserID
        LEFT JOIN material AS m ON t.MaterialID = m.MaterialID
        LEFT JOIN question AS q ON t.QuestionID = q.QuestionID
        LEFT JOIN answer AS a ON t.AnswerID = a.AnswerID
        ORDER BY FIELD(r.Status, 'pending', 'resolved'), r.ReportTime ASC";

$reports = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - StudyPal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; margin: 0; padding-top: 70px; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; padding: 40px; border-radius: 12px; }
        .report-card { border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px; padding: 20px; }
        .report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; }
        .report-meta { font-size: 14px; color: #555; }
        .report-reason { margin: 15px 0; }
        .reported-content { background-color: #f9f9f9; border-radius: 6px; padding: 15px; margin-top: 15px; }
        .reported-content h4 { margin-top: 0; margin-bottom: 10px; color: #333; }
        .reported-content p { margin: 0; line-height: 1.5; }
        .reported-content a { color: #1877f2; }
        .action-form button { margin-right: 10px; padding: 8px 12px; border-radius: 5px; border: none; cursor: pointer; font-weight: bold; }
        .btn-delete { background-color: #dc3545; color: white; font-family: 'Inter', sans-serif; }
        .btn-ban { background-color: #6f42c1; color: white; font-family: 'Inter', sans-serif; }
        .btn-ignore { background-color: #6c757d; color: white; font-family: 'Inter', sans-serif; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; text-transform: uppercase; color: #333; background-color: #ffc107; margin-left: 10px; }
        .status-resolved { background-color: #28a745; color: white; }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>
    <div class="container">
        <h1>Admin Dashboard - All Reports</h1>
        <?php if (!empty($reports)): foreach ($reports as $report): ?>
            <div class="report-card">
                <div class="report-header">
                    <div>
                        <strong>Report #<?php echo $report['ReportID']; ?></strong>
                        <?php 
                        $statusClass = $report['Status'] === 'resolved' ? 'status-resolved' : '';
                        ?>
                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($report['Status']); ?></span>
                        <p class="report-meta">
                            Reported User: <?php echo htmlspecialchars($report['ReportedUserName']); ?> | 
                            Reported By: <?php echo htmlspecialchars($report['ReporterName']); ?>
                        </p>
                    </div>
                    <span>Target Type: <?php echo ucfirst($report['TargetType']); ?></span>
                </div>
                <div class="report-reason">
                    <strong>Reason:</strong>
                    <p><?php echo htmlspecialchars($report['Reason']); ?></p>
                </div>
                
                <div class="reported-content">
                    <h4>Reported Content:</h4>
                    <?php
                    switch ($report['TargetType']) {
                        case 'material':
                            echo "<p><strong>Title:</strong> " . htmlspecialchars($report['MaterialTitle']) . "</p>";
                            if (!empty($report['MaterialLink'])) {
                                echo "<p><strong>Link:</strong> <a href='" . htmlspecialchars($report['MaterialLink']) . "' target='_blank'>" . htmlspecialchars($report['MaterialLink']) . "</a></p>";
                            }
                            break;
                        case 'question':
                            echo "<p><strong>Title:</strong> " . htmlspecialchars($report['QuestionTitle']) . "</p>";
                            echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($report['QuestionDescription'])) . "</p>";
                            break;
                        case 'answer':
                            echo "<p>" . nl2br(htmlspecialchars($report['AnswerContent'])) . "</p>";
                            break;
                    }
                    ?>
                </div>

                <?php 
                if ($report['Status'] === 'pending'): ?>
                    <div class="action-form">
                        <form action="admin_action_process.php" method="POST" style="display: inline;">
                            <input type="hidden" name="report_id" value="<?php echo $report['ReportID']; ?>">
                            <button type="submit" name="action_type" value="delete" class="btn-delete">Delete Content</button>
                            <button type="submit" name="action_type" value="ban_user" class="btn-ban">Ban User</button>
                            <button type="submit" name="action_type" value="ignore" class="btn-ignore">Ignore</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p style="font-style: italic; color: #555;">This report has been resolved.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; else: ?>
            <p>No reports found.</p>
        <?php endif; ?>
    </div>
</body>
</html>