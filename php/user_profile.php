<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT Username, Email, UserType FROM Users WHERE UserID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch joined RSOs
$rsos_stmt = $conn->prepare("SELECT RSOs.Name, RSOs.Description FROM RSO_Members JOIN RSOs ON RSO_Members.RSOID = RSOs.RSOID WHERE RSO_Members.UserID = ?");
$rsos_stmt->bind_param("i", $user_id);
$rsos_stmt->execute();
$rsos_result = $rsos_stmt->get_result();

// Fetch event history
$events_stmt = $conn->prepare("SELECT Events.Name, Events.EventTime, Comments.Rating FROM Comments JOIN Events ON Comments.EventID = Events.EventID WHERE Comments.UserID = ?");
$events_stmt->bind_param("i", $user_id);
$events_stmt->execute();
$events_result = $events_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>User Profile</h2>
        <p><strong>Username:</strong> <?php echo $user['Username']; ?></p>
        <p><strong>Email:</strong> <?php echo $user['Email']; ?></p>
        <p><strong>Role:</strong> <?php echo $user['UserType']; ?></p>
        <h3>Joined RSOs</h3>
        <?php while ($rso = $rsos_result->fetch_assoc()): ?>
            <div class="rso">
                <h4><?php echo $rso['Name']; ?></h4>
                <p><?php echo $rso['Description']; ?></p>
            </div>
        <?php endwhile; ?>
        <h3>Event History</h3>
        <?php while ($event = $events_result->fetch_assoc()): ?>
            <div class="event">
                <h4><?php echo $event['Name']; ?></h4>
                <p><strong>Date and Time:</strong> <?php echo date("F j, Y, g:i A", strtotime($event['EventTime'])); ?></p>
                <p><strong>Rating:</strong> <?php echo $event['Rating']; ?> / 5</p>
            </div>
        <?php endwhile; ?>
        <a href="edit_profile.php"><button>Edit Profile</button></a>
        <a href="../index.php?view=day"><button>Back to Homepage</button></a>
    </div>
</body>
</html>