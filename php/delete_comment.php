<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();
redirectIfNotLoggedIn();

$comment_id = $_POST['comment_id'] ?? null;
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

if (!$comment_id) {
    die("Invalid comment.");
}

// Get comment details
$stmt = $conn->prepare("SELECT EventID, UserID FROM Comments WHERE CommentID = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$comment = $stmt->get_result()->fetch_assoc();
$event_id = $comment['EventID'];
$comment_user_id = $comment['UserID'];

// Check if this user owns the comment
$can_delete = $comment_user_id == $user_id;

// If admin, also allow delete if they control the RSO
if (!$can_delete && $user_type === 'Admin') {
    $admin_id = $user_id;
    $rso_check = $conn->prepare("
        SELECT 1 FROM Events E
        JOIN RSOs R ON E.RSOID = R.RSOID
        WHERE E.EventID = ? AND R.AdminID = ?
    ");
    $rso_check->bind_param("ii", $event_id, $admin_id);
    $rso_check->execute();
    $can_delete = $rso_check->get_result()->num_rows > 0;
}

if ($can_delete) {
    $delete_stmt = $conn->prepare("DELETE FROM Comments WHERE CommentID = ?");
    $delete_stmt->bind_param("i", $comment_id);
    $delete_stmt->execute();
}

header("Location: event_details.php?event_id=" . $event_id);
exit;
