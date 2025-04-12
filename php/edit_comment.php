<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();
redirectIfNotLoggedIn();

$comment_id = $_POST['comment_id'] ?? null;
$new_text = trim($_POST['comment_text'] ?? '');
$new_rating = $_POST['rating'] ?? null;

$user_id = $_SESSION['user_id'];

if (!$comment_id || !$new_text) {
    die("Invalid data.");
}

// Get comment details to verify ownership
$stmt = $conn->prepare("SELECT EventID, UserID, Type FROM Comments WHERE CommentID = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$comment = $stmt->get_result()->fetch_assoc();

if ($comment['UserID'] != $user_id) {
    die("You can only edit your own comment.");
}

if ($comment['Type'] === 'Review') {
    $update_stmt = $conn->prepare("UPDATE Comments SET CommentText = ?, Rating = ? WHERE CommentID = ?");
    $update_stmt->bind_param("sii", $new_text, $new_rating, $comment_id);
} else {
    $update_stmt = $conn->prepare("UPDATE Comments SET CommentText = ? WHERE CommentID = ?");
    $update_stmt->bind_param("si", $new_text, $comment_id);
}

$update_stmt->execute();
header("Location: event_details.php?event_id=" . $comment['EventID']);
exit;
