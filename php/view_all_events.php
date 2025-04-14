<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isSuperAdmin()) {
    header("Location: ../unauthorized.php");
    exit;
}

$search_query = $_GET['query'] ?? '';
$university_filter = $_GET['university_id'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$events_per_page = 5;
$offset = ($page - 1) * $events_per_page;

// Build base query
$base_query = "FROM Events WHERE 1=1";

if ($search_query) {
    $escaped_query = $conn->real_escape_string($search_query);
    $base_query .= " AND (Name LIKE '%$escaped_query%' OR Description LIKE '%$escaped_query%')";
}

if ($university_filter) {
    $base_query .= " AND UniversityID = " . intval($university_filter);
}

if ($category_filter) {
    $escaped_category = $conn->real_escape_string($category_filter);
    $base_query .= " AND Category = '$escaped_category'";
}

$count_result = $conn->query("SELECT COUNT(*) as total $base_query");
$total_events = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_events / $events_per_page);

$query = "SELECT * $base_query ORDER BY EventTime ASC LIMIT $events_per_page OFFSET $offset";
$result = $conn->query($query);
$universities = $conn->query("SELECT UniversityID, Name FROM Universities");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Events - Super Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="container">
    <h2>All Events</h2>
    <form method="GET" action="view_all_events.php">
        <input type="text" name="query" placeholder="Search events..." value="<?= htmlspecialchars($search_query) ?>">
        
        <label for="university_id">University:</label>
        <select name="university_id" id="university_id">
            <option value="">All Universities</option>
            <?php while ($uni = $universities->fetch_assoc()): ?>
                <option value="<?= $uni['UniversityID'] ?>" <?= ($university_filter == $uni['UniversityID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($uni['Name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="category">Category:</label>
        <select name="category" id="category">
            <option value="">All Categories</option>
            <option value="Social" <?= ($category_filter == 'Social') ? 'selected' : '' ?>>Social</option>
            <option value="Fundraising" <?= ($category_filter == 'Fundraising') ? 'selected' : '' ?>>Fundraising</option>
            <option value="Tech Talk" <?= ($category_filter == 'Tech Talk') ? 'selected' : '' ?>>Tech Talk</option>
        </select>

        <button type="submit">Filter</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="event">
                <h3><?= htmlspecialchars($row['Name']) ?></h3>
                <p><?= htmlspecialchars($row['Description']) ?></p>
                <p><strong>Date:</strong> <?= date("F j, Y", strtotime($row['EventTime'])) ?></p>
                <p><strong>Time:</strong> <?= date("g:i A", strtotime($row['EventTime'])) ?></p>
                <p><strong>Location:</strong> <?= $row['LocationName'] ?: 'Virtual' ?></p>
                <p><strong>Publicity:</strong> <?= $row['Publicity'] ?></p>
                <a href="event_details.php?event_id=<?= $row['EventID'] ?>"><button>View Details</button></a>
            </div>
        <?php endwhile; ?>

        <!-- Pagination Controls -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&query=<?= urlencode($search_query) ?>&university_id=<?= $university_filter ?>&category=<?= $category_filter ?>">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&query=<?= urlencode($search_query) ?>&university_id=<?= $university_filter ?>&category=<?= $category_filter ?>" <?= ($i == $page) ? 'class="active"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&query=<?= urlencode($search_query) ?>&university_id=<?= $university_filter ?>&category=<?= $category_filter ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p>No events found.</p>
    <?php endif; ?>

    <a href="../dashboard.php"><button>Back to Dashboard</button></a>
</div>
</body>
</html>
