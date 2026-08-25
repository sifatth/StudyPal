<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}

$group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($group_id <= 0) {
    die("Invalid group ID.");
}

// Fetch group details
$stmt = $conn->prepare("SELECT GroupName, Description, CreatedBy FROM studygroup WHERE GroupID = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$group) {
    die("Group not found.");
}

// Verify that the current user is the creator of this group
if ($group['CreatedBy'] != $user_id) {
    die("Access Denied: Only the group creator can edit this group.");
}

$error_message = '';
if (isset($_GET['error']) && $_GET['error'] == 'duplicate_name') {
    $error_message = 'A study group with this name already exists. Please choose a different name.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Group - StudyPal</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap');
        * { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; font-weight: 300; background: linear-gradient(135deg, #f7faff 0%, #eef4ff 100%); margin: 0; color: #14213d; min-height: 100vh; }
        .edit-container { max-width: 560px; margin: 28px auto 48px; background: rgba(255,255,255,0.96); padding: 32px; border-radius: 24px; border: 1px solid #e4ebf7; box-shadow: 0 10px 35px rgba(15, 23, 42, 0.06); }
        .edit-container h2 { text-align: center; margin-bottom: 28px; font-size: 1.6rem; color: #0f172a; font-family: 'Montserrat', sans-serif; font-weight: 700; }
        label { font-weight: 400; display: block; margin-bottom: 8px; color: #334155; }
        input[type="text"], textarea { width: 100%; padding: 12px 14px; margin-bottom: 18px; border-radius: 12px; border: 1px solid #dbe4f0; box-sizing: border-box; font-size: 15px; font-family: 'DM Sans', sans-serif; font-weight: 300; outline: none; background: #f8fbff; }
        input[type="text"]:focus, textarea:focus { border-color: #60a5fa; box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.16); }
        textarea { height: 120px; resize: vertical; }
        .error-banner { background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: 12px; border-radius: 12px; font-size: 14px; margin-bottom: 20px; text-align: center; }
        .form-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 24px; gap: 12px; flex-wrap: wrap; }
        button { padding: 12px 24px; background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; border: none; border-radius: 999px; cursor: pointer; font-size: 15px; font-weight: 400; font-family: 'DM Sans', sans-serif; font-weight: 300; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2); }
        button:hover { filter: brightness(1.05); }
        a { color: #2563eb; text-decoration: none; font-weight: 400; font-size: 15px; }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <div class="edit-container">
        <h2>Edit Group Details</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-banner"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="edit_group_process.php" method="POST">
            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
            
            <label for="group_name">Group Name:</label>
            <input type="text" id="group_name" name="group_name" value="<?php echo htmlspecialchars($group['GroupName']); ?>" required>

            <label for="group_desc">Description:</label>
            <textarea id="group_desc" name="group_desc" required><?php echo htmlspecialchars($group['Description']); ?></textarea>

            <div class="form-actions">
                <a href="group_page.php?group_id=<?php echo $group_id; ?>">&larr; Cancel</a>
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</body>
</html>
