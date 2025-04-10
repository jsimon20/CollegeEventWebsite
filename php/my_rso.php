<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();
redirectIfNotLoggedIn();

if (!isAdmin()) die("Access denied.");

$admin_id = $_SESSION['UserID'];
$rso_query = $conn->query("SELECT * FROM RSOs WHERE AdminID = $admin_id");
?>

<h2>My RSO</h2>

<?php if ($rso_query->num_rows == 0): ?>
    <p>You haven't created an RSO yet.</p>
<?php else: ?>
    <?php while ($rso = $rso_query->fetch_assoc()): ?>
        <div class="event-card">
            <h3><?= htmlspecialchars($rso['Name']) ?></h3>
            <p><?= htmlspecialchars($rso['Description']) ?></p>

            <h4>Members:</h4>
            <?php
            $rso_id = $rso['RSOID'];
            $members = $conn->query("SELECT Users.Username FROM Users 
                                     JOIN RSOMembers ON Users.UserID = RSOMembers.UserID 
                                     WHERE RSOMembers.RSOID = $rso_id");
            while ($m = $members->fetch_assoc()):
            ?>
                <p>- <?= htmlspecialchars($m['Username']) ?></p>
            <?php endwhile; ?>
        </div>
    <?php endwhile; ?>
<?php endif; ?>
