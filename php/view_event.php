<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Include the script to update events from UCF
require 'update_ucf_events.php';

// Pagination settings
$events_per_page = 10; // Number of events to display per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $events_per_page;

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$university_id = $_SESSION['university_id'];

$query = "SELECT * FROM Events WHERE Publicity = 'Public'";

if ($user_type == 'Student') {
    $query .= " OR (Publicity = 'Private' AND UniversityID = $university_id)";
    $query .= " OR (Publicity = 'RSO' AND RSOID IN (SELECT RSOID FROM RSO_Members WHERE UserID = $user_id))";
}

$query .= " ORDER BY DATE(EventTime) DESC, TIME(EventTime) ASC LIMIT $events_per_page OFFSET $offset";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Get total number of events for pagination
$total_query = "SELECT COUNT(*) as total FROM Events WHERE Publicity = 'Public'";

if ($user_type == 'Student') {
    $total_query .= " OR (Publicity = 'Private' AND UniversityID = $university_id)";
    $total_query .= " OR (Publicity = 'RSO' AND RSOID IN (SELECT RSOID FROM RSO_Members WHERE UserID = $user_id))";
}

$total_result = $conn->query($total_query);
$total_events = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_events / $events_per_page);
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
                <p><strong>Date and Time:</strong> 
                    <?php 
                    $event_time = strtotime($row['EventTime']);
                    $formatted_time = date("g:i A", $event_time);
                    if (date("i", $event_time) == "00") {
                        $formatted_time = date("g A", $event_time);
                    }
                    echo date("F j, Y, ", $event_time) . $formatted_time;
                    ?>
                </p>
                <p><strong>Location:</strong> <?php echo !empty($row['LocationName']) ? $row['LocationName'] : 'Virtual'; ?></p>
                <p><strong>Contact:</strong> 
                    <?php 
                    $contact_info = '';
                    if (!empty($row['ContactPhone'])) {
                        $contact_info .= $row['ContactPhone'];
                    }
                    if (!empty($row['ContactEmail'])) {
                        if (!empty($contact_info)) {
                            $contact_info .= ', ';
                        }
                        $contact_info .= $row['ContactEmail'];
                    }
                    echo $contact_info;
                    ?>
                </p>
                <a href="event_details.php?event_id=<?php echo $row['EventID']; ?>"><button>View Details</button></a>
            </div>
        <?php endwhile; ?>

        <!-- Pagination controls -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>