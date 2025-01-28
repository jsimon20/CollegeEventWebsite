<?php
session_start();
if ($_SESSION['Role'] !== 'Admin' && $_SESSION['Role'] !== 'SuperAdmin') {
    header("Location: unauthorized.php"); // Redirect to an error page
    exit();
}
require 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $adminID = $_SESSION['UserID']; // Assumes the logged-in user is the admin

    $stmt = $conn->prepare("INSERT INTO Events 
        (Name, Category, Description, EventTime, LocationName, Latitude, Longitude, ContactPhone, ContactEmail, Publicity, RSOID, UniversityID)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL)");
    $stmt->bind_param("ssssddssss", $name, $category, $description, $event_time, $location_name, $latitude, $longitude, $contact_phone, $contact_email, $publicity);

    if ($stmt->execute()) {
        echo "Event created successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<form action="create_event.php" method="POST">
    <input type="text" name="name" placeholder="Event Name" required>
    <select name="category" required>
        <option value="Social">Social</option>
        <option value="Fundraising">Fundraising</option>
        <option value="Tech Talk">Tech Talk</option>
    </select>
    <textarea name="description" placeholder="Event Description" required></textarea>
    <input type="datetime-local" name="event_time" required>
    <input type="text" name="location_name" placeholder="Location Name" required>
    <input type="text" name="latitude" placeholder="Latitude" required>
    <input type="text" name="longitude" placeholder="Longitude" required>
    <input type="text" name="contact_phone" placeholder="Contact Phone" required>
    <input type="email" name="contact_email" placeholder="Contact Email" required>
    <select name="publicity" required>
        <option value="Public">Public</option>
        <option value="Private">Private</option>
        <option value="RSO">RSO</option>
    </select>
    <button type="submit">Create Event</button>
</form>
