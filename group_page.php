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
$stmt = $conn->prepare("SELECT sg.GroupName, sg.Description, sg.CreatedBy, sg.OriginalCreatorID, up.Name AS CreatorName FROM studygroup AS sg LEFT JOIN userprofile AS up ON sg.OriginalCreatorID = up.UserID WHERE sg.GroupID = ?");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($group['GroupName']); ?> - StudyPal</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap');
        body { font-family: 'DM Sans', sans-serif; font-weight: 300; background: linear-gradient(135deg, #f7faff 0%, #eef4ff 100%); margin: 0; padding-top: 74px; color: #14213d; }
        .container { max-width: 840px; margin: 28px auto 48px; background: rgba(255,255,255,0.96); padding: 32px; border-radius: 24px; border: 1px solid #e4ebf7; box-shadow: 0 10px 35px rgba(15,23,42,0.06); }
        .group-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding: 8px 4px; gap: 20px; flex-wrap: wrap; }
        .header-actions { display: flex; align-items: center; flex-shrink: 0; gap: 10px; flex-wrap: wrap; }
        .group-header h2 { margin: 0 0 10px 0; font-size: 1.8rem; }
        .group-meta { font-size: 14px; margin-bottom: 10px; color: #64748b; }
        .group-meta a { font-weight: 400; text-decoration: none; color: #2563eb; }
        .group-description { line-height: 1.6; margin-bottom: 10px; color: #475569; }
        .action-btn { padding: 10px 15px; border: none; border-radius: 999px; text-decoration: none; font-size: 14px; font-weight: 400; cursor: pointer; white-space: nowrap; }
        .leave-btn { background: linear-gradient(135deg, #fde68a, #f59e0b); color: #78350f; }
        .join-btn { background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; }
        .delete-btn { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
        .tabs { display: flex; border-bottom: 2px solid #e5ecf7; margin-top: 28px; gap: 6px; }
        .tab-link { padding: 10px 16px; cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; font-weight: 400; color: #64748b; }
        .tab-link.active { color: #2563eb; border-bottom-color: #2563eb; }
        .tab-content { display: none; padding-top: 20px; }
        .tab-content.active { display: block; }
        .section-header { display: flex; justify-content: space-between; align-items: center; padding: 8px 2px; margin-bottom: 16px; gap: 12px; flex-wrap: wrap; }
        .section-title { font-size: 1.1rem; margin: 0; color: #0f172a; }
        .content-list { list-style: none; padding: 0; margin: 0; }
        .content-list li { padding: 12px 0; border-bottom: 1px solid #eef2f7; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        .content-list a { text-decoration: none; font-weight: 400; color: #0f172a; }
        .delete-material-btn { background: #fee2e2; color: #b91c1c; font-size: 12px; padding: 6px 10px; border-radius: 999px; text-decoration: none; font-weight: 400; }
        .form-container { display: flex; flex-direction: column; justify-content: space-between; background: linear-gradient(135deg, #f8fbff, #f2f7ff); padding: 16px; margin-top: 18px; margin-bottom: 20px; border-radius: 16px; border: 1px solid #e4ebf7; }
        .form-container input, .form-container textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #dbe4f0; border-radius: 10px; box-sizing: border-box; font-family: 'DM Sans', sans-serif; font-weight: 300; }
        .form-container button { background: linear-gradient(135deg, #16a34a, #22c55e); color: white; padding: 10px 15px; border: none; border-radius: 999px; cursor: pointer; font-family: 'DM Sans', sans-serif; font-weight: 300; font-weight: 400; }
        .search-container { max-width: 300px; width: 100%; font-family: 'DM Sans', sans-serif; font-weight: 300; }
        .search-input { width: 100%; padding: 10px 12px; font-size: 14px; border: 1px solid #dbe4f0; border-radius: 999px; box-sizing: border-box; outline: none; }
        .search-input:focus { border-color: #60a5fa; box-shadow: 0 0 0 4px rgba(96,165,250,0.16); }
        .back-link { display: block; text-align: left; margin-left: 2px; margin-top: 24px; font-weight: 400; font-size: 15px; color: #2563eb; text-decoration: none; }
        .report-btn { background: #fef3c7; color: #92400e; font-size: 12px; padding: 6px 10px; border-radius: 999px; text-decoration: none; font-weight: 400; flex-shrink: 0; border: none; cursor: pointer; }
        .popup-overlay { display: none; position: fixed; inset: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.55); justify-content: center; align-items: center; z-index: 1000; padding: 20px; }
        .popup-content { background: #fff; padding: 28px; border-radius: 18px; width: 100%; max-width: 420px; box-shadow: 0 16px 40px rgba(15,23,42,0.2); }
        .popup-content h2 { margin-top: 0; color: #0f172a; }
        .popup-content textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #dbe4f0; border-radius: 10px; box-sizing: border-box; font-family: 'DM Sans', sans-serif; font-weight: 300; font-size: 14px; }
        .popup-content button { background: linear-gradient(135deg, #16a34a, #22c55e); color: white; padding: 10px 15px; border: none; border-radius: 999px; cursor: pointer; font-family: 'DM Sans', sans-serif; font-weight: 300; font-size: 14px; font-weight: 400; }
        .popup-close { float: right; cursor: pointer; font-size: 24px; font-weight: 400; color: #64748b; }
        .report-options label { display: inline-flex; margin-right: 24px; margin-bottom: 10px; color: #334155; }
        .action-menu-container { position: relative; display: inline-block; }
        .action-menu-btn { background: none; border: none; font-size: 18px; font-weight: 500; color: #64748b; cursor: pointer; padding: 0 8px; border-radius: 6px; line-height: 1; margin-top: 2px; letter-spacing: 1px; }
        .action-menu-btn:hover { background: #f1f5f9; }
        .action-menu-dropdown { display: none; position: absolute; right: 0; top: 100%; margin-top: 6px; background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(15,23,42,0.1); border: 1px solid #e4ebf7; overflow: hidden; z-index: 100; min-width: 110px; }
        .action-menu-dropdown.show { display: flex; flex-direction: column; }
        .action-menu-dropdown .report-btn, .action-menu-dropdown .delete-material-btn, .action-menu-dropdown .delete-btn, .action-menu-dropdown .leave-btn, .action-menu-dropdown .edit-group-btn, .action-menu-dropdown .edit-material-btn { border-radius: 0; padding: 10px 16px; width: 100%; text-align: left; background: white; box-sizing: border-box; color: #0f172a; text-decoration: none; font-size: 14px; font-weight: 400; font-family: 'DM Sans', sans-serif; cursor: pointer; border: none; display: block; }
        .action-menu-dropdown .report-btn:hover { background: #fef3c7; color: #92400e; }
        .action-menu-dropdown .delete-material-btn:hover, .action-menu-dropdown .delete-btn:hover, .action-menu-dropdown .leave-btn:hover { background: #fee2e2; color: #b91c1c; }
        .action-menu-dropdown .edit-group-btn:hover, .action-menu-dropdown .edit-material-btn:hover { background: #eff6ff; color: #1d4ed8; }
   </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <div class="container">
        <div class="group-header">
            <div>
                <h2><?php echo htmlspecialchars($group['GroupName']); ?></h2>
                <p class="group-description"><?php echo nl2br(htmlspecialchars($group['Description'])); ?></p>
                <p class="group-meta"> Group Creator: 
                <?php if ($group['OriginalCreatorID']): ?>
                    <a href="view_profile.php?user_id=<?php echo $group['OriginalCreatorID']; ?>"><?php echo htmlspecialchars($group['CreatorName']); ?></a>
                <?php else: ?>
                    <span style="color: #94a3b8; font-style: italic;">[unknown]</span>
                <?php endif; ?>
                </p>
            </div>
            <div class="header-actions">
                <?php if ($is_member && !$is_creator): ?>
                    <div class="action-menu-container">
                        <button class="action-menu-btn" onclick="toggleActionMenu(this)" style="margin-top: 0; padding: 8px 4px; color: #64748b; font-size: 24px; line-height: 0.5;">•••</button>
                        <div class="action-menu-dropdown">
                            <button class="report-btn" onclick="openReportPopup(0, <?php echo $group['CreatedBy']; ?>, 'group')">Report</button>
                            <a href="leave_group.php?group_id=<?php echo $group_id; ?>" class="leave-btn" style="color: #b91c1c;">Leave</a>
                        </div>
                    </div>
                <?php elseif (!$is_member): ?>
                    <a href="join_group.php?group_id=<?php echo $group_id; ?>" class="action-btn join-btn">Join Group</a>
                <?php endif; ?>
                <?php if ($is_creator): ?>
                    <div class="action-menu-container">
                        <button class="action-menu-btn" onclick="toggleActionMenu(this)" style="margin-top: 0; padding: 8px 4px; color: #64748b; font-size: 24px; line-height: 0.5;">•••</button>
                        <div class="action-menu-dropdown">
                            <a href="edit_group.php?group_id=<?php echo $group_id; ?>" class="edit-group-btn" style="color: #2563eb;">Edit</a>
                            <a href="leave_group.php?group_id=<?php echo $group_id; ?>" class="leave-btn" style="color: #b91c1c;" onclick="return confirm('Are you sure you want to leave this group?');">Leave</a>
                            <a href="delete_group.php?group_id=<?php echo $group_id; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this entire group?');">Delete</a>
                        </div>
                    </div>
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
                        <span class="raw-material-title" style="display:none;"><?php echo htmlspecialchars($material['Title']); ?></span>
                        <a href="<?php echo htmlspecialchars($material['FilePath'] ?: $material['LinkURL']); ?>" target="_blank" class="material-title"><?php echo htmlspecialchars($material['Title']); ?></a>
                        
                        <!-- actions -->
                        <div style="display: flex; gap: 10px;">
                            <?php if ($material['UploadedBy'] == $user_id): ?>
                                <div class="action-menu-container">
                                    <button class="action-menu-btn" onclick="toggleActionMenu(this)">•••</button>
                                    <div class="action-menu-dropdown">
                                        <button class="edit-material-btn" onclick="openEditMaterialPopup(this, <?php echo $material['MaterialID']; ?>)">Edit</button>
                                        <a href="delete_material.php?material_id=<?php echo $material['MaterialID']; ?>&group_id=<?php echo $group_id; ?>" class="delete-material-btn" onclick="return confirm('Are you sure?');">Delete</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="action-menu-container">
                                    <button class="action-menu-btn" onclick="toggleActionMenu(this)">•••</button>
                                    <div class="action-menu-dropdown">
                                        <button class="report-btn" onclick="openReportPopup(<?php echo $material['MaterialID']; ?>, <?php echo $material['UploadedBy']; ?>)">Report</button>
                                    </div>
                                </div>
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
                    <label><input type="radio" name="target_type" value="material" checked> Content</label>
                    <label><input type="radio" name="target_type" value="user"> User</label>
                    <label><input type="radio" name="target_type" value="group"> Group</label>
                </div>
                <textarea name="reason" rows="4" placeholder="Reason for reporting..." required></textarea>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <!-- edit material popup -->
    <div class="popup-overlay" id="editMaterialPopup">
        <div class="popup-content">
            <span class="popup-close" onclick="closeEditMaterialPopup()">&times;</span>
            <h2>Edit</h2>
            <form action="edit_material_process.php" method="POST">
                <input type="hidden" name="material_id" id="editMaterialIdInput">
                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                <label style="display:block; margin-bottom: 6px; font-weight: 500; text-align: left;">Title:</label>
                <input type="text" name="material_title" id="editMaterialTitleInput" style="width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid #dbe4f0; border-radius: 10px; box-sizing: border-box; font-family: 'DM Sans', sans-serif;" required>
                <button type="submit" style="background: linear-gradient(135deg, #16a34a, #22c55e); color: white; padding: 10px 15px; border: none; border-radius: 999px; cursor: pointer; font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 400;">Save Changes</button>
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
        memberSearchInput.addEventListener('keyup', () => fetchResults('user', memberSearchInput.value, memberList));
        memberList.addEventListener('click', (e) => logSearchOnClick(e, 'A', memberSearchInput, 'user'));

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

        function openReportPopup(materialId, reportedUserId, defaultTarget = null) {
            reportMaterialIdInput.value = materialId || 0;
            reportUserIdInput.value = reportedUserId;
            
            if (defaultTarget) {
                const targetRadio = document.querySelector(`input[name="target_type"][value="${defaultTarget}"]`);
                if (targetRadio) targetRadio.checked = true;
            }
            
            reportPopup.style.display = 'flex';
        }

        function closeReportPopup() {
            reportPopup.style.display = 'none';
        }

        // js for edit material popup
        const editMaterialPopup = document.getElementById('editMaterialPopup');
        const editMaterialIdInput = document.getElementById('editMaterialIdInput');
        const editMaterialTitleInput = document.getElementById('editMaterialTitleInput');

        function openEditMaterialPopup(btn, materialId) {
            const rawTitle = btn.closest('li').querySelector('.raw-material-title').textContent;
            editMaterialIdInput.value = materialId;
            editMaterialTitleInput.value = rawTitle;
            editMaterialPopup.style.display = 'flex';
        }

        function closeEditMaterialPopup() {
            editMaterialPopup.style.display = 'none';
        }
    </script>
</body>
</html>