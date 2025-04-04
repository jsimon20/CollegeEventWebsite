<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isSuperAdmin()) {
    header("Location: ../unauthorized.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];

    $stmt = $conn->prepare("UPDATE Users SET UserType = 'Admin' WHERE UserID = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "Admin approved successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>