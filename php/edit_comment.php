<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $comment_id = $_POST['comment_id'];
    $comment_text = $_POST['comment_text'];
    $rating = $_POST['rating'];

    $stmt = $conn->prepare("UPDATE Comments SET CommentText = ?, Rating = ? WHERE CommentID = ?");
    $stmt->bind_param("sii", $comment_text, $rating, $comment_id);

    if ($stmt->execute()) {
        // Fetch the event ID to redirect back to the event details page
        $event_stmt = $conn->prepare("SELECT EventID FROM Comments WHERE CommentID = ?");
        $event_stmt->bind_param("i", $comment_id);
        $event_stmt->execute();
        $event_result = $event_stmt->get_result();
        $event_id = $event_result->fetch_assoc()['EventID'];

        header("Location: event_details.php?event_id=$event_id");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>