<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isSuperAdmin()) {
    header("Location: login.php");
    exit;
}

$event_id = $_POST['event_id'];
$action = $_POST['action'];

if ($action === 'approve') {
    $stmt = $conn->prepare("UPDATE Events SET NeedsApproval = 0 WHERE EventID = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
} elseif ($action === 'decline') {
    $stmt = $conn->prepare("DELETE FROM Events WHERE EventID = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
}

header("Location: approve_events.php");
exit;
