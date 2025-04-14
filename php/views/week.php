<?php
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/functions.php';
date_default_timezone_set('America/New_York');

$today = date('Y-m-d');
$start_of_week = date('Y-m-d', strtotime('last sunday', strtotime($today)));
if (date('w', strtotime($today)) == 0) {
    $start_of_week = $today;
}
$end_of_week = date('Y-m-d', strtotime($start_of_week . ' +6 days'));

$university_filter = $_GET['university_id'] ?? '';
$category_filter = $_GET['category'] ?? '';
$search_query = $_GET['query'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$events_per_page = 10;

$user_id = $_SESSION['user_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? null;
$user_university_id = $_SESSION['university_id'] ?? null;

// Get user's RSO membership list
$rso_ids = [];
if ($user_id) {
    $rso_query = $conn->prepare("SELECT RSOID FROM RSO_Members WHERE UserID = ?");
    $rso_query->bind_param("i", $user_id);
    $rso_query->execute();
    $rso_result = $rso_query->get_result();
    while ($row = $rso_result->fetch_assoc()) {
        $rso_ids[] = $row['RSOID'];
    }
}

$query = "SELECT * FROM Events 
          WHERE DATE(EventTime) BETWEEN '$start_of_week' AND '$end_of_week'";

if ($user_id) {
    $query .= " AND (
        Publicity = 'Public'
        OR (Publicity = 'Private' AND UniversityID = $user_university_id)
        " . (!empty($rso_ids) ? "OR (Publicity = 'RSO' AND RSOID IN (" . implode(',', $rso_ids) . "))" : "") . "
    )";
} else {
    $query .= " AND Publicity = 'Public'";
}

if ($university_filter) {
    $query .= " AND UniversityID = " . intval($university_filter);
}
if ($category_filter) {
    $query .= " AND Category = '" . $conn->real_escape_string($category_filter) . "'";
}
if ($search_query) {
    $escaped_query = $conn->real_escape_string($search_query);
    $query .= " AND (Name LIKE '%$escaped_query%' OR Description LIKE '%$escaped_query%')";
}

$query .= " ORDER BY EventTime ASC";
$result = $conn->query($query);

$all_events = [];
while ($row = $result->fetch_assoc()) {
    $date = date('Y-m-d', strtotime($row['EventTime']));
    $all_events[$date][] = $row;
}

// Pagination
$event_list = [];
foreach ($all_events as $day_events) {
    foreach ($day_events as $e) {
        $event_list[] = $e;
    }
}
$total_events = count($event_list);
$total_pages = ceil($total_events / $events_per_page);
$paged_events = array_slice($event_list, ($page - 1) * $events_per_page, $events_per_page);

$universities = $conn->query("SELECT UniversityID, Name FROM Universities");
?>

<h2>This Week's Events</h2>
<form method="GET" action="index.php">
    <input type="hidden" name="view" value="week">
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

<?php
$current = null;
foreach ($paged_events as $event):
    $event_date = date('Y-m-d', strtotime($event['EventTime']));
    $formatted_date = date('l, F j, Y', strtotime($event_date));
    if ($event_date !== $current) {
        if ($current !== null) echo "</div>";
        echo "<div class='weekly-day-block'><h3 class='weekly-date'>$formatted_date</h3>";
        $current = $event_date;
    }
?>
    <div class="event-card">
        <h4><?= htmlspecialchars($event['Name']) ?></h4>
        <p><?= htmlspecialchars($event['Description']) ?></p>
        <p><strong>Time:</strong> <?= date("g:i A", strtotime($event['EventTime'])) ?></p>
        <p><strong>Location:</strong> <?= $event['LocationName'] ?: 'Virtual' ?></p>
        <a href="php/event_details.php?event_id=<?= $event['EventID'] ?>"><button>View Details</button></a>
    </div>
<?php endforeach; ?>
<?php if ($current !== null) echo "</div>"; ?>

<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?view=week&page=<?= $page - 1 ?>">&laquo; Previous</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?view=week&page=<?= $i ?>" <?= ($i == $page) ? 'class="active"' : '' ?>><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $total_pages): ?>
        <a href="?view=week&page=<?= $page + 1 ?>">Next &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>
