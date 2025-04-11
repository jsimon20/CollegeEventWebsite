<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();
redirectIfNotLoggedIn();

if (!isAdmin()) {
    die("Access denied.");
}

$rso_id = $_GET['rso_id'] ?? null;
if (!$rso_id) {
    die("RSO ID missing.");
}

$admin_id = $_SESSION['UserID'] ?? $_SESSION['user_id'] ?? null;
if (!$admin_id) {
    die("Admin session ID missing.");
}

// Handle form submission
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name'] ?? '');
    $new_desc = trim($_POST['description'] ?? '');
    $new_email = trim($_POST['new_member_email'] ?? '');

    // Update name and description
    $stmt = $conn->prepare("UPDATE RSOs SET Name = ?, Description = ? WHERE RSOID = ? AND AdminID = ?");
    $stmt->bind_param("ssii", $new_name, $new_desc, $rso_id, $admin_id);
    if ($stmt->execute()) {
        $success = true;
    } else {
        $error = "Failed to update RSO details.";
    }

    // Add member if email provided
    if ($new_email !== '') {
        $user_stmt = $conn->prepare("SELECT UserID FROM Users WHERE Email = ?");
        $user_stmt->bind_param("s", $new_email);
        $user_stmt->execute();
        $user_stmt->bind_result($uid);
        if ($user_stmt->fetch()) {
            $insert_stmt = $conn->prepare("INSERT IGNORE INTO RSO_Members (RSOID, UserID) VALUES (?, ?)");
            $insert_stmt->bind_param("ii", $rso_id, $uid);
            $insert_stmt->execute();
        } else {
            $error = "User not found with that email.";
        }
        $user_stmt->close();
    }

    // Handle member removal
    if (!empty($_POST['remove_member_ids'])) {
        foreach ($_POST['remove_member_ids'] as $remove_id) {
            $del_stmt = $conn->prepare("DELETE FROM RSO_Members WHERE RSOID = ? AND UserID = ?");
            $del_stmt->bind_param("ii", $rso_id, $remove_id);
            $del_stmt->execute();
        }
    }
}

// Fetch current RSO info
$stmt = $conn->prepare("SELECT Name, Description FROM RSOs WHERE RSOID = ? AND AdminID = ?");
$stmt->bind_param("ii", $rso_id, $admin_id);
$stmt->execute();
$stmt->bind_result($rso_name, $rso_desc);
$stmt->fetch();
$stmt->close();

// Fetch current members
$members_stmt = $conn->prepare("
    SELECT Users.UserID, Users.Email 
    FROM Users 
    JOIN RSO_Members ON Users.UserID = RSO_Members.UserID 
    WHERE RSO_Members.RSOID = ?
");
$members_stmt->bind_param("i", $rso_id);
$members_stmt->execute();
$members_result = $members_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit RSO</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="container">
    <h2>Edit RSO</h2>
    <a href="../index.php?view=day"><button type="button">← Back to Events</button></a>

    <?php if ($success): ?>
        <p style="color: green;">RSO updated successfully.</p>
    <?php elseif ($error): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="name">RSO Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($rso_name) ?>" required><br>

        <label for="description">Description:</label>
        <textarea name="description" required><?= htmlspecialchars($rso_desc) ?></textarea><br>

        <h4>Current Members</h4>
        <?php while ($member = $members_result->fetch_assoc()): ?>
            <label>
                <input type="checkbox" name="remove_member_ids[]" value="<?= $member['UserID'] ?>">
                Remove <?= htmlspecialchars($member['Email']) ?>
            </label><br>
        <?php endwhile; ?>

        <h4>Add New Member by Email</h4>
        <input type="email" name="new_member_email" placeholder="example@school.edu"><br><br>

        <button type="submit">💾 Save Changes</button>
    </form>
</div>
</body>
</html>
