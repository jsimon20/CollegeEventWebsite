<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['UserID']);
}

function isAdmin() {
    return (
        (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin') ||
        (isset($_SESSION['UserType']) && $_SESSION['UserType'] === 'Admin')
    );
}

function isSuperAdmin() {
    return (
        (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'SuperAdmin') ||
        (isset($_SESSION['UserType']) && $_SESSION['UserType'] === 'SuperAdmin')
    );
}

function isStudent() {
    return (
        (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Student') ||
        (isset($_SESSION['UserType']) && $_SESSION['UserType'] === 'Student')
    );
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}
?>
