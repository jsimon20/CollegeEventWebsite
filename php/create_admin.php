<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isSuperAdmin()) {
    header("Location: ../unauthorized.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $university_id = $_SESSION['university_id']; // Super Admin's university

    $stmt = $conn->prepare("INSERT INTO Users (Username, Password, Email, UserType, UniversityID) VALUES (?, ?, ?, 'Admin', ?)");
    $stmt->bind_param("sssi", $username, $password, $email, $university_id);

    if ($stmt->execute()) {
        echo "Admin created successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>