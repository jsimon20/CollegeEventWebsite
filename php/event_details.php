<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$event_id = $_GET['event_id'];

$stmt = $conn->prepare("SELECT * FROM Events WHERE EventID = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Details</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2><?php echo $event['Name']; ?></h2>
        <p><strong>Category:</strong> <?php echo $event['Category']; ?></p>
        <p><strong>Description:</strong> <?php echo $event['Description']; ?></p>
        <p><strong>Event Time:</strong> <?php echo $event['EventTime']; ?></p>
        <p><strong>Location:</strong> <?php echo $event['LocationName']; ?></p>
        <p><strong>Latitude:</strong> <?php echo $event['Latitude']; ?></p>
        <p><strong>Longitude:</strong> <?php echo $event['Longitude']; ?></p>
        <p><strong>Contact Phone:</strong> <?php echo $event['ContactPhone']; ?></p>
        <p><strong>Contact Email:</strong> <?php echo $event['ContactEmail']; ?></p>
        <p><strong>Publicity:</strong> <?php echo $event['Publicity']; ?></p>
    </div>
</body>
</html>