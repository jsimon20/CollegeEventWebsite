<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $rso_id = $_POST['rso_id'];

    $stmt = $conn->prepare("INSERT INTO RSO_Members (UserID, RSOID) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $rso_id);

    if ($stmt->execute()) {
        echo "Successfully joined the RSO!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join RSO</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Join RSO</h2>
        <form method="POST" action="join_rso.php">
            <label for="rso_id">RSO ID:</label>
            <input type="text" id="rso_id" name="rso_id" required><br>
            <button type="submit">Join RSO</button>
        </form>
    </div>
</body>
</html>