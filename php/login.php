<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM Users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Password'])) {
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['Role'] = $user['Role'];
            header("Location: dashboard.php");
        } else {
            echo "Invalid credentials.";
        }
    } else {
        echo "Invalid credentials.";
    }
    $stmt->close();
}
?>
