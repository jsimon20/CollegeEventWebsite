<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isSuperAdmin()) {
    header("Location: ../unauthorized.php");
    exit;
}

// Fetch all events
$query = "SELECT * FROM Events ORDER BY EventTime DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View All Events</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>All Events</h2>
        <?php while ($event = $result->fetch_assoc()): ?>
            <div class="event">
                <h3><?php echo $event['Name']; ?></h3>
                <p><strong>Category:</strong> <?php echo $event['Category']; ?></p>
                <p><strong>Date and Time:</strong> <?php echo date("F j, Y, g:i A", strtotime($event['EventTime'])); ?></p>
                <p><strong>Location:</strong> <?php echo $event['LocationName']; ?></p>
                <p><strong>Publicity:</strong> <?php echo $event['Publicity']; ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>