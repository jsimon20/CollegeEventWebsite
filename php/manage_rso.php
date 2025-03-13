<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$university_id = $_SESSION['university_id'];

// Fetch RSOs managed by the admin
$query = "SELECT * FROM RSOs WHERE AdminID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$rsos_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage RSOs</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Manage RSOs</h2>
        <a href="create_rso.php"><button>Create New RSO</button></a>
        <?php while ($rso = $rsos_result->fetch_assoc()): ?>
            <div class="rso">
                <h3><?php echo $rso['Name']; ?></h3>
                <p><?php echo $rso['Description']; ?></p>
                <a href="view_rso.php?rso_id=<?php echo $rso['RSOID']; ?>"><button>View Members</button></a>
                <a href="approve_join_requests.php?rso_id=<?php echo $rso['RSOID']; ?>"><button>Approve Join Requests</button></a>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>