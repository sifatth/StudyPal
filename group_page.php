<?php
session_start();
require_once 'db_connect.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
$user_id = $_SESSION['user_id'];
if ($group_id <= 0) die("Invalid group ID.");

// group details
$stmt = $conn->prepare("SELECT sg.GroupName, sg.Description, sg.CreatedBy, up.Name AS CreatorName FROM studygroup AS sg JOIN userprofile AS up ON sg.CreatedBy = up.UserID WHERE sg.GroupID = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();
if (!$group) die("Group not found.");

$is_creator = ($user_id == $group['CreatedBy']);
$member_check_stmt = $conn->prepare("SELECT MembershipID FROM groupmembership WHERE GroupID = ? AND UserID = ?");
$member_check_stmt->bind_param("ii", $group_id, $user_id);
$member_check_stmt->execute();
$is_member = $member_check_stmt->get_result()->num_rows > 0;

// group page tabs
$materials = $conn->query("SELECT MaterialID, Title, FilePath, LinkURL, UploadedBy FROM material WHERE GroupID = $group_id ORDER BY UploadedAt DESC")->fetch_all(MYSQLI_ASSOC);
$questions = $conn->query("SELECT QuestionID, Title FROM question WHERE GroupID = $group_id ORDER BY PostedAt DESC")->fetch_all(MYSQLI_ASSOC);
$members = $conn->query("SELECT up.UserID, up.Name FROM groupmembership AS gm JOIN userprofile AS up ON gm.UserID = up.UserID WHERE gm.GroupID = $group_id ORDER BY up.Name ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($group['GroupName']); ?> - StudyPal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; margin: 0; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .group-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding: 10px; }
        .header-actions { display: flex; align-items: center; flex-shrink: 0; }
        .group-header h2 { margin: 0 0 10px 0; font-size: 32px; }
        .group-meta { font-size: 14px; margin-bottom: 10px; }
        .group-meta a { font-weight: 500; text-decoration: none; color: #333; }
        .group-description { line-height: 1.6; margin-bottom: 10px; }
        .action-btn { padding: 10px 15px; border: none; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: bold; cursor: pointer; white-space: nowrap; }
        .leave-btn { background-color: #ffc107; color: #333; }
        .join-btn { background-color: #1877f2; color: white; }
        .delete-btn { background-color: #dc3545; color: white; }
        .tabs { display: flex; border-bottom: 2px solid #eee; margin-top: 30px; }
        .tab-link { padding: 10px 20px; cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; font-weight: 500; color: #555; }
        .tab-link.active { color: #1877f2; border-bottom-color: #1877f2; }
        .tab-content { display: none; padding-top: 20px; }
        .tab-content.active { display: block; }
        .section-header { display: flex; justify-content: space-between; align-items: center; padding: 10px; padding-bottom: 10px; margin-bottom: 20px; }
        .section-title { font-size: 22px; margin: 0; }
        .content-list { list-style: none; padding: 0; }
        .content-list li { padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .content-list a { text-decoration: none; font-weight: 500; color: #333; }
        .delete-material-btn { background: #f3efefff; color: #ef4444; font-size: 12px; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-weight: bold; }
        .form-container { display: flex; flex-direction: column; justify-content: space-between; background-color: #f9f9f9; padding: 10px; padding-top: 0px; margin-bottom: 20px; }
        .form-container input, .form-container textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        .form-container button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-family: 'Inter', sans-serif; font-weight: 600; }
        .search-container { max-width: 300px; width: 100%; font-family: 'Inter', sans-serif; }
        .search-input { width: 100%; padding: 10px; font-size: 14px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .back-link { display: block; text-align: left; margin-left: 10px; margin-top: 30px; font-weight: 500; font-size: 16px; color: #1877f2; text-decoration: none; }
        .report-btn { background: #fffbeb; color: #d97706; font-size: 12px; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-weight: bold; flex-shrink: 0; border: none; cursor: pointer; }
        .popup-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .popup-content { background: #fff; padding: 30px; border-radius: 8px; width: 400px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .popup-content h2 { margin-top: 0; }
        .popup-content textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-family: Inter, sans-serif; font-size: 14px; }
        .popup-content button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-family: Inter, sans-serif; font-size: 14px; }
        .popup-close { float: right; cursor: pointer; font-size: 24px; font-weight: bold; }
        .report-options label { display: inline-flex; margin-right: 40px; margin-bottom: 10px; }
   </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <div class="container">
        <div class="group-header">
            <div>
                <h2><?php echo htmlspecialchars($group['GroupName']); ?></h2>
                <p class="group-description"><?php echo nl2br(htmlspecialchars($group['Description'])); ?></p>
                <p class="group-meta">Created by: <a href="view_profile.php?user_id=<?php echo $group['CreatedBy']; ?>"><?php echo htmlspecialchars($group['CreatorName']); ?></a></p>
            </div>
            <div class="header-actions">
                <?php if ($is_member && !$is_creator): ?>
                    <a href="leave_group.php?group_id=<?php echo $group_id; ?>" class="action-btn leave-btn">Leave Group</a>
                <?php elseif (!$is_member): ?>
                    <a href="join_group.php?group_id=<?php echo $group_id; ?>" class="action-btn join-btn">Join Group</a>
                <?php endif; ?>
                <?php if ($is_creator): ?>
                    <a href="delete_group.php?group_id=<?php echo $group_id; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure?');">Delete Group</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- tabs -->
        <nav class="tabs">
            <div class="tab-link active" onclick="openTab(event, 'qa')">Q&A</div>
            <div class="tab-link" onclick="openTab(event, 'materials')">Contents</div>
            <div class="tab-link" onclick="openTab(event, 'members')">Members</div>
        </nav>

        <!-- Q&A tab -->
        <div id="qa" class="tab-content active">
            <div class="section-header">
                <h3 class="section-title">Q&A</h3>
                <div class="search-container">
                    <input type="text" id="questionSearchInput" class="search-input" placeholder="Search questions...">
                </div>
            </div>
            <ul class="content-list" id="questionList">
                <?php foreach ($questions as $question): ?>
                    <li><a href="view_question.php?question_id=<?php echo $question['QuestionID']; ?>"><?php echo htmlspecialchars($question['Title']); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php 
            if ($is_member): ?>
            <div class="form-container">
                <h4>Ask a New Question</h4>
                <form action="post_question.php" method="POST">
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    <input type="text" name="title" placeholder="Question Title" required>
                    <textarea name="description" rows="3" placeholder="Describe your question..."></textarea>
                    <button type="submit">Post</button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- contents tab -->
        <div id="materials" class="tab-content">
            <div class="section-header">
                <h3 class="section-title">Contents</h3>
                <div class="search-container">
                    <input type="text" id="materialSearchInput" class="search-input" placeholder="Search contents...">
                </div>
            </div>
            <ul class="content-list" id="materialList">
                <?php foreach ($materials as $material): ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($material['FilePath'] ?: $material['LinkURL']); ?>" target="_blank" class="material-title"><?php echo htmlspecialchars($material['Title']); ?></a>
                        
                        <!-- actions -->
                        <div style="display: flex; gap: 10px;">
                            <?php if ($material['UploadedBy'] == $user_id): ?>
                                <a href="delete_material.php?material_id=<?php echo $material['MaterialID']; ?>&group_id=<?php echo $group_id; ?>" class="delete-material-btn" onclick="return confirm('Are you sure?');">Delete</a>
                            <?php else: ?>
                                <button class="report-btn" onclick="openReportPopup(<?php echo $material['MaterialID']; ?>, <?php echo $material['UploadedBy']; ?>)">Report</button>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php
            if ($is_member): ?>
            <div class="form-container">
                <h4>Share a New Content</h4>
                <form action="upload_material.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    <input type="text" name="title" placeholder="Title of the content" required>
                    <input type="text" name="link_url" placeholder="Paste a link...">
                    <label style="margin-bottom: 10px; display: block;">Or upload a file:</label>
                    <input type="file" name="material_file">
                    <button type="submit">Share</button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- members tab -->
        <div id="members" class="tab-content">
            <div class="section-header">
                <h3 class="section-title">Members (<?php echo count($members); ?>)</h3>
                <div class="search-container">
                    <input type="text" id="memberSearchInput" class="search-input" placeholder="Search members...">
                </div>
            </div>
            <ul class="content-list" id="memberList">
                <?php foreach ($members as $member): ?>
                    <li><a href="view_profile.php?user_id=<?php echo $member['UserID']; ?>"><?php echo htmlspecialchars($member['Name']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <a href="javascript:history.back()" class="back-link">&larr; Back</a>
    </div>

    <!-- report popup -->
    <div class="popup-overlay" id="reportPopup">
        <div class="popup-content">
            <span class="popup-close" onclick="closeReportPopup()">&times;</span>
            <h2>Report Content</h2>
            <p>Please select what you are reporting and provide a reason.</p>
            <form action="report_process.php" method="POST">
                <input type="hidden" name="material_id" id="reportMaterialId">
                <input type="hidden" name="reported_user_id" id="reportUserId">
                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                
                <div class="report-options">
                    <label><input type="radio" name="target_type" value="material" checked> Material</label>
                    <label><input type="radio" name="target_type" value="user"> Uploader</label>
                </div>
                <textarea name="reason" rows="4" placeholder="Reason for reporting..." required></textarea>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            const tabcontent = document.getElementsByClassName("tab-content");
            for (let i = 0; i < tabcontent.length; i++) { tabcontent[i].style.display = "none"; }
            const tablinks = document.getElementsByClassName("tab-link");
            for (let i = 0; i < tablinks.length; i++) { tablinks[i].className = tablinks[i].className.replace(" active", ""); }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        const groupId = <?php echo $group_id; ?>;

        // member search
        const memberSearchInput = document.getElementById('memberSearchInput');
        const memberList = document.getElementById('memberList');
        memberSearchInput.addEventListener('keyup', () => fetchResults('member', memberSearchInput.value, memberList));
        memberList.addEventListener('click', (e) => logSearchOnClick(e, 'A', memberSearchInput, 'member'));

        // material search
        const materialSearchInput = document.getElementById('materialSearchInput');
        const materialList = document.getElementById('materialList');
        materialSearchInput.addEventListener('keyup', () => fetchResults('material', materialSearchInput.value, materialList));
        materialList.addEventListener('click', (e) => logSearchOnClick(e, 'A', materialSearchInput, 'material'));

        // question search
        const questionSearchInput = document.getElementById('questionSearchInput');
        const questionList = document.getElementById('questionList');
        questionSearchInput.addEventListener('keyup', () => fetchResults('question', questionSearchInput.value, questionList));
        questionList.addEventListener('click', (e) => logSearchOnClick(e, 'A', questionSearchInput, 'question'));

        // reusable functions
        function fetchResults(type, query, listElement) {
            fetch(`live_search_${type}s.php?group_id=${groupId}&query=${query}`)
                .then(response => response.text())
                .then(data => { listElement.innerHTML = data; })
                .catch(error => console.error('Error:', error));
        }

        function logSearchOnClick(event, targetTag, searchInput, searchType) {
            if (event.target.tagName === targetTag) {
                const searchTerm = searchInput.value;
                if (searchTerm.trim() !== '') {
                    const formData = new FormData();
                    formData.append('search_term', searchTerm);
                    formData.append('search_type', searchType);
                    fetch('log_search_query.php', { method: 'POST', body: formData });
                }
            }
        }

        // js for report popup
        const reportPopup = document.getElementById('reportPopup');
        const reportMaterialIdInput = document.getElementById('reportMaterialId');
        const reportUserIdInput = document.getElementById('reportUserId');

        function openReportPopup(materialId, reportedUserId) {
            reportMaterialIdInput.value = materialId;
            reportUserIdInput.value = reportedUserId;
            reportPopup.style.display = 'flex';
        }

        function closeReportPopup() {
            reportPopup.style.display = 'none';
        }
    </script>
</body>
</html>