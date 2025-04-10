<?php
require_once(__DIR__ . '/../includes/db_connect.php');
date_default_timezone_set('America/New_York');

$now = date("Y-m-d H:i:s");

$rss_url = "https://events.ucf.edu/feed.xml";

// Fetch RSS feed
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

// Load XML
libxml_use_internal_errors(true);
$rss = simplexml_load_string($rss_content);
if ($rss === false) {
    echo "Failed to load RSS feed.";
    foreach (libxml_get_errors() as $error) {
        echo "<br>", $error->message;
    }
    libxml_clear_errors();
    exit;
}

$allowed_categories = ['Social', 'Fundraising', 'Tech Talk'];

foreach ($rss->event as $event) {
    $title = substr((string) $event->title, 0, 100);
    $description = strip_tags((string) $event->description);
    $event_time = date("Y-m-d H:i:s", strtotime((string) $event->start_date));
    $end_time = date("Y-m-d H:i:s", strtotime((string) $event->end_date));

    // Skip if start or end is missing or the event has already ended
    if (empty($event_time) || empty($end_time) || strtotime($end_time) < strtotime($now)) {
        continue;
    }

    $location = (string) $event->location;
    $category = (string) $event->category;
    if (!in_array($category, $allowed_categories)) {
        $category = 'Social';
    }

    $latitude = null;
    $longitude = null;
    $contact_phone = !empty($event->contact_phone) ? (string) $event->contact_phone : null;
    $contact_email = (string) $event->contact_email;
    $publicity = 'Public';
    $rso_id = null;
    $university_id = 1;

    // Avoid duplicates by name + EventTime
    $stmt = $conn->prepare("SELECT EventID FROM Events WHERE Name = ? AND EventTime = ?");
    $stmt->bind_param("ss", $title, $event_time);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO Events 
            (Name, Category, Description, EventTime, EndTime, LocationName, Latitude, Longitude, ContactPhone, ContactEmail, Publicity, RSOID, UniversityID, Approved) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sssssssddssii",
            $title, $category, $description, $event_time, $end_time, $location, $latitude, $longitude,
            $contact_phone, $contact_email, $publicity, $rso_id, $university_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt->close();
    }
}
?>
