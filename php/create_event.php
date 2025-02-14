<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $event_time = $_POST['event_time'];
    $location_name = $_POST['location_name'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $contact_phone = $_POST['contact_phone'];
    $contact_email = $_POST['contact_email'];
    $publicity = $_POST['publicity'];
    $rso_id = $_POST['rso_id'];
    $university_id = $_SESSION['university_id'];

    $stmt = $conn->prepare("INSERT INTO Events (Name, Category, Description, EventTime, LocationName, Latitude, Longitude, ContactPhone, ContactEmail, Publicity, RSOID, UniversityID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssdssssis", $name, $category, $description, $event_time, $location_name, $latitude, $longitude, $contact_phone, $contact_email, $publicity, $rso_id, $university_id);

    if ($stmt->execute()) {
        echo "Event created successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Event</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Create Event</h2>
        <form method="POST" action="create_event.php">
            <label for="name">Event Name:</label>
            <input type="text" id="name" name="name" required><br>
            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="Social">Social</option>
                <option value="Fundraising">Fundraising</option>
                <option value="Tech Talk">Tech Talk</option>
            </select><br>
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea><br>
            <label for="event_time">Event Time:</label>
            <input type="datetime-local" id="event_time" name="event_time" required><br>
            <label for="location_name">Location Name:</label>
            <input type="text" id="location_name" name="location_name" required><br>
            <label for="latitude">Latitude:</label>
            <input type="text" id="latitude" name="latitude" required><br>
            <label for="longitude">Longitude:</label>
            <input type="text" id="longitude" name="longitude" required><br>
            <label for="contact_phone">Contact Phone:</label>
            <input type="text" id="contact_phone" name="contact_phone" required><br>
            <label for="contact_email">Contact Email:</label>
            <input type="email" id="contact_email" name="contact_email" required><br>
            <label for="publicity">Publicity:</label>
            <select id="publicity" name="publicity" required>
                <option value="Public">Public</option>
                <option value="Private">Private</option>
                <option value="RSO">RSO</option>
            </select><br>
            <label for="rso_id">RSO ID:</label>
            <input type="text" id="rso_id" name="rso_id" required><br>
            <button type="submit">Create Event</button>
        </form>
    </div>
</body>
</html>