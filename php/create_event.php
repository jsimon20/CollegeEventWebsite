<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();
redirectIfNotLoggedIn();

if (!isAdmin()) {
    die("Access denied.");
}

$admin_id = $_SESSION['UserID'] ?? $_SESSION['user_id'] ?? null;
$university_id = $_SESSION['UniversityID'] ?? $_SESSION['university_id'] ?? null;
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $description = trim($_POST['description']);
    $datetime = $_POST['datetime'];
    $location_name = trim($_POST['location_name']);
    $contact_phone = trim($_POST['contact_phone']);
    $contact_email = trim($_POST['contact_email']);
    $publicity = $_POST['publicity'];
    $rso_id = !empty($_POST['rso_id']) ? (int)$_POST['rso_id'] : null;

    // Needs approval only if no RSO selected
    $needs_approval = is_null($rso_id) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO Events (Name, Category, Description, EventTime, LocationName, ContactPhone, ContactEmail, Publicity, UniversityID, AdminID, RSOID, NeedsApproval) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssiiiii",
        $name,
        $category,
        $description,
        $datetime,
        $location_name,
        $contact_phone,
        $contact_email,
        $publicity,
        $university_id,
        $admin_id,
        $rso_id,
        $needs_approval
    );

    if ($stmt->execute()) {
        $success = true;
    } else {
        $error = "Failed to create event: " . $stmt->error;
    }
}

// Fetch RSOs the admin manages
$rsos_result = $conn->prepare("SELECT RSOID, Name FROM RSOs WHERE AdminID = ?");
$rsos_result->bind_param("i", $admin_id);
$rsos_result->execute();
$rsos = $rsos_result->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Event</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="container">
    <h2>Create Event</h2>
    <a href="../index.php?view=day"><button type="button">← Back to Events</button></a>

    <?php if ($success): ?>
        <p style="color: green;">Event created successfully!</p>
    <?php elseif ($error): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="name">Event Name:</label>
        <input type="text" name="name" required>

        <label for="category">Category:</label>
        <select name="category" required>
            <option value="Social">Social</option>
            <option value="Fundraising">Fundraising</option>
            <option value="Tech Talk">Tech Talk</option>
        </select>

        <label for="description">Description:</label>
        <textarea name="description" required></textarea>

        <label for="datetime">Date and Time:</label>
        <input type="datetime-local" name="datetime" required>

        <label for="location_name">Location Name:</label>
        <input type="text" name="location_name" required>

        <label for="contact_phone">Contact Phone:</label>
        <input type="text" name="contact_phone" required>

        <label for="contact_email">Contact Email:</label>
        <input type="email" name="contact_email" required>

        <label for="publicity">Publicity:</label>
        <select name="publicity" required>
            <option value="Public">Public</option>
            <option value="Private">Private</option>
            <option value="RSO">RSO</option>
        </select>

        <label for="rso_id">RSO (Optional):</label>
        <select name="rso_id">
            <option value="">None</option>
            <?php while ($row = $rsos->fetch_assoc()): ?>
                <option value="<?= $row['RSOID'] ?>"><?= htmlspecialchars($row['Name']) ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <button type="submit">Create Event</button>
    </form>
</div>
</body>
</html>
