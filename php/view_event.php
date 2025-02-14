<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$university_id = $_SESSION['university_id'];

$query = "SELECT * FROM Events WHERE Publicity = 'Public'";

if ($user_type == 'Student') {
    $query .= " OR (Publicity = 'Private' AND UniversityID = $university_id)";
    $query .= " OR (Publicity = 'RSO' AND RSOID IN (SELECT RSOID FROM RSO_Members WHERE UserID = $user_id))";
}

// Ensure the query is properly enclosed in parentheses
$query = "SELECT * FROM Events WHERE Publicity = 'Public' OR (Publicity = 'Private' AND UniversityID = $university_id) OR (Publicity = 'RSO' AND RSOID IN (SELECT RSOID FROM RSO_Members WHERE UserID = $user_id))";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Events</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>View Events</h2>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="event">
                <h3><?php echo $row['Name']; ?></h3>
                <p><?php echo $row['Description']; ?></p>
                <p><strong>Category:</strong> <?php echo $row['Category']; ?></p>
                <p><strong>Time:</strong> <?php echo $row['EventTime']; ?></p>
                <p><strong>Location:</strong> <?php echo $row['LocationName']; ?></p>
                <p><strong>Contact:</strong> <?php echo $row['ContactPhone']; ?>, <?php echo $row['ContactEmail']; ?></p>
                <a href="event_details.php?event_id=<?php echo $row['EventID']; ?>"><button>View Details</button></a>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>