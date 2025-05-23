<?php
require '../includes/db_connect.php';
session_start();

$universities = [];
$result = $conn->query("SELECT UniversityID, Name FROM Universities");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $universities[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];
    $user_type = $_POST['user_type'];
    $university_id = $_POST['university_id'];

    // Restrict student registration to .edu emails
    if ($user_type === 'Student' && !preg_match('/@.+\.edu$/', $email)) {
        echo "Error: Only .edu emails are allowed for student registration.";
        exit;
    }

    // Check if the UniversityID exists in the Universities table
    $stmt = $conn->prepare("SELECT UniversityID FROM Universities WHERE UniversityID = ?");
    $stmt->bind_param("i", $university_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO Users (Username, Password, Email, UserType, UniversityID) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $username, $password, $email, $user_type, $university_id);

        if ($stmt->execute()) {
            // Registration successful, log the user in
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['user_type'] = $user_type;
            $_SESSION['university_id'] = $university_id;
            $_SESSION['Username'] = $username;
            $_SESSION['UserType'] = $user_type;
            $_SESSION['UniversityID'] = $university_id;

            header("Location: ../index.php?view=day");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Error: The specified university is not supported.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form method="POST" action="register.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="user_type">Role:</label>
            <select id="user_type" name="user_type" required>
                <option value="Student">Student</option>
                <option value="Admin">Admin</option>
            </select><br>

            <label for="university_id">University:</label>
            <select id="university_id" name="university_id" required>
                <option value="">Select a university</option>
                <?php foreach ($universities as $university): ?>
                    <option value="<?= $university['UniversityID']; ?>"><?= htmlspecialchars($university['Name']); ?></option>
                <?php endforeach; ?>
            </select><br>

            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
