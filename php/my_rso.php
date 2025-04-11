<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();
redirectIfNotLoggedIn();

if (!isAdmin()) {
    die("Access denied.");
}

$admin_id = $_SESSION["UserID"] ?? $_SESSION["user_id"] ?? null;
if (!$admin_id) {
    die("Session user ID not found.");
}

// Get RSOs the admin is a member of
$rso_stmt = $conn->prepare("
    SELECT RSOs.RSOID, RSOs.Name, RSOs.Description 
    FROM RSOs 
    JOIN RSO_Members ON RSOs.RSOID = RSO_Members.RSOID 
    WHERE RSO_Members.UserID = ?
");
$rso_stmt->bind_param("i", $admin_id);
$rso_stmt->execute();
$rsos_result = $rso_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My RSOs</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="container">
    <h2>Manage Your RSOs</h2>
    <a href="../index.php?view=day"><button type="button">← Back to Events</button></a>

    <?php if ($rsos_result->num_rows > 0): ?>
        <?php while ($rso = $rsos_result->fetch_assoc()): ?>
            <div class="rso">
                <h3>
                    <a href="edit_rso.php?rso_id=<?= $rso['RSOID'] ?>">
                        <?= htmlspecialchars($rso['Name']) ?>
                    </a>
                </h3>
                <p><?= nl2br(htmlspecialchars($rso['Description'])) ?></p>

                <h4>Events for this RSO:</h4>
                <?php
                $event_stmt = $conn->prepare("SELECT EventID, Name, EventTime FROM Events WHERE RSOID = ? ORDER BY EventTime ASC");
                $event_stmt->bind_param("i", $rso['RSOID']);
                $event_stmt->execute();
                $events_result = $event_stmt->get_result();

                if ($events_result->num_rows > 0):
                    while ($event = $events_result->fetch_assoc()):
                        $event_time = date("F j, Y g:i A", strtotime($event['EventTime']));
                        ?>
                        <div class="event-card">
                            <strong><?= htmlspecialchars($event['Name']) ?></strong><br>
                            <small><?= $event_time ?></small><br>
                            <a href="event_details.php?event_id=<?= $event['EventID'] ?>"><button>View</button></a>
                        </div>
                    <?php endwhile;
                else: ?>
                    <p class="no-events">No events yet.</p>
                <?php endif;
                $event_stmt->close();
                ?>
            </div>
            <hr>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You are not currently a member of any RSOs.</p>
    <?php endif; ?>
</div>
</body>
</html>
