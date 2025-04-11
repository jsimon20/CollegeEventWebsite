<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();
redirectIfNotLoggedIn();

if (!isAdmin()) {
    die("Access denied.");
}

$success = false;
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $creator_id = $_SESSION['UserID'] ?? $_SESSION['user_id'] ?? null;
    $university_id = $_SESSION['UniversityID'] ?? $_SESSION['university_id'] ?? null;

    $member_emails = array_filter($_POST['emails'] ?? [], fn($e) => trim($e) !== '');
    $member_ids = [];

    if (!$creator_id || !$university_id) {
        $error = "Missing session info.";
    } elseif (count($member_emails) < 4) {
        $error = "You must enter at least 4 student emails.";
    } else {
        // Get the full domain (e.g. 'ucf.edu') from the creator's university
        $domain_query = $conn->prepare("SELECT Domain FROM Universities WHERE UniversityID = ?");
        $domain_query->bind_param("i", $university_id);
        $domain_query->execute();
        $domain_query->bind_result($creator_domain);
        $domain_query->fetch();
        $domain_query->close();

        if (!$creator_domain) {
            $error = "Unable to fetch your university domain.";
        } else {
            foreach ($member_emails as $email) {
                $email = trim($email);
                $stmt = $conn->prepare("SELECT UserID FROM Users WHERE Email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->bind_result($uid);
                if ($stmt->fetch() && str_ends_with($email, "@$creator_domain")) {
                    $member_ids[] = $uid;
                }
                $stmt->close();
            }

            if (count($member_ids) < 4) {
                $error = "All listed members must be registered users with '@$creator_domain' email addresses.";
            } else {
                // Create RSO
                $stmt = $conn->prepare("INSERT INTO RSOs (Name, Description, AdminID) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $name, $description, $creator_id);
                if ($stmt->execute()) {
                    $rso_id = $stmt->insert_id;

                    $stmt_add = $conn->prepare("INSERT INTO RSO_Members (RSOID, UserID) VALUES (?, ?)");

                    // Add creator
                    $stmt_add->bind_param("ii", $rso_id, $creator_id);
                    $stmt_add->execute();

                    // Add other members
                    foreach ($member_ids as $mid) {
                        $stmt_add->bind_param("ii", $rso_id, $mid);
                        $stmt_add->execute();
                    }

                    $success = true;
                } else {
                    $error = "Failed to create RSO.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create RSO</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="container">
    <h2>Create RSO</h2>

    <a href="../index.php?view=day"><button type="button" style="margin-bottom: 20px;">← Back to Events</button></a>

    <?php if ($success): ?>
        <p style="color: green;">RSO created successfully!</p>
    <?php elseif ($error): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="name">RSO Name:</label>
        <input type="text" name="name" required>

        <label for="description">Description:</label>
        <textarea name="description" required></textarea>

        <h4>Enter at least 4 student emails from your university domain:</h4>

        <div id="email-container">
            <?php for ($i = 0; $i < 4; $i++): ?>
                <input type="email" name="emails[]" placeholder="Student Email <?= $i + 1 ?>" required><br>
            <?php endfor; ?>
        </div>

        <button type="button" onclick="addEmailField()">➕ Add More</button><br><br>
        <button type="submit">Create RSO</button>
    </form>
</div>

<script>
function addEmailField() {
    const container = document.getElementById('email-container');
    const count = container.querySelectorAll('input').length + 1;

    const input = document.createElement('input');
    input.type = 'email';
    input.name = 'emails[]';
    input.placeholder = 'Student Email ' + count;
    input.required = false;

    container.appendChild(input);
    container.appendChild(document.createElement('br'));
}
</script>
</body>
</html>
