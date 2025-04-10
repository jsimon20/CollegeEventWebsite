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

$query = "SELECT * FROM Events 
          WHERE Publicity = 'Public' 
          AND DATE(EventTime) BETWEEN '$start_of_week' AND '$end_of_week'";

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

// Ensure all days of the week are included in the $all_events array
$current = strtotime($start_of_week);
while ($current <= strtotime($end_of_week)) {
    $date_key = date('Y-m-d', $current);
    if (!isset($all_events[$date_key])) {
        $all_events[$date_key] = []; // Add empty array for days with no events
    }
    $current = strtotime('+1 day', $current);
}

$total_events = count($all_events);
$total_pages = ceil($total_events / $events_per_page);
$offset = ($page - 1) * $events_per_page;

$paginated_grouped = [];
foreach ($all_events as $date => $events) {
    $paginated_grouped[$date] = $events;
}

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
$current = strtotime($start_of_week);
$end = strtotime($end_of_week);
while ($current <= $end):
    $date_key = date('Y-m-d', $current);
    $readable = date('l, F j, Y', $current);
    echo "<div class='weekly-day-block'>";
    echo "<h3 class='weekly-date'>$readable</h3>";

    if (!empty($paginated_grouped[$date_key])):
        foreach ($paginated_grouped[$date_key] as $event): ?>
            <div class="event-card">
                <h4><?= htmlspecialchars($event['Name']) ?></h4>
                <p><?= htmlspecialchars($event['Description']) ?></p>
                <p><strong>Time:</strong> <?= date("g:i A", strtotime($event['EventTime'])) ?></p>
                <p><strong>Location:</strong> <?= $event['LocationName'] ?: 'Virtual' ?></p>
                <a href="php/event_details.php?event_id=<?= $event['EventID'] ?>"><button>View Details</button></a>
            </div>
        <?php endforeach;
    else:
        echo "<p class='no-events'>No events.</p>";
    endif;

    echo "</div>";
    $current = strtotime('+1 day', $current);
endwhile;
?>

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