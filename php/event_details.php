<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$event_id = $_GET['event_id'];
$user_id = $_SESSION['user_id'];

// Fetch event details
$stmt = $conn->prepare("SELECT * FROM Events WHERE EventID = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

// Fetch comments and ratings
$comments_stmt = $conn->prepare("SELECT Comments.CommentID, Comments.CommentText, Comments.Rating, Users.Username, Comments.UserID, Comments.Type FROM Comments JOIN Users ON Comments.UserID = Users.UserID WHERE Comments.EventID = ?");
$comments_stmt->bind_param("i", $event_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

// Calculate average rating
$rating_stmt = $conn->prepare("SELECT AVG(Rating) as AverageRating FROM Comments WHERE EventID = ? AND Type = 'Review'");
$rating_stmt->bind_param("i", $event_id);
$rating_stmt->execute();
$rating_result = $rating_stmt->get_result();
$average_rating = $rating_result->fetch_assoc()['AverageRating'];
$average_rating = $average_rating !== null ? number_format($average_rating, 1) : 'No ratings yet';

// Check if the user is attending the event
$attending_stmt = $conn->prepare("SELECT * FROM Event_Attendees WHERE EventID = ? AND UserID = ?");
$attending_stmt->bind_param("ii", $event_id, $user_id);
$attending_stmt->execute();
$is_attending = $attending_stmt->get_result()->num_rows > 0;

// Check if the event has ended
$event_ended = strtotime($event['EventTime']) < time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Details</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h2><?php echo $event['Name']; ?></h2>
        <p><strong>Category:</strong> <?php echo $event['Category']; ?></p>
        <p><strong>Description:</strong> <?php echo $event['Description']; ?></p>
        <p><strong>Date and Time:</strong> 
            <?php 
            $event_time = strtotime($event['EventTime']);
            $end_time = strtotime($event['EndTime']);
            $formatted_event_time = date("g:i A", $event_time);
            $formatted_end_time = date("g:i A", $end_time);
            if (date("i", $event_time) == "00") {
                $formatted_event_time = date("g A", $event_time);
            }
            if (date("i", $end_time) == "00") {
                $formatted_end_time = date("g A", $end_time);
            }
            echo date("F j, Y, ", $event_time) . $formatted_event_time . " to " . date("F j, Y, ", $end_time) . $formatted_end_time;
            ?>
        </p>
        <p><strong>Location:</strong> 
            <?php 
            if (!empty($event['LocationName'])) {
                $location_name = $event['LocationName'];
                $location_url = "https://www.google.com/maps/search/?api=1&query=" . urlencode($location_name . ", UCF");
                echo "<a href=\"$location_url\" target=\"_blank\">$location_name</a>";
            } else {
                echo 'Virtual';
            }
            ?>
        </p>
        <?php if (!empty($event['ContactPhone'])): ?>
            <p><strong>Contact Phone:</strong> <?php echo $event['ContactPhone']; ?></p>
        <?php endif; ?>
        <p><strong>Contact Email:</strong> <?php echo $event['ContactEmail']; ?></p>
        <p><strong>Publicity:</strong> <?php echo $event['Publicity']; ?></p>
        <p><strong>Average Rating:</strong> <?php echo $average_rating; ?> / 5</p>
        <a href="view_event.php"><button>Back</button></a> <!-- Back button added -->

        <!-- Attend Event Button -->
        <?php if (!$is_attending && !$event_ended && isStudent()): ?>
            <form method="POST" action="attend_event.php">
                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                <button type="submit">Attend Event</button>
            </form>
        <?php endif; ?>

        <!-- Social Network Integration -->
        <h3>Share this Event</h3>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode("http://yourwebsite.com/event_details.php?event_id=$event_id"); ?>" target="_blank">Share on Facebook</a>
        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode("http://yourwebsite.com/event_details.php?event_id=$event_id"); ?>&text=<?php echo urlencode($event['Name']); ?>" target="_blank">Share on Twitter</a>

        <!-- Comments Section -->
        <h3>Comments</h3>
        <?php while ($comment = $comments_result->fetch_assoc()): ?>
            <div class="comment">
                <p><strong><?php echo $comment['Username']; ?>:</strong> <?php echo $comment['CommentText']; ?></p>
                <?php if ($comment['Type'] == 'Review'): ?>
                    <p><strong>Rating:</strong> <?php echo $comment['Rating']; ?> / 5</p>
                <?php endif; ?>
                <?php if ($comment['UserID'] == $user_id && !$event_ended): ?>
                    <form method="POST" action="edit_comment.php">
                        <input type="hidden" name="comment_id" value="<?php echo $comment['CommentID']; ?>">
                        <textarea name="comment_text" required><?php echo $comment['CommentText']; ?></textarea>
                        <?php if ($comment['Type'] == 'Review'): ?>
                            <input type="number" name="rating" min="1" max="5" value="<?php echo $comment['Rating']; ?>" required>
                        <?php endif; ?>
                        <button type="submit">Edit Comment</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

        <!-- Add Comment Section -->
        <?php if ($is_attending && !$event_ended): ?>
            <h3>Add a Comment</h3>
            <form method="POST" action="add_comment.php">
                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                <textarea name="comment_text" required></textarea>
                <input type="hidden" name="type" value="Comment">
                <button type="submit">Submit Comment</button>
            </form>
        <?php endif; ?>

        <!-- Add Review Section -->
        <?php if ($is_attending && $event_ended): ?>
            <h3>Add a Review</h3>
            <form method="POST" action="add_comment.php">
                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                <textarea name="comment_text" required></textarea>
                <input type="number" name="rating" min="1" max="5" required>
                <input type="hidden" name="type" value="Review">
                <button type="submit">Submit Review</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>