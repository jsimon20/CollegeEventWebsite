<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$university_id = $_GET['university_id'];

// Fetch university details
$query = "SELECT * FROM Universities WHERE UniversityID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $university_id);
$stmt->execute();
$university_result = $stmt->get_result();
$university = $university_result->fetch_assoc();

// Fetch private events and RSOs at the university
$events_query = "SELECT * FROM Events WHERE UniversityID = ? AND Publicity = 'Private'";
$events_stmt = $conn->prepare($events_query);
$events_stmt->bind_param("i", $university_id);
$events_stmt->execute();
$events_result = $events_stmt->get_result();

$rsos_query = "SELECT * FROM RSOs WHERE UniversityID = ?";
$rsos_stmt = $conn->prepare($rsos_query);
$rsos_stmt->bind_param("i", $university_id);
$rsos_stmt->execute();
$rsos_result = $rsos_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>University Profile</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2><?php echo $university['Name']; ?></h2>
        <p><strong>Location:</strong> <?php echo $university['Location']; ?></p>
        <p><strong>Description:</strong> <?php echo $university['Description']; ?></p>
        <p><strong>Student Count:</strong> <?php echo $university['StudentCount']; ?></p>
        <h3>Private Events</h3>
        <?php while ($event = $events_result->fetch_assoc()): ?>
            <div class="event">
                <h4><?php echo $event['Name']; ?></h4>
                <p><?php echo $event['Description']; ?></p>
                <p><strong>Date and Time:</strong> <?php echo date("F j, Y, g:i A", strtotime($event['EventTime'])); ?></p>
                <a href="event_details.php?event_id=<?php echo $event['EventID']; ?>"><button>View Details</button></a>
            </div>
        <?php endwhile; ?>
        <h3>RSOs</h3>
        <?php while ($rso = $rsos_result->fetch_assoc()): ?>
            <div class="rso">
                <h4><?php echo $rso['Name']; ?></h4>
                <p><?php echo $rso['Description']; ?></p>
                <a href="view_rso.php?rso_id=<?php echo $rso['RSOID']; ?>"><button>View Members</button></a>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>