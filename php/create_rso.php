<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $admin_id = $_SESSION['user_id'];
    $university_id = $_SESSION['university_id'];

    $stmt = $conn->prepare("INSERT INTO RSOs (Name, Description, AdminID, UniversityID) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $name, $description, $admin_id, $university_id);

    if ($stmt->execute()) {
        echo "RSO created successfully!";
    } else {
        echo "Error creating RSO: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create RSO</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Create RSO</h1>
    <form method="post" action="create_rso.php">
        <label for="name">RSO Name:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>
        <br>
        <button type="submit">Create RSO</button>
    </form>
</body>
</html>