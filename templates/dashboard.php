<?php
session_start();
if ($_SESSION['Role'] === 'SuperAdmin') {
    echo "Welcome, Super Admin!";
    // Show admin-specific options
} elseif ($_SESSION['Role'] === 'Admin') {
    echo "Welcome, Admin!";
    // Show event creation and RSO management
} else {
    echo "Welcome, Student!";
    // Show public/private events and RSO events
}
?>
