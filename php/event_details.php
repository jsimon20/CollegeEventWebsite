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
        <p><strong>Date and Time:</strong> 
            <?php 
            $event_time = strtotime($event['EventTime']);
            $end_time = strtotime($event['EndTime']);
            $formatted_event_time = date("g:i A", $event_time);
            $formatted_end_time = date("g:i A", $end_time);
            if (date("i", $event_time) == "00") {
                $formatted_event_time = date("g A", $event_time);
            }
            if (date("i", $end_time) == "00") {
                $formatted_end_time = date("g A", $end_time);
            }
            echo date("F j, Y, ", $event_time) . $formatted_event_time . " to " . date("F j, Y, ", $end_time) . $formatted_end_time;
            ?>
        </p>
        <p><strong>Location:</strong> 
            <?php 
            if (!empty($event['LocationName'])) {
                $location_name = $event['LocationName'];
                $location_url = "https://www.google.com/maps/search/?api=1&query=" . urlencode($location_name . ", UCF");
                echo "<a href=\"$location_url\" target=\"_blank\">$location_name</a>";
            } else {
                echo 'Virtual';
            }
            ?>
        </p>
        <?php if (!empty($event['ContactPhone'])): ?>
            <p><strong>Contact Phone:</strong> <?php echo $event['ContactPhone']; ?></p>
        <?php endif; ?>
        <p><strong>Contact Email:</strong> <?php echo $event['ContactEmail']; ?></p>
        <p><strong>Publicity:</strong> <?php echo $event['Publicity']; ?></p>
        <a href="view_event.php"><button>Back</button></a> <!-- Back button added -->
    </div>
</body>
</html>