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

// Groups where creator has left
$orphaned_groups = $conn->query("SELECT sg.GroupID, sg.GroupName, sg.Description, COUNT(gm.UserID) AS MemberCount FROM studygroup AS sg LEFT JOIN groupmembership AS gm ON sg.GroupID = gm.GroupID WHERE sg.CreatedBy IS NULL GROUP BY sg.GroupID ORDER BY sg.GroupName ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StudyPal</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap');
        body { font-family: 'DM Sans', sans-serif; font-weight: 300; background: linear-gradient(135deg, #f7faff 0%, #eef4ff 100%); margin: 0; padding-top: 74px; color: #14213d; }
        .container { max-width: 860px; margin: 28px auto 48px; background: rgba(255,255,255,0.96); padding: 32px; border-radius: 24px; border: 1px solid #e4ebf7; box-shadow: 0 10px 35px rgba(15,23,42,0.06); }
        .container h1 { margin-top: 0; margin-bottom: 24px; color: #0f172a; }
        .report-card { border: 1px solid #e4ebf7; border-radius: 18px; margin-bottom: 18px; padding: 20px; background: #ffffff; box-shadow: 0 6px 16px rgba(15,23,42,0.04); }
        .report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eef2f7; padding-bottom: 10px; margin-bottom: 10px; gap: 12px; flex-wrap: wrap; }
        .report-meta { font-size: 14px; color: #64748b; margin-top: 6px; }
        .report-reason { margin: 15px 0; color: #334155; }
        .reported-content { background: linear-gradient(135deg, #f8fbff, #f3f8ff); border-radius: 12px; padding: 15px; margin-top: 15px; border: 1px solid #e4ebf7; }
        .reported-content h4 { margin-top: 0; margin-bottom: 10px; color: #0f172a; }
        .reported-content p { margin: 0; line-height: 1.5; color: #475569; }
        .reported-content a { color: #2563eb; }
        .action-form button { margin-right: 10px; padding: 8px 12px; border-radius: 999px; border: none; cursor: pointer; font-weight: 400; margin-top: 10px; }
        .btn-delete { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
        .btn-ban { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }
        .btn-ignore { background: linear-gradient(135deg, #64748b, #475569); color: white; }
        .status-badge { padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 400; text-transform: uppercase; color: #92400e; background: #fef3c7; margin-left: 8px; }
        .status-resolved { background: #dcfce7; color: #166534; }
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

    <?php if (!empty($orphaned_groups)): ?>
    <div class="container" style="margin-top: 0;">
        <h1>Orphaned Groups <span style="font-size: 14px; font-weight: 300; color: #64748b;">(Creator left)</span></h1>
        <?php foreach ($orphaned_groups as $og): ?>
            <div class="report-card">
                <div class="report-header">
                    <div>
                        <strong><?php echo htmlspecialchars($og['GroupName']); ?></strong>
                        <p class="report-meta"><?php echo $og['MemberCount']; ?> member(s)</p>
                    </div>
                    <a href="delete_group.php?group_id=<?php echo $og['GroupID']; ?>" class="btn-delete" style="padding: 8px 16px; border-radius: 999px; text-decoration: none; font-size: 14px;" onclick="return confirm('Are you sure you want to delete this group?');">Delete Group</a>
                </div>
                <?php if (!empty($og['Description'])): ?>
                    <p style="color: #475569; margin: 8px 0 0;"><?php echo htmlspecialchars($og['Description']); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</body>
</html>