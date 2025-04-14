<?php
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/functions.php';
date_default_timezone_set('America/New_York');

$now = date('Y-m-d H:i:s');
$university_filter = $_GET['university_id'] ?? '';
$category_filter = $_GET['category'] ?? '';
$search_query = $_GET['query'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$events_per_page = 10;
$offset = ($page - 1) * $events_per_page;

$is_logged_in = isLoggedIn();
$user_id = $_SESSION['user_id'] ?? null;
$university_id = $_SESSION['university_id'] ?? null;
$rso_ids = [];

// Get RSOs the user is a member of (if logged in)
if ($is_logged_in) {
    $rso_query = $conn->prepare("SELECT RSOID FROM RSO_Members WHERE UserID = ?");
    $rso_query->bind_param("i", $user_id);
    $rso_query->execute();
    $rso_result = $rso_query->get_result();
    while ($row = $rso_result->fetch_assoc()) {
        $rso_ids[] = $row['RSOID'];
    }
    $rso_query->close();
}

$conditions = ["EventTime >= '$now'"];
$public_clause = "Publicity = 'Public'";

// For logged-in users, allow access to Private events (same university) and RSO events (matching RSOID)
if ($is_logged_in) {
    $private_clause = "Publicity = 'Private' AND UniversityID = " . intval($university_id);
    $rso_clause = $rso_ids ? "Publicity = 'RSO' AND RSOID IN (" . implode(',', array_map('intval', $rso_ids)) . ")" : "0";
    $conditions[] = "($public_clause OR $private_clause OR $rso_clause)";
} else {
    $conditions[] = $public_clause;
}

// Apply filters
if ($university_filter) {
    $conditions[] = "UniversityID = " . intval($university_filter);
}
if ($category_filter) {
    $conditions[] = "Category = '" . $conn->real_escape_string($category_filter) . "'";
}
if ($search_query) {
    $escaped_query = $conn->real_escape_string($search_query);
    $conditions[] = "(Name LIKE '%$escaped_query%' OR Description LIKE '%$escaped_query%')";
}

$where_clause = implode(" AND ", $conditions);

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM Events WHERE $where_clause";
$total_events = $conn->query($count_query)->fetch_assoc()['total'];
$total_pages = ceil($total_events / $events_per_page);

// Final event query
$query = "SELECT * FROM Events WHERE $where_clause ORDER BY EventTime ASC LIMIT $events_per_page OFFSET $offset";
$result = $conn->query($query);

// Fetch universities
$universities = $conn->query("SELECT UniversityID, Name FROM Universities");
?>

<h2>Upcoming Events</h2>
<form method="GET" action="index.php">
    <input type="hidden" name="view" value="upcoming">
    <input type="text" name="query" placeholder="Search by name or description" value="<?= htmlspecialchars($search_query) ?>">
    <label for="university_id">University:</label>
    <select id="university_id" name="university_id">
        <option value="">All Universities</option>
        <?php while ($uni = $universities->fetch_assoc()): ?>
            <option value="<?= $uni['UniversityID'] ?>" <?= ($university_filter == $uni['UniversityID']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($uni['Name']) ?>
            </option>
        <?php endwhile; ?>
    </select>
    <label for="category">Category:</label>
    <select id="category" name="category">
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
            <a href="php/event_details.php?event_id=<?= $row['EventID'] ?>"><button>View Details</button></a>
        </div>
    <?php endwhile; ?>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="index.php?view=upcoming&page=<?= $page - 1 ?>">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="index.php?view=upcoming&page=<?= $i ?>" <?= ($i == $page) ? 'class="active"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="index.php?view=upcoming&page=<?= $page + 1 ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <p>No upcoming events found.</p>
<?php endif; ?>
