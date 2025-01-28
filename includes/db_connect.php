<?php
$servername = "localhost";
$username = "root"; // Default MAMP username
$password = "root"; // Default MAMP password
$dbname = "CollegeEventDB";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>
