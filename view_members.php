<?php
session_start();
require_once 'db_connect.php';

$group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
if ($group_id <= 0) {
    die("Invalid group ID provided.");
}

// fetch members
$members_stmt = $conn->prepare(
    "SELECT up.UserID, up.Name 
     FROM groupmembership AS gm
     JOIN userprofile AS up ON gm.UserID = up.UserID
     WHERE gm.GroupID = ? ORDER BY up.Name ASC"
);
$members_stmt->bind_param("i", $group_id);
$members_stmt->execute();
$members_result = $members_stmt->get_result();
$members = $members_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Group Members - StudyPal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; margin: 0; color: #333; }
        .container { max-width: 500px; margin: 40px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .section-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px; }
        .section-title { font-size: 22px; color: #333; margin: 0; }
        .search-container { max-width: 250px; width: 100%; }
        .search-input { width: 100%; padding: 10px; font-size: 14px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Inter', sans-serif; box-sizing: border-box; }
        .member-list { list-style: none; padding: 0; margin-top: 20px; min-height: 50px; }
        .member-list li { padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
        .member-list li:last-child { border-bottom: none; }
        .member-list a { text-decoration: none; color: #333; font-weight: 500; }
        .member-list a:hover { text-decoration: none; }
        .back-link { display: inline-block; margin-top: 30px; font-weight: 500; font-size: 16px; color: #1877f2; text-decoration: none; }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <div class="container">
        <div class="section-header">
            <h3 class="section-title">Members (<?php echo count($members); ?>)</h3>
            <div class="search-container">
                <input type="text" id="memberSearchInput" class="search-input" placeholder="Search members...">
            </div>
        </div>
        <ul class="member-list" id="memberList">
            <?php if (!empty($members)): ?>
                <?php foreach ($members as $member): ?>
                    <li>
                        <a href="view_profile.php?user_id=<?php echo $member['UserID']; ?>">
                            <?php echo htmlspecialchars($member['Name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No members have joined yet.</li>
            <?php endif; ?>
        </ul>
        <a href="group_page.php?group_id=<?php echo $group_id; ?>" class="back-link">&larr; Back</a>
    </div>
    <script>
        const searchInput = document.getElementById('memberSearchInput');
        const memberList = document.getElementById('memberList');
        const groupId = <?php echo $group_id; ?>;
        
        // real-time search
        searchInput.addEventListener('keyup', function() {
            const query = this.value;
            fetch(`live_search_members.php?group_id=${groupId}&query=${query}`)
                .then(response => response.text())
                .then(data => { memberList.innerHTML = data; })
                .catch(error => { console.error('Error:', error); });
        });

        // logging the search on click
        memberList.addEventListener('click', function(event) {
            if (event.target.tagName === 'A') {
                const searchTerm = searchInput.value;
                if (searchTerm.trim() !== '') {
                    const formData = new FormData();
                    formData.append('search_term', searchTerm);
                    formData.append('search_type', 'user');

                    fetch('log_search_query.php', {
                        method: 'POST',
                        body: formData
                    }).catch(error => console.error('Error logging search:', error));
                }
            }
        });
    </script>
</body>
</html>