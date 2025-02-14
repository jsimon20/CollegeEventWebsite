<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];

    $stmt = $conn->prepare("UPDATE Comments SET Rating = ? WHERE EventID = ? AND UserID = ?");
    $stmt->bind_param("iii", $rating, $event_id, $user_id);

    if ($stmt->execute()) {
        echo "Rating updated successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rate Event</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Rate Event</h2>
        <form method="POST" action="rate_event.php">
            <label for="event_id">Event ID:</label>
            <input type="text" id="event_id" name="event_id" required><br>
            <label for="rating">Rating:</label>
            <input type="number" id="rating" name="rating" min="1" max="5" required><br>
            <button type="submit">Submit Rating</button>
        </form>
    </div>
</body>
</html>