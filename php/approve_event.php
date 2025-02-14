<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isSuperAdmin()) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $stmt = $conn->prepare("UPDATE Events SET Approved = 1 WHERE EventID = ?");
    $stmt->bind_param("i", $event_id);

    if ($stmt->execute()) {
        echo "Event approved successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Approve Events</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Approve Events</h2>
        <form method="POST" action="approve_event.php">
            <label for="event_id">Event ID:</label>
            <input type="text" id="event_id" name="event_id" required><br>
            <button type="submit">Approve Event</button>
        </form>
    </div>
</body>
</html>