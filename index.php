<?php
session_start();
if (isset($_SESSION['UserID'])) {
    header("Location: templates/dashboard.php"); // Redirect logged-in users
    exit();
} else {
    header("Location: html/login.html"); // Redirect guests to login page
    exit();
}
?>
