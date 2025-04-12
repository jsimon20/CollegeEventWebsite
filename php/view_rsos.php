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
$message = "";

// Handle leave RSO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_rso_id'])) {
    $leave_rso_id = intval($_POST['leave_rso_id']);

    $delete_stmt = $conn->prepare("DELETE FROM RSO_Members WHERE UserID = ? AND RSOID = ?");
    $delete_stmt->bind_param("ii", $user_id, $leave_rso_id);
    $delete_stmt->execute();

    $message = "You have left the RSO.";
}

// Get RSOs the student is in
$rsos_stmt = $conn->prepare("
    SELECT RSOs.RSOID, RSOs.Name, RSOs.Description
    FROM RSOs
    JOIN RSO_Members M ON RSOs.RSOID = M.RSOID
    WHERE M.UserID = ?
");
$rsos_stmt->bind_param("i", $user_id);
$rsos_stmt->execute();
$rsos_result = $rsos_stmt->get_result();

$rsos = [];
while ($row = $rsos_result->fetch_assoc()) {
    $rsos[$row['RSOID']] = $row;
}

// Get events for these RSOs
$events = [];
if (!empty($rsos)) {
    $rso_ids = implode(',', array_keys($rsos));
    $events_query = "SELECT * FROM Events WHERE RSOID IN ($rso_ids) ORDER BY EventTime ASC";
    $event_result = $conn->query($events_query);
    while ($event = $event_result->fetch_assoc()) {
        $events[$event['RSOID']][] = $event;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My RSOs</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>My RSOs & Their Events</h2>

        <?php if ($message): ?>
            <p style="color: green;"><?= $message ?></p>
        <?php endif; ?>

        <?php if (empty($rsos)): ?>
            <p>You have not joined any RSOs yet.</p>
        <?php else: ?>
            <?php foreach ($rsos as $rso_id => $rso): ?>
                <div class="weekly-day-block">
                    <h3 class="weekly-date"><?= htmlspecialchars($rso['Name']) ?></h3>
                    <p><?= htmlspecialchars($rso['Description']) ?></p>
                    <form method="POST" style="margin-bottom: 15px;">
                        <input type="hidden" name="leave_rso_id" value="<?= $rso_id ?>">
                        <button type="submit" style="background-color: #d9534f;">Leave RSO</button>
                    </form>

                    <?php if (!empty($events[$rso_id])): ?>
                        <?php foreach ($events[$rso_id] as $event): ?>
                            <div class="event-card">
                                <h4><?= htmlspecialchars($event['Name']) ?></h4>
                                <p><?= htmlspecialchars($event['Description']) ?></p>
                                <p><strong>Date:</strong> <?= date("F j, Y", strtotime($event['EventTime'])) ?></p>
                                <p><strong>Time:</strong> <?= date("g:i A", strtotime($event['EventTime'])) ?></p>
                                <p><strong>Location:</strong> <?= $event['LocationName'] ?: 'Virtual' ?></p>
                                <a href="event_details.php?event_id=<?= $event['EventID'] ?>"><button>View Details</button></a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-events">No events for this RSO.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <br>
        <a href="../index.php?view=day"><button>Back to Homepage</button></a>
    </div>
</body>
</html>
