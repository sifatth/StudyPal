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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap');
        body { font-family: 'DM Sans', sans-serif; font-weight: 300; background: linear-gradient(135deg, #f7faff 0%, #eef4ff 100%); margin: 0; color: #14213d; min-height: 100vh; }
        .main-container { max-width: 1120px; margin: 32px auto 56px; padding: 0 20px; }
        .hero-card { background: rgba(255, 255, 255, 0.95); border: 1px solid #dfe9ff; border-radius: 24px; padding: 24px 28px; box-shadow: 0 10px 35px rgba(37, 99, 235, 0.08); display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 24px; flex-wrap: wrap; }
        .hero-eyebrow { text-transform: uppercase; letter-spacing: 0.18em; font-size: 0.75rem; font-weight: 400; color: #2563eb; margin: 0 0 8px; }
        .hero-card h1 { margin: 0 0 8px; font-size: 1.7rem; color: #0f172a; }
        .hero-card p { margin: 0; color: #475569; max-width: 560px; line-height: 1.6; }
        .hero-badge { background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; padding: 10px 14px; border-radius: 999px; font-weight: 400; font-size: 0.95rem; box-shadow: 0 8px 24px rgba(37, 99, 235, 0.2); font-family: 'Montserrat', sans-serif; letter-spacing: -0.02em; }
        .search-container { max-width: 300px; width: 100%; }
        .search-input { width: 100%; padding: 10px 14px; font-size: 15px; border: 1px solid #dbe4f0; border-radius: 999px; font-family: 'DM Sans', sans-serif; font-weight: 300; box-sizing: border-box; outline: none; background: #fff; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04); }
        .search-input:focus { border-color: #60a5fa; box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.2); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin: 28px 0 16px; }
        .section-header h2 { margin: 0; font-size: 1.15rem; color: #0f172a; }
        .section-header .create-group-btn { margin-left: 20px; }
        .create-group-btn { background: linear-gradient(135deg, #16a34a, #22c55e); color: white; border: none; padding: 10px 18px; border-radius: 999px; font-size: 0.95rem; font-weight: 400; cursor: pointer; box-shadow: 0 8px 20px rgba(34, 197, 94, 0.18); max-width: 190px; width: 100%; }
        .create-group-btn:hover { filter: brightness(1.02); }
        .group-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; min-height: 100px; }
        .group-card { background-color: #ffffff; border: 1px solid #e4ebf7; border-radius: 18px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04); padding: 20px; display: flex; flex-direction: column; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .group-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08); }
        .group-pill { display: inline-block; padding: 5px 10px; background: #eff6ff; color: #2563eb; border-radius: 999px; font-size: 0.8rem; font-weight: 400; margin-bottom: 12px; width: fit-content; }
        .group-card h3 { margin: 0 0 8px; font-size: 1.05rem; color: #111827; }
        .group-card p { margin: 0 0 16px; color: #64748b; line-height: 1.6; flex: 1; }
        .view-group-btn { display: block; width: 100%; padding: 10px; background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #2563eb; border: none; border-radius: 10px; text-align: center; text-decoration: none; font-weight: 400; margin-top: auto; box-sizing: border-box; }
        .no-groups { text-align: center; padding: 40px; background-color: #fff; border: 1px solid #e4ebf7; border-radius: 18px; grid-column: 1 / -1; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04); }
        .popup-overlay { display: none; position: fixed; inset: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.55); justify-content: center; align-items: center; z-index: 1000; padding: 20px; }
        .popup-content { background: #fff; padding: 28px; border-radius: 18px; width: 100%; max-width: 420px; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.2); }
        .popup-content h2 { margin-top: 0; color: #0f172a; }
        .popup-content label { display: block; margin-bottom: 6px; font-weight: 400; color: #334155; }
        .popup-content input, .popup-content textarea { width: 100%; padding: 11px 12px; margin-bottom: 14px; border-radius: 10px; border: 1px solid #dbe4f0; box-sizing: border-box; font-family: 'DM Sans', sans-serif; font-weight: 300; }
        .popup-content input:focus, .popup-content textarea:focus { outline: none; border-color: #60a5fa; box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.14); }
        .popup-content button { width: 100%; padding: 12px; }
        .popup-close { float: right; cursor: pointer; font-size: 24px; font-weight: 400; color: #64748b; }
        .error-popup .popup-content { border-top: 5px solid #dc3545; }
        .error-popup h2 { color: #dc3545; }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>
    
    <main class="main-container">
        <section class="hero-card">
            <div>
                <p class="hero-eyebrow">Study together</p>
                <h1>Find your next study circle</h1>
                <p>Discover active groups, connect with classmates, and keep your learning on track.</p>
            </div>
            <div class="hero-badge">Simple, focused, modern</div>
        </section>

        <!-- your groups -->
        <div class="section-header">
            <h2>Your Groups</h2>
            <button class="create-group-btn" id="createGroupBtn">+ Create New Group</button>
        </div>
        <div class="group-grid" id="yourGroupGrid">
            <?php if (!empty($user_groups)): ?>
                <?php foreach ($user_groups as $group): ?>
                    <div class="group-card">
                        <span class="group-pill">Joined</span>
                        <h3><?php echo htmlspecialchars($group['GroupName']); ?></h3>
                        <p><?php echo htmlspecialchars($group['Description']); ?></p>
                        <a href="group_page.php?group_id=<?php echo $group['GroupID']; ?>" class="view-group-btn">Open Group</a>
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
            <div class="search-container">
                <input type="text" id="groupSearchInput" class="search-input" placeholder="Search for groups...">
            </div>
        </div>
        <div class="group-grid" id="groupGrid">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $group): ?>
                    <div class="group-card">
                        <span class="group-pill">Active</span>
                        <h3><?php echo htmlspecialchars($group['GroupName']); ?></h3>
                        <p><?php echo htmlspecialchars($group['Description']); ?></p>
                        <a href="group_page.php?group_id=<?php echo $group['GroupID']; ?>" class="view-group-btn">Open Group</a>
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