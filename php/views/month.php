<?php
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/functions.php';
date_default_timezone_set('America/New_York');

$month = isset($_GET['m']) ? (int)$_GET['m'] : date('n');
$year = isset($_GET['y']) ? (int)$_GET['y'] : date('Y');

$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day);
$month_name = date('F', $first_day);
$start_day = date('w', $first_day); // 0 = Sunday

$start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$end_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-$days_in_month";

$query = "SELECT EventID, Name, EventTime FROM Events 
          WHERE Publicity = 'Public' 
          AND DATE(EventTime) BETWEEN '$start_date' AND '$end_date'";
$result = $conn->query($query);

$events_by_date = [];
while ($row = $result->fetch_assoc()) {
    $date_key = date('Y-m-d', strtotime($row['EventTime']));
    $events_by_date[$date_key][] = [
        'id' => $row['EventID'],
        'name' => $row['Name'],
        'time' => date("g:i A", strtotime($row['EventTime']))
    ];
}
?>

<h2><?= $month_name . ' ' . $year ?> Calendar</h2>
<form method="GET" action="index.php" class="calendar-filter-form">
    <input type="hidden" name="view" value="month">
    <select name="m" id="m" class="small-select">
        <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= ($m == $month) ? 'selected' : '' ?>>
                <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
            </option>
        <?php endfor; ?>
    </select>

    <select name="y" id="y" class="small-select">
        <?php for ($y = 2024; $y <= 2026; $y++): ?>
            <option value="<?= $y ?>" <?= ($y == $year) ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
    </select>

    <button type="submit">View</button>
</form>

<table class="calendar">
    <tr>
        <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th>
        <th>Thu</th><th>Fri</th><th>Sat</th>
    </tr>
    <tr>
    <?php
    $day_count = 0;

    for ($i = 0; $i < $start_day; $i++) {
        echo "<td></td>";
        $day_count++;
    }

    for ($day = 1; $day <= $days_in_month; $day++) {
        $date_str = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
        echo "<td><strong>$day</strong><div class='event-cell'>";

        if (isset($events_by_date[$date_str])) {
            foreach ($events_by_date[$date_str] as $event) {
                echo "<a href='php/event_details.php?event_id={$event['id']}'>{$event['time']} – " . htmlspecialchars($event['name']) . "</a>";
            }
        }

        echo "</div></td>";
        $day_count++;
        if ($day_count % 7 == 0) echo "</tr><tr>";
    }

    while ($day_count % 7 != 0) {
        echo "<td></td>";
        $day_count++;
    }
    ?>
    </tr>
</table>
