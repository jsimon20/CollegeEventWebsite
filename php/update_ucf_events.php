<?php
require '../includes/db_connect.php';

// Set the default timezone
date_default_timezone_set('America/New_York'); // Adjust to your timezone

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// UCF Events RSS feed URL
$rss_url = "https://events.ucf.edu/feed.xml";

// Fetch the RSS feed content using curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $rss_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$rss_content = curl_exec($ch);
curl_close($ch);

if ($rss_content === false) {
    echo "Failed to fetch RSS feed.";
    exit;
}

// Check if the content is valid XML
libxml_use_internal_errors(true);
$rss = simplexml_load_string($rss_content);
if ($rss === false) {
    echo "Failed to load RSS feed. Errors: ";
    foreach (libxml_get_errors() as $error) {
        echo "<br>", $error->message;
    }
    libxml_clear_errors();
    exit;
}

// Define allowed categories
$allowed_categories = ['Social', 'Fundraising', 'Tech Talk'];

foreach ($rss->event as $event) {
    $title = (string) $event->title;
    $description = (string) $event->description;
    $event_time = date("Y-m-d H:i:s", strtotime((string) $event->start_date));
    $end_time = date("Y-m-d H:i:s", strtotime((string) $event->end_date));
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

    // Handle missing phone number
    if (empty($contact_phone)) {
        $contact_phone = null;
    }

    // Check if the event already exists in the database
    $stmt = $conn->prepare("SELECT EventID FROM Events WHERE Name = ? AND EventTime = ?");
    $stmt->bind_param("ss", $title, $event_time);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        // Event does not exist, insert it
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO Events (Name, Category, Description, EventTime, EndTime, LocationName, Latitude, Longitude, ContactPhone, ContactEmail, Publicity, RSOID, UniversityID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssdsssis", $title, $category, $description, $event_time, $end_time, $location, $latitude, $longitude, $contact_phone, $contact_email, $publicity, $rso_id, $university_id);

        if ($stmt->execute()) {
            echo "Event '$title' added successfully.<br>";
        } else {
            echo "Error adding event '$title': " . $stmt->error . "<br>";
        }
    } else {
        echo "Event '$title' already exists.<br>";
    }
    $stmt->close();
}
?>