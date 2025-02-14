<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin';
}

function isSuperAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'SuperAdmin';
}

function isStudent() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Student';
}
?>