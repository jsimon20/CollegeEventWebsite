<?php
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/functions.php';

$user_id = $_SESSION['user_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? null;
$user_university_id = $_SESSION['university_id'] ?? null;

// RSO membership list
$rso_ids = [];
if ($user_id) {
    $stmt = $conn->prepare("SELECT RSOID FROM RSO_Members WHERE UserID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $rso_result = $stmt->get_result();
    while ($row = $rso_result->fetch_assoc()) {
        $rso_ids[] = $row['RSOID'];
    }
}

// Month and year selection
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$start_date = "$year-$month-01";
$end_date = date("Y-m-t", strtotime($start_date));

// Query for events this month with visibility checks
$query = "SELECT * FROM Events WHERE DATE(EventTime) BETWEEN '$start_date' AND '$end_date'";
if ($user_id) {
    $query .= " AND (
        Publicity = 'Public'
        OR (Publicity = 'Private' AND UniversityID = $user_university_id)
        " . (!empty($rso_ids) ? "OR (Publicity = 'RSO' AND RSOID IN (" . implode(',', $rso_ids) . "))" : "") . "
    )";
} else {
    $query .= " AND Publicity = 'Public'";
}
$query .= " ORDER BY EventTime ASC";
$result = $conn->query($query);

// Group events by date
$events_by_day = [];
while ($row = $result->fetch_assoc()) {
    $day = date('j', strtotime($row['EventTime']));
    $events_by_day[$day][] = $row;
}
?>

<h2><?= date('F Y', strtotime($start_date)) ?> Calendar</h2>
<form method="GET" action="index.php">
    <input type="hidden" name="view" value="month">
    <select name="month">
        <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= ($m == $month) ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
        <?php endfor; ?>
    </select>
    <select name="year">
        <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
            <option value="<?= $y ?>" <?= ($y == $year) ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
    </select>
    <button type="submit">View</button>
</form>

<div class="calendar-grid">
    <?php
    $first_day_of_month = date('w', strtotime($start_date));
    $days_in_month = date('t', strtotime($start_date));
    $day_counter = 1;

    echo "<div class='calendar-row'>";
    // Print empty cells for first week
    for ($i = 0; $i < $first_day_of_month; $i++) {
        echo "<div class='calendar-cell empty'></div>";
    }

    for ($i = $first_day_of_month; $i < 7; $i++) {
        echo "<div class='calendar-cell'>";
        echo "<strong>$day_counter</strong>";
        if (isset($events_by_day[$day_counter])) {
            foreach ($events_by_day[$day_counter] as $event) {
                echo "<div class='event-link'>";
                echo "<a href='php/event_details.php?event_id={$event['EventID']}'>" . htmlspecialchars($event['Name']) . "</a>";
                echo "</div>";
            }
        }
        echo "</div>";
        $day_counter++;
    }
    echo "</div>";

    while ($day_counter <= $days_in_month) {
        echo "<div class='calendar-row'>";
        for ($i = 0; $i < 7 && $day_counter <= $days_in_month; $i++) {
            echo "<div class='calendar-cell'>";
            echo "<strong>$day_counter</strong>";
            if (isset($events_by_day[$day_counter])) {
                foreach ($events_by_day[$day_counter] as $event) {
                    echo "<div class='event-link'>";
                    echo "<a href='php/event_details.php?event_id={$event['EventID']}'>" . htmlspecialchars($event['Name']) . "</a>";
                    echo "</div>";
                }
            }
            echo "</div>";
            $day_counter++;
        }
        echo "</div>";
    }
    ?>
</div>

<style>
.calendar-grid {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.calendar-row {
    display: flex;
}
.calendar-cell {
    flex: 1;
    min-height: 100px;
    border: 1px solid #ccc;
    padding: 5px;
    box-sizing: border-box;
}
.calendar-cell.empty {
    background-color: #f0f0f0;
}
.event-link a {
    display: block;
    font-size: 0.9em;
    color: #0077cc;
}
</style>
