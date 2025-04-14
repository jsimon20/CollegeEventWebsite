<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isSuperAdmin()) {
    header("Location: login.php");
    exit;
}

$query = "SELECT * FROM Events WHERE NeedsApproval = 1";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Approve Events</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="container">
    <h2>Pending Event Approvals</h2>

    <?php if ($result->num_rows === 0): ?>
        <p>No pending event approvals.</p>
    <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="event-card">
                <h3><?= htmlspecialchars($row['Name']) ?></h3>
                <p><?= htmlspecialchars($row['Description']) ?></p>
                <p><strong>Date:</strong> <?= date("F j, Y, g:i A", strtotime($row['EventTime'])) ?></p>
                <a href="event_details.php?event_id=<?= $row['EventID'] ?>" target="_blank"><button>View Details</button></a>
                <form method="POST" action="process_approval.php" style="display:inline;">
                    <input type="hidden" name="event_id" value="<?= $row['EventID'] ?>">
                    <button type="submit" name="action" value="approve">Approve</button>
                    <button type="submit" name="action" value="decline" style="background-color: #d9534f;">Decline</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <br><br>
    <a href="../dashboard.php"><button>Back to Dashboard</button></a>
</div>
</body>
</html>
