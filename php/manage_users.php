<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isSuperAdmin()) {
    header("Location: ../unauthorized.php");
    exit;
}

// Fetch pending Admin requests
$stmt = $conn->prepare("SELECT UserID, Username, Email FROM Users WHERE UserType = 'PendingAdmin'");
$stmt->execute();
$pending_admins = $stmt->get_result();

// Fetch all Admins
$stmt = $conn->prepare("SELECT UserID, Username, Email FROM Users WHERE UserType = 'Admin'");
$stmt->execute();
$admins = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Manage Users</h2>

        <h3>Pending Admin Requests</h3>
        <?php while ($user = $pending_admins->fetch_assoc()): ?>
            <div class="user">
                <p><strong>Username:</strong> <?php echo $user['Username']; ?></p>
                <p><strong>Email:</strong> <?php echo $user['Email']; ?></p>
                <form method="POST" action="approve_admin.php">
                    <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                    <button type="submit">Approve as Admin</button>
                </form>
            </div>
        <?php endwhile; ?>

        <h3>Existing Admins</h3>
        <?php while ($admin = $admins->fetch_assoc()): ?>
            <div class="user">
                <p><strong>Username:</strong> <?php echo $admin['Username']; ?></p>
                <p><strong>Email:</strong> <?php echo $admin['Email']; ?></p>
            </div>
        <?php endwhile; ?>

        <h3>Create New Admin</h3>
        <form method="POST" action="create_admin.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <button type="submit">Create Admin</button>
        </form>
    </div>
</body>
</html>