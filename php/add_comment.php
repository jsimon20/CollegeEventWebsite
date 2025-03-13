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
        header("Location: event_details.php?event_id=$event_id");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>