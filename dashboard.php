<?php
session_start();
require 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: php/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Welcome to the College Event Website</h1>
    </header>
    <div class="container">
        <h2>Dashboard</h2>
        <?php if (isAdmin()): ?>
            <a href="php/create_event.php"><button>Create Event</button></a>
            <a href="php/manage_rso.php"><button>Manage RSOs</button></a>
        <?php endif; ?>
        <?php if (isSuperAdmin()): ?>
            <a href="php/approve_event.php"><button>Approve Events</button></a>
            <a href="php/manage_universities.php"><button>Manage Universities</button></a>
            <a href="php/manage_users.php"><button>Manage Users</button></a>
            <a href="php/view_all_events.php"><button>View All Events</button></a>
        <?php endif; ?>
        <?php if (isStudent()): ?>
            <a href="php/view_event.php"><button>View Events</button></a>
            <a href="php/join_rso.php"><button>Join RSO</button></a>
        <?php endif; ?>
        <a href="php/logout.php"><button>Logout</button></a>
    </div>
</body>
</html>