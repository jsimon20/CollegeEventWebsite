<?php
session_start();
require 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_SESSION['UserID'];
    $rsoID = $_POST['rso_id']; // From the form

    $stmt = $conn->prepare("INSERT INTO RSO_Members (UserID, RSOID) VALUES (?, ?)");
    $stmt->bind_param("ii", $userID, $rsoID);

    if ($stmt->execute()) {
        echo "Successfully joined the RSO!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
