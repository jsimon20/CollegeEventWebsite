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
    $description = $_POST['description'];
    $admin_id = $_SESSION['user_id'];
    $university_id = $_SESSION['university_id'];
    $member_emails = $_POST['member_emails']; // Comma-separated list of member emails

    // Validate member emails
    $emails = explode(',', $member_emails);
    $valid_emails = array_filter($emails, function($email) use ($university_id) {
        return preg_match('/@.+\.edu$/', $email) && strpos($email, '@' . $university_id . '.edu') !== false;
    });

    if (count($valid_emails) >= 4) {
        $stmt = $conn->prepare("INSERT INTO RSOs (Name, Description, AdminID, UniversityID) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $name, $description, $admin_id, $university_id);

        if ($stmt->execute()) {
            $rso_id = $stmt->insert_id;
            foreach ($valid_emails as $email) {
                $stmt = $conn->prepare("INSERT INTO RSO_Members (RSOID, Email) VALUES (?, ?)");
                $stmt->bind_param("is", $rso_id, $email);
                $stmt->execute();
            }
            echo "RSO created successfully!";
        } else {
            echo "Error creating RSO: " . $stmt->error;
        }
    } else {
        echo "Error: At least 4 other students with the same email domain are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create RSO</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Create RSO</h1>
    <form method="post" action="create_rso.php">
        <label for="name">RSO Name:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>
        <br>
        <label for="member_emails">Member Emails (comma-separated):</label>
        <input type="text" id="member_emails" name="member_emails" required>
        <br>
        <button type="submit">Create RSO</button>
    </form>
</body>
</html>