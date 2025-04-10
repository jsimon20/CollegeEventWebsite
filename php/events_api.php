<?php
require_once(__DIR__ . '/../includes/db_connect.php');
header('Content-Type: application/json');

$result = $conn->query("SELECT Name, EventTime, EndTime, Description FROM Events WHERE EventTime >= NOW()");
$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['Name'],
        'start' => $row['EventTime'],
        'end' => $row['EndTime'],
        'description' => $row['Description']
    ];
}

echo json_encode($events);
?>
