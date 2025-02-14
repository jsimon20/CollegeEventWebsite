<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php"); // Redirect logged-in users
    exit();
} else {
    header("Location: php/login.php"); // Redirect guests to login page
    exit();
}
?>