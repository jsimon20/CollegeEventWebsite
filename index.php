<?php
session_start();
include_once 'php/update_ucf_events.php';
require 'includes/db_connect.php';
require 'includes/functions.php';

// Fetch universities for the dropdown
$universities_query = "SELECT UniversityID, Name FROM Universities";
$universities_result = $conn->query($universities_query);

// Get view type
$view = $_GET['view'] ?? 'featured';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>College Event Website</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <script>
        function switchView(view) {
            const params = new URLSearchParams(window.location.search);
            params.set('view', view);
            window.location.search = params.toString();
        }
    </script>
</head>
<body>
    <header>
        <div class="header-content">
        <h1><a href="index.php?view=day" style="text-decoration: none; color: inherit;">College Events</a></h1>
        <nav>
                <?php if (isLoggedIn()): ?>
                    <div class="menu">
                        <div class="menu-icon" onclick="toggleMenu()">&#9776;</div>
                        <div class="dropdown-menu" id="dropdownMenu">
                            <a href="php/user_profile.php">Account Settings</a>
                            <?php if (isStudent()): ?>
                                <a href="php/join_rso.php">Join RSOs</a>
                                <a href="php/view_rsos.php">View RSOs</a>
                            <?php endif; ?>
                            <?php if (isLoggedIn() && isAdmin()): ?>
                                <a href="php/create_rso.php">Create RSO</a>
                                <a href="php/create_event.php">Create Event</a>
                                <a href="php/my_rso.php">Manage My RSO</a>
                            <?php endif; ?>

                            <a href="php/logout.php">Log Out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="php/login.php">Login</a>
                    <a href="php/register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
        <div class="tabs">
            <button class="tab-btn <?php if($view == 'day') echo 'active'; ?>" onclick="switchView('day')">Day</button>
            <button class="tab-btn <?php if($view == 'week') echo 'active'; ?>" onclick="switchView('week')">Week</button>
            <button class="tab-btn <?php if($view == 'upcoming') echo 'active'; ?>" onclick="switchView('upcoming')">Upcoming</button>
            <button class="tab-btn <?php if($view == 'month') echo 'active'; ?>" onclick="switchView('month')">Month</button>
        </div>
    </header>

    <div class="container">
        <?php
        switch ($view) {
            case 'day':
                include 'php/views/day.php';
                break;
            case 'week':
                include 'php/views/week.php';
                break;
            case 'upcoming':
                include 'php/views/upcoming.php';
                break;
            case 'month':
                include 'php/views/month.php';
                break;
            default:
                include 'php/views/day.php';
        }
        ?>
    </div>

    <script>
        function toggleMenu() {
            const menu = document.getElementById('dropdownMenu');
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        }
    </script>
</body>
</html>
