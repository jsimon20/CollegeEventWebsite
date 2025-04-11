<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

$event_id = $_GET['event_id'] ?? null;
if (!$event_id) {
    die("Event ID not provided.");
}

$is_logged_in = isLoggedIn();
$user_id = $is_logged_in ? ($_SESSION['user_id'] ?? $_SESSION['UserID']) : null;

$stmt = $conn->prepare("SELECT * FROM Events WHERE EventID = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

$comments_stmt = $conn->prepare("SELECT Comments.CommentID, Comments.CommentText, Comments.Rating, Users.Username, Comments.UserID, Comments.Type FROM Comments JOIN Users ON Comments.UserID = Users.UserID WHERE Comments.EventID = ?");
$comments_stmt->bind_param("i", $event_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

$rating_stmt = $conn->prepare("SELECT AVG(Rating) as AverageRating FROM Comments WHERE EventID = ? AND Type = 'Review'");
$rating_stmt->bind_param("i", $event_id);
$rating_stmt->execute();
$rating_result = $rating_stmt->get_result();
$average_rating = $rating_result->fetch_assoc()['AverageRating'];
$average_rating = $average_rating !== null ? number_format($average_rating, 1) : 'No ratings yet';

$is_attending = false;
if ($is_logged_in) {
    $attending_stmt = $conn->prepare("SELECT * FROM Event_Attendees WHERE EventID = ? AND UserID = ?");
    $attending_stmt->bind_param("ii", $event_id, $user_id);
    $attending_stmt->execute();
    $is_attending = $attending_stmt->get_result()->num_rows > 0;
}

$event_time = !empty($event['EventTime']) ? strtotime($event['EventTime']) : null;
$end_time = !empty($event['EndTime']) ? strtotime($event['EndTime']) : null;
$event_ended = $event_time !== null && $event_time < time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Details</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="container">
    <h2><?= htmlspecialchars($event['Name']) ?></h2>
    <p><strong>Category:</strong> <?= htmlspecialchars($event['Category']) ?></p>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($event['Description'])) ?></p>

    <p><strong>Date and Time:</strong>
        <?php
        if ($event_time) {
            echo date("F j, Y, g:i A", $event_time);
            if ($end_time) {
                echo " to " . date("F j, Y, g:i A", $end_time);
            }
        } else {
            echo "N/A";
        }
        ?>
    </p>

    <p><strong>Location:</strong>
        <?php
        if (!empty($event['LocationName'])) {
            $loc = htmlspecialchars($event['LocationName']);
            $url = "https://www.google.com/maps/search/?api=1&query=" . urlencode($loc . ", UCF");
            echo "<a href=\"$url\" target=\"_blank\">$loc</a>";
        } else {
            echo 'Virtual';
        }
        ?>
    </p>

    <?php if (!empty($event['ContactPhone'])): ?>
        <p><strong>Contact Phone:</strong> <?= htmlspecialchars($event['ContactPhone']) ?></p>
    <?php endif; ?>

    <p><strong>Contact Email:</strong> <?= htmlspecialchars($event['ContactEmail']) ?></p>

    <p><strong>Publicity:</strong>
        <?php
        $pub = $event['Publicity'];
        if ($pub === "Public" || $pub === "Private" || $pub === "RSO") {
            echo $pub;
        } elseif ($pub == 0 && $event['NeedsApproval'] ?? false) {
            echo "Pending Approval";
        } else {
            echo htmlspecialchars($pub);
        }
        ?>
    </p>

    <p><strong>Average Rating:</strong> <?= $average_rating ?> / 5</p>

    <a href="../index.php?view=day"><button>← Back</button></a>

    <!-- Attend Button -->
    <?php if ($is_logged_in && !$is_attending && !$event_ended && isStudent()): ?>
        <form method="POST" action="attend_event.php">
            <input type="hidden" name="event_id" value="<?= $event_id ?>">
            <button type="submit">Attend Event</button>
        </form>
    <?php endif; ?>

    <!-- Social Share -->
    <h3>Share this Event</h3>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode("http://yourwebsite.com/event_details.php?event_id=$event_id") ?>" target="_blank">Share on Facebook</a>
    <a href="https://twitter.com/intent/tweet?url=<?= urlencode("http://yourwebsite.com/event_details.php?event_id=$event_id") ?>&text=<?= urlencode($event['Name']) ?>" target="_blank">Share on Twitter</a>

    <!-- Comments -->
    <h3>Comments</h3>
    <?php while ($comment = $comments_result->fetch_assoc()): ?>
        <div class="comment">
            <p><strong><?= htmlspecialchars($comment['Username']) ?>:</strong> <?= htmlspecialchars($comment['CommentText']) ?></p>
            <?php if ($comment['Type'] === 'Review'): ?>
                <p><strong>Rating:</strong> <?= $comment['Rating'] ?> / 5</p>
            <?php endif; ?>
            <?php if ($is_logged_in && $comment['UserID'] == $user_id && !$event_ended): ?>
                <form method="POST" action="edit_comment.php">
                    <input type="hidden" name="comment_id" value="<?= $comment['CommentID'] ?>">
                    <textarea name="comment_text" required><?= htmlspecialchars($comment['CommentText']) ?></textarea>
                    <?php if ($comment['Type'] === 'Review'): ?>
                        <input type="number" name="rating" min="1" max="5" value="<?= $comment['Rating'] ?>" required>
                    <?php endif; ?>
                    <button type="submit">Edit Comment</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

    <!-- Add Comment -->
    <?php if ($is_logged_in && $is_attending && !$event_ended): ?>
        <h3>Add a Comment</h3>
        <form method="POST" action="add_comment.php">
            <input type="hidden" name="event_id" value="<?= $event_id ?>">
            <textarea name="comment_text" required></textarea>
            <input type="hidden" name="type" value="Comment">
            <button type="submit">Submit Comment</button>
        </form>
    <?php endif; ?>

    <!-- Add Review -->
    <?php if ($is_logged_in && $is_attending && $event_ended): ?>
        <h3>Add a Review</h3>
        <form method="POST" action="add_comment.php">
            <input type="hidden" name="event_id" value="<?= $event_id ?>">
            <textarea name="comment_text" required></textarea>
            <input type="number" name="rating" min="1" max="5" required>
            <input type="hidden" name="type" value="Review">
            <button type="submit">Submit Review</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
