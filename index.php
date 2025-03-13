<?php
session_start();
require 'includes/db_connect.php';
require 'includes/functions.php';

// Fetch universities for the dropdown
$universities_query = "SELECT UniversityID, Name FROM Universities";
$universities_result = $conn->query($universities_query);

// Fetch featured events (public events only)
$university_filter = isset($_GET['university_id']) ? $_GET['university_id'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$search_query = isset($_GET['query']) ? $_GET['query'] : '';

// Pagination settings
$events_per_page = 5; // Number of events to display per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $events_per_page;

$query = "SELECT * FROM Events WHERE Publicity = 'Public'";
if ($university_filter) {
    $query .= " AND UniversityID = " . intval($university_filter);
}
if ($category_filter) {
    $query .= " AND Category = '" . $conn->real_escape_string($category_filter) . "'";
}
if ($date_filter) {
    $query .= " AND DATE(EventTime) = '" . $conn->real_escape_string($date_filter) . "'";
}
if ($search_query) {
    $query .= " AND (Name LIKE '%" . $conn->real_escape_string($search_query) . "%' OR Description LIKE '%" . $conn->real_escape_string($search_query) . "%')";
}
$query .= " ORDER BY DATE(EventTime) DESC, TIME(EventTime) ASC LIMIT $events_per_page OFFSET $offset";
$result = $conn->query($query);

// Get total number of events for pagination
$total_query = "SELECT COUNT(*) as total FROM Events WHERE Publicity = 'Public'";
if ($university_filter) {
    $total_query .= " AND UniversityID = " . intval($university_filter);
}
if ($category_filter) {
    $total_query .= " AND Category = '" . $conn->real_escape_string($category_filter) . "'";
}
if ($date_filter) {
    $total_query .= " AND DATE(EventTime) = '" . $conn->real_escape_string($date_filter) . "'";
}
if ($search_query) {
    $total_query .= " AND (Name LIKE '%" . $conn->real_escape_string($search_query) . "%' OR Description LIKE '%" . $conn->real_escape_string($search_query) . "%')";
}
$total_result = $conn->query($total_query);
$total_events = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_events / $events_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>College Event Website</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <style>
        /* Inline styles for testing */
        body {
            background-color: #f4f4f4;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        .menu-icon {
            font-size: 1.5em;
            cursor: pointer;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }
        .dropdown-menu a {
            display: block;
            padding: 10px 20px;
            color: #333;
            text-decoration: none;
        }
        .dropdown-menu a:hover {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>College Events</h1>
            <nav>
                <?php if (isLoggedIn()): ?>
                    <div class="menu">
                        <div class="menu-icon" onclick="toggleMenu()">&#9776;</div>
                        <div class="dropdown-menu" id="dropdownMenu">
                            <a href="php/user_profile.php">Account Settings</a>
                            <a href="php/my_calendar.php">My Calendar</a>
                            <a href="php/logout.php">Log Out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="php/login.php">Login</a>
                    <a href="php/register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <div class="container">
        <h2>Featured Events</h2>
        <form method="GET" action="index.php">
            <input type="text" name="query" placeholder="Search by name or description" value="<?php echo $search_query; ?>">
            <label for="university_id">University:</label>
            <select id="university_id" name="university_id">
                <option value="">All Universities</option>
                <?php while ($university = $universities_result->fetch_assoc()): ?>
                    <option value="<?php echo $university['UniversityID']; ?>" <?php if ($university_filter == $university['UniversityID']) echo 'selected'; ?>>
                        <?php echo $university['Name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <label for="category">Category:</label>
            <select id="category" name="category">
                <option value="">All Categories</option>
                <option value="Social" <?php if ($category_filter == 'Social') echo 'selected'; ?>>Social</option>
                <option value="Fundraising" <?php if ($category_filter == 'Fundraising') echo 'selected'; ?>>Fundraising</option>
                <option value="Tech Talk" <?php if ($category_filter == 'Tech Talk') echo 'selected'; ?>>Tech Talk</option>
            </select>
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" value="<?php echo $date_filter; ?>">
            <button type="submit">Filter</button>
        </form>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="event">
                <h3><?php echo $row['Name']; ?></h3>
                <p><?php echo $row['Description']; ?></p>
                <p><strong>Date and Time:</strong> 
                    <?php 
                    $event_time = strtotime($row['EventTime']);
                    $formatted_time = date("g:i A", $event_time);
                    if (date("i", $event_time) == "00") {
                        $formatted_time = date("g A", $event_time);
                    }
                    echo date("F j, Y, ", $event_time) . $formatted_time;
                    ?>
                </p>
                <p><strong>Location:</strong> <?php echo !empty($row['LocationName']) ? $row['LocationName'] : 'Virtual'; ?></p>
                <a href="php/event_details.php?event_id=<?php echo $row['EventID']; ?>"><button>View Details</button></a>
            </div>
        <?php endwhile; ?>

        <!-- Pagination controls -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleMenu() {
            var menu = document.getElementById('dropdownMenu');
            if (menu.style.display === 'block') {
                menu.style.display = 'none';
            } else {
                menu.style.display = 'block';
            }
        }
    </script>
</body>
</html>