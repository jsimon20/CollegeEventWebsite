<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isSuperAdmin()) {
    header("Location: ../unauthorized.php");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_university'])) {
        $name = $_POST['name'];
        $raw_domain = strtolower(trim($_POST['domain']));
        $domain = $raw_domain . '.edu';
        $location = $_POST['location'];
        $description = $_POST['description'];

        $stmt = $conn->prepare("INSERT INTO Universities (Name, Domain, Location, Description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $domain, $location, $description);
        if ($stmt->execute()) {
            echo "University added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
    } elseif (isset($_POST['edit_university'])) {
        $university_id = $_POST['university_id'];
        $name = $_POST['name'];
        $raw_domain = strtolower(trim($_POST['domain']));
        $domain = $raw_domain . '.edu';
        $location = $_POST['location'];
        $description = $_POST['description'];

        $stmt = $conn->prepare("UPDATE Universities SET Name = ?, Domain = ?, Location = ?, Description = ? WHERE UniversityID = ?");
        $stmt->bind_param("ssssi", $name, $domain, $location, $description, $university_id);
        if ($stmt->execute()) {
            echo "University updated successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
    } elseif (isset($_POST['delete_university'])) {
        $university_id = $_POST['university_id'];

        $stmt = $conn->prepare("DELETE FROM Universities WHERE UniversityID = ?");
        $stmt->bind_param("i", $university_id);
        if ($stmt->execute()) {
            echo "University deleted successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}

// Fetch all universities
$query = "SELECT * FROM Universities";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Universities</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Manage Universities</h2>
        <h3>Add University</h3>
        <form method="POST" action="manage_universities.php">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br>

            <label for="domain">Email Domain (without .edu):</label>
            <input type="text" id="domain" name="domain" placeholder="e.g. ucf, knights, ufl" required><br>

            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required><br>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea><br>

            <button type="submit" name="add_university">Add University</button>
        </form>

        <h3>Existing Universities</h3>
        <?php while ($university = $result->fetch_assoc()): ?>
            <div class="university">
                <form method="POST" action="manage_universities.php">
                    <input type="hidden" name="university_id" value="<?= $university['UniversityID']; ?>">

                    <label for="name">Name:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($university['Name']); ?>" required><br>

                    <label for="domain">Domain (without .edu):</label>
                    <input type="text" name="domain" value="<?= htmlspecialchars(str_replace('.edu', '', $university['Domain'])); ?>" required><br>

                    <label for="location">Location:</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($university['Location']); ?>" required><br>

                    <label for="description">Description:</label>
                    <textarea name="description" required><?= htmlspecialchars($university['Description']); ?></textarea><br>

                    <button type="submit" name="edit_university">Edit</button>
                    <button type="submit" name="delete_university">Delete</button>
                </form>
            </div>
        <?php endwhile; ?>

        <br>
        <a href="../dashboard.php"><button>Back to Dashboard</button></a>
    </div>
</body>
</html>
