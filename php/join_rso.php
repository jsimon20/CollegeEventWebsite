<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();
redirectIfNotLoggedIn();

if (!isStudent()) {
    header("Location: unauthorized.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$university_id = $_SESSION['university_id'];
$message = "";

// Handle join request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['rso_id'])) {
    $rso_id = intval($_POST['rso_id']);

    // Make sure the user isn’t already a member
    $check_stmt = $conn->prepare("SELECT * FROM RSO_Members WHERE RSOID = ? AND UserID = ?");
    $check_stmt->bind_param("ii", $rso_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        $insert_stmt = $conn->prepare("INSERT INTO RSO_Members (RSOID, UserID) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $rso_id, $user_id);
        $insert_stmt->execute();
        $message = "You joined the RSO successfully!";
    } else {
        $message = "You're already a member of this RSO.";
    }
}

// Get RSOs not joined yet
$stmt = $conn->prepare("
    SELECT RSOs.RSOID, RSOs.Name, RSOs.Description
    FROM RSOs
    WHERE RSOs.RSOID NOT IN (
        SELECT RSOID FROM RSO_Members WHERE UserID = ?
    )
    AND RSOs.AdminID IN (
        SELECT UserID FROM Users WHERE UniversityID = ?
    )
");
$stmt->bind_param("ii", $user_id, $university_id);
$stmt->execute();
$rsos = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Join RSOs</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Join a Registered Student Organization</h2>

        <?php if ($message): ?>
            <p style="color: green;"><?= $message ?></p>
        <?php endif; ?>

        <?php if ($rsos->num_rows > 0): ?>
            <?php while ($rso = $rsos->fetch_assoc()): ?>
                <div class="event">
                    <h3><?= htmlspecialchars($rso['Name']) ?></h3>
                    <p><?= htmlspecialchars($rso['Description']) ?></p>
                    <form method="POST">
                        <input type="hidden" name="rso_id" value="<?= $rso['RSOID'] ?>">
                        <button type="submit">Join</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No available RSOs to join.</p>
        <?php endif; ?>
        <br>
        <a href="../index.php?view=day"><button>Back to Homepage</button></a>
    </div>
</body>
</html>
