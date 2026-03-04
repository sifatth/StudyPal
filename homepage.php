<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all active groups
$sql = "SELECT `GroupID`, `GroupName`, `Description` FROM `studygroup` WHERE `IsActive` = 1 ORDER BY `GroupName` ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$groups = [];
if ($result && $result->num_rows > 0) {
    $groups = $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch groups the user belongs to
$user_groups = [];
$sql_user = "SELECT sg.`GroupID`, sg.`GroupName`, sg.`Description`
             FROM `studygroup` sg
             INNER JOIN `groupmembership` gm ON sg.`GroupID` = gm.`GroupID`
             WHERE gm.`UserID` = ? AND sg.`IsActive` = 1 ORDER BY sg.`GroupName` ASC";

if (!$conn) {
    die("Database connection failed.");
}
$check = $conn->query("SHOW TABLES LIKE 'groupmembership'");
if (!$check || $check->num_rows == 0) {
    die("Table 'groupmembership' does not exist in the database.");
}

$stmt_user = $conn->prepare($sql_user);
if (!$stmt_user) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error . " | SQL: " . $sql_user);
}
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
if ($result_user && $result_user->num_rows > 0) {
    $user_groups = $result_user->fetch_all(MYSQLI_ASSOC);
}

// duplicate group check
$error_message = '';
if (isset($_GET['error']) && $_GET['error'] == 'duplicate_group') {
    $error_message = 'A study group with this name already exists. Please choose a different name.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage - StudyPal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; margin: 0; color: #333; }
        .main-container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .search-container { margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto; }
        .search-input { width: 100%; padding: 12px; font-size: 16px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Inter', sans-serif; box-sizing: border-box; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section-header .create-group-btn { margin-left: 20px; }
        .create-group-btn { background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; }
        .group-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; min-height: 100px; }
        .group-card { background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 20px; display: flex; flex-direction: column; }
        .view-group-btn { display: block; width: 100%; padding: 10px; background-color: #e7f3ff; color: #1877f2; border: none; border-radius: 6px; text-align: center; text-decoration: none; font-weight: 500; margin-top: auto; box-sizing: border-box; }
        .no-groups { text-align: center; padding: 40px; background-color: #fff; border-radius: 8px; grid-column: 1 / -1; }
        .popup-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .popup-content { background: #fff; padding: 30px; border-radius: 8px; width: 400px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .popup-content h2 { margin-top: 0; }
        .popup-content input, .popup-content textarea { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ddd; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        .popup-content button { width: 100%; padding: 12px; }
        .popup-close { float: right; cursor: pointer; font-size: 24px; font-weight: bold; }
        .error-popup .popup-content { border-top: 5px solid #dc3545; }
        .error-popup h2 { color: #dc3545; }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>
    
    <main class="main-container">
        <div class="search-container">
            <input type="text" id="groupSearchInput" class="search-input" placeholder="Search for groups...">
        </div>

        <!-- your groups -->
        <div class="section-header">
            <h2>Your Groups</h2>
            <button class="create-group-btn" id="createGroupBtn">+ Create New Group</button>
        </div>
        <div class="group-grid" id="yourGroupGrid">
            <?php if (!empty($user_groups)): ?>
                <?php foreach ($user_groups as $group): ?>
                    <div class="group-card">
                        <h3><?php echo htmlspecialchars($group['GroupName']); ?></h3>
                        <p><?php echo htmlspecialchars($group['Description']); ?></p>
                        <a href="group_page.php?group_id=<?php echo $group['GroupID']; ?>" class="view-group-btn">View Group</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-groups">
                    <h3>You have not joined any groups yet.</h3>
                    <p>Join or create a group to get started!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- study groups -->
        <div class="section-header">
            <h2>Study Groups</h2>
        </div>
        <div class="group-grid" id="groupGrid">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $group): ?>
                    <div class="group-card">
                        <h3><?php echo htmlspecialchars($group['GroupName']); ?></h3>
                        <p><?php echo htmlspecialchars($group['Description']); ?></p>
                        <a href="group_page.php?group_id=<?php echo $group['GroupID']; ?>" class="view-group-btn">View Group</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-groups">
                    <h3>No study groups found.</h3>
                    <p>Be the first to create one!</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- create group popup -->
    <div class="popup-overlay" id="createGroupPopup">
        <div class="popup-content">
            <span class="popup-close" id="closePopupBtn">&times;</span>
            <h2>Create a New Study Group</h2>
            <form action="create_group_process.php" method="POST">
                <label for="group_name">Group Name:</label>
                <input type="text" id="group_name" name="group_name" required>
                <label for="group_desc">Description:</label>
                <textarea id="group_desc" name="group_desc" rows="4" required></textarea>
                <button type="submit" class="create-group-btn">Create Group</button>
            </form>
        </div>
    </div>

    <!-- error popup -->
    <div id="errorPopup" class="popup-overlay error-popup">
        <div class="popup-content">
            <span class="popup-close" id="closeErrorPopupBtn">&times;</span>
            <h2>Failed</h2>
            <p id="errorMessage"></p>
        </div>
    </div>

    <script>
        // js to handle the popup
        const createGroupBtn = document.getElementById('createGroupBtn');
        const createGroupPopup = document.getElementById('createGroupPopup');
        const closePopupBtn = document.getElementById('closePopupBtn');
        createGroupBtn.addEventListener('click', () => { createGroupPopup.style.display = 'flex'; });
        closePopupBtn.addEventListener('click', () => { createGroupPopup.style.display = 'none'; });
        window.addEventListener('click', (event) => { if (event.target == createGroupPopup) { createGroupPopup.style.display = 'none'; } });

        // js for real-time group search (only for Study Groups section)
        const searchInput = document.getElementById('groupSearchInput');
        const groupGrid = document.getElementById('groupGrid');
        searchInput.addEventListener('keyup', function() {
            const query = this.value;
            fetch(`live_search_groups.php?query=${query}`)
                .then(response => response.text())
                .then(data => { groupGrid.innerHTML = data; })
                .catch(error => { console.error('Error:', error); });
        });

        // inserts into table after clicking on search result
        groupGrid.addEventListener('click', function(event) {
            if (event.target.classList.contains('view-group-btn')) {
                const searchTerm = searchInput.value;
                if (searchTerm.trim() !== '') {
                    const formData = new FormData();
                    formData.append('search_term', searchTerm);
                    formData.append('search_type', 'group'); 

                    fetch('log_search_query.php', {
                        method: 'POST',
                        body: formData
                    }).catch(error => console.error('Error logging search:', error));
                }
            }
        });

        // js for error popup
        const errorMessage = "<?php echo $error_message; ?>";
        if (errorMessage) {
            const popup = document.getElementById('errorPopup');
            const messageElement = document.getElementById('errorMessage');
            const closeBtn = document.getElementById('closeErrorPopupBtn');
            
            messageElement.textContent = errorMessage;
            popup.style.display = 'flex';

            const closePopup = () => {
                popup.style.display = 'none';
                window.history.replaceState({}, document.title, window.location.pathname);
            };

            closeBtn.addEventListener('click', closePopup);
            window.addEventListener('click', (event) => {
                if (event.target == popup) {
                    closePopup();
                }
            });
        }
    </script>
</body>
</html>