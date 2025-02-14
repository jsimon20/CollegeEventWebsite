<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected to the database successfully.<br>";
}

// UCF Events RSS feed URL
$rss_url = "https://events.ucf.edu/feed.xml";

// Load the RSS feed
$rss = simplexml_load_file($rss_url);

if ($rss === false) {
    echo "Failed to load RSS feed.";
    exit;
} else {
    echo "RSS feed loaded successfully.<br>";
}

// Debug: Print the structure of the RSS feed
echo "<pre>";
print_r($rss);
echo "</pre>";

// Define allowed categories
$allowed_categories = ['Social', 'Fundraising', 'Tech Talk'];

foreach ($rss->event as $event) {
    $title = (string) $event->title;
    $description = (string) $event->description;
    $event_time = date("Y-m-d H:i:s", strtotime((string) $event->start_date));
    $location = (string) $event->location;
    $category = (string) $event->category;
    
    // Map category to allowed values
    if (!in_array($category, $allowed_categories)) {
        $category = 'Social'; // Default value if category is not allowed
    }
    
    $latitude = null; // Set default value or fetch from RSS if available
    $longitude = null; // Set default value or fetch from RSS if available
    $contact_phone = (string) $event->contact_phone;
    $contact_email = (string) $event->contact_email;
    $publicity = 'Public'; // Default value
    $rso_id = null; // Set default value or fetch from RSS if available
    $university_id = null; // Set default value or fetch from RSS if available

    // Truncate title to fit within the allowed length
    $title = substr($title, 0, 100);

    // Remove specific HTML tags from description
    $description = preg_replace('/<p><em>|<p><span>|<p>/', '', $description);
    $description = strip_tags($description);

    // Print debug information
    echo "Inserting event: $title, $category, $description, $event_time, $location, $latitude, $longitude, $contact_phone, $contact_email, $publicity, $rso_id, $university_id<br>";

    // Insert event into the database
    $stmt = $conn->prepare("INSERT INTO Events (Name, Category, Description, EventTime, LocationName, Latitude, Longitude, ContactPhone, ContactEmail, Publicity, RSOID, UniversityID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssdssssis", $title, $category, $description, $event_time, $location, $latitude, $longitude, $contact_phone, $contact_email, $publicity, $rso_id, $university_id);

    if ($stmt->execute()) {
        echo "Event '$title' added successfully.<br>";
    } else {
        echo "Error adding event '$title': " . $stmt->error . "<br>";
    }
}
?>