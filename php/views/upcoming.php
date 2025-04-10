<?php
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/functions.php';
date_default_timezone_set('America/New_York');

$now = date('Y-m-d H:i:s');
$university_filter = $_GET['university_id'] ?? '';
$category_filter = $_GET['category'] ?? '';
$search_query = $_GET['query'] ?? '';

$query = "SELECT * FROM Events WHERE Publicity = 'Public' AND EventTime >= '$now'";

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
<?php else: ?>
    <p>No upcoming events.</p>
<?php endif; ?>
