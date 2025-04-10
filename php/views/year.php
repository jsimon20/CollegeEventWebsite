<?php
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/functions.php';

$year = isset($_GET['y']) ? (int)$_GET['y'] : date('Y');
$events = [];

$query = "SELECT EventID, Name, DATE(EventTime) as EventDate FROM Events WHERE Publicity = 'Public' AND YEAR(EventTime) = $year";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $events[$row['EventDate']][] = [
        'name' => $row['Name'],
        'id' => $row['EventID']
    ];
}
?>

<h2><?= $year ?> Calendar</h2>
<div class="year-grid">
<?php
for ($month = 1; $month <= 12; $month++) {
    $first_day = mktime(0, 0, 0, $month, 1, $year);
    $days_in_month = date('t', $first_day);
    $month_name = date('F', $first_day);
    $start_day = date('w', $first_day);

    echo "<div class='month-box'>";
    echo "<h3><a href='index.php?view=month&m=$month&y=$year'>$month_name</a></h3>";
    echo "<table class='calendar'><tr>
        <th>Su</th><th>Mo</th><th>Tu</th><th>We</th>
        <th>Th</th><th>Fr</th><th>Sa</th></tr><tr>";

    $day_count = 0;
    for ($i = 0; $i < $start_day; $i++) {
        echo "<td></td>";
        $day_count++;
    }

    for ($day = 1; $day <= $days_in_month; $day++) {
        $date_str = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
        echo "<td><a href='index.php?view=day&date=$date_str'>$day</a>";

        if (isset($events[$date_str])) {
            foreach ($events[$date_str] as $event) {
                echo "<br><a href='php/event_details.php?event_id={$event['id']}'>" . htmlspecialchars($event['name']) . "</a>";
            }
        }

        echo "</td>";
        $day_count++;
        if ($day_count % 7 == 0) echo "</tr><tr>";
    }

    while ($day_count % 7 != 0) {
        echo "<td></td>";
        $day_count++;
    }

    echo "</tr></table></div>";
}
?>
</div>
