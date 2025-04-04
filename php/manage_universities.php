<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isSuperAdmin()) {
    header("Location: ../unauthorized.php");
    exit;
}

// Fetch all universities
$query = "SELECT * FROM Universities";
$result = $conn->query($query);

// Handle form submissions for adding, editing, or deleting universities
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_university'])) {
        $name = $_POST['name'];
        $location = $_POST['location'];
        $description = $_POST['description'];
        $student_count = $_POST['student_count'];

        $stmt = $conn->prepare("INSERT INTO Universities (Name, Location, Description, StudentCount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $location, $description, $student_count);
        if ($stmt->execute()) {
            echo "University added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
    } elseif (isset($_POST['edit_university'])) {
        $university_id = $_POST['university_id'];
        $name = $_POST['name'];
        $location = $_POST['location'];
        $description = $_POST['description'];
        $student_count = $_POST['student_count'];

        $stmt = $conn->prepare("UPDATE Universities SET Name = ?, Location = ?, Description = ?, StudentCount = ? WHERE UniversityID = ?");
        $stmt->bind_param("sssii", $name, $location, $description, $student_count, $university_id);
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
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required><br>
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea><br>
            <label for="student_count">Student Count:</label>
            <input type="number" id="student_count" name="student_count" required><br>
            <button type="submit" name="add_university">Add University</button>
        </form>

        <h3>Existing Universities</h3>
        <?php while ($university = $result->fetch_assoc()): ?>
            <div class="university">
                <form method="POST" action="manage_universities.php">
                    <input type="hidden" name="university_id" value="<?php echo $university['UniversityID']; ?>">
                    <label for="name">Name:</label>
                    <input type="text" name="name" value="<?php echo $university['Name']; ?>" required><br>
                    <label for="location">Location:</label>
                    <input type="text" name="location" value="<?php echo $university['Location']; ?>" required><br>
                    <label for="description">Description:</label>
                    <textarea name="description" required><?php echo $university['Description']; ?></textarea><br>
                    <label for="student_count">Student Count:</label>
                    <input type="number" name="student_count" value="<?php echo $university['StudentCount']; ?>" required><br>
                    <button type="submit" name="edit_university">Edit</button>
                    <button type="submit" name="delete_university">Delete</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>