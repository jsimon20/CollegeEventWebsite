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
    $comment_text = $_POST['comment_text'];
    $rating = $_POST['rating'];

    $stmt = $conn->prepare("INSERT INTO Comments (EventID, UserID, CommentText, Rating) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $event_id, $user_id, $comment_text, $rating);

    if ($stmt->execute()) {
        echo "Comment added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Comment on Event</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Comment on Event</h2>
        <form method="POST" action="comment.php">
            <label for="event_id">Event ID:</label>
            <input type="text" id="event_id" name="event_id" required><br>
            <label for="comment_text">Comment:</label>
            <textarea id="comment_text" name="comment_text" required></textarea><br>
            <label for="rating">Rating:</label>
            <input type="number" id="rating" name="rating" min="1" max="5" required><br>
            <button type="submit">Submit Comment</button>
        </form>
    </div>
</body>
</html>