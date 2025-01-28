<?php
require 'includes/db_connect.php';

$sql = "SELECT * FROM Events WHERE Publicity = 'Public'";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<h2>" . $row['Name'] . "</h2>";
    echo "<p>" . $row['Description'] . "</p>";
    echo "<p>Time: " . $row['EventTime'] . "</p>";
    echo "<p>Location: " . $row['LocationName'] . "</p>";
    echo "<hr>";
}
?>
