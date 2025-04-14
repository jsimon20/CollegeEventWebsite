<?php
session_start();
require '../includes/db_connect.php';
require '../includes/functions.php';

$event_id = $_GET['event_id'];
$is_logged_in = isLoggedIn();
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;
$is_admin = isAdmin();
$is_student = isStudent();

$stmt = $conn->prepare("SELECT * FROM Events WHERE EventID = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

$rso_id = $event['RSOID'] ?? null;

$comments_stmt = $conn->prepare("
    SELECT Comments.CommentID, Comments.CommentText, Comments.Rating, Comments.Type, Comments.UserID, Comments.CreatedAt, Users.Username
    FROM Comments
    JOIN Users ON Comments.UserID = Users.UserID
    WHERE Comments.EventID = ?
");
$comments_stmt->bind_param("i", $event_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

$rating_stmt = $conn->prepare("SELECT AVG(Rating) as AverageRating FROM Comments WHERE EventID = ? AND Type = 'Review'");
$rating_stmt->bind_param("i", $event_id);
$rating_stmt->execute();
$avg_rating = $rating_stmt->get_result()->fetch_assoc()['AverageRating'];
$avg_rating = $avg_rating !== null ? number_format($avg_rating, 1) : "No ratings yet";

$is_attending = false;
if ($is_student) {
    $check_stmt = $conn->prepare("SELECT * FROM Event_Attendees WHERE EventID = ? AND UserID = ?");
    $check_stmt->bind_param("ii", $event_id, $user_id);
    $check_stmt->execute();
    $is_attending = $check_stmt->get_result()->num_rows > 0;
}

$event_ended = strtotime($event['EventTime']) < time();

$has_left_review = false;
if ($is_student && $event_ended) {
    $review_check = $conn->prepare("SELECT * FROM Comments WHERE EventID = ? AND UserID = ? AND Type = 'Review'");
    $review_check->bind_param("ii", $event_id, $user_id);
    $review_check->execute();
    $has_left_review = $review_check->get_result()->num_rows > 0;
}

$admin_controls_this_event = false;
if ($is_admin && $rso_id) {
    $own_check = $conn->prepare("SELECT * FROM RSOs WHERE RSOID = ? AND AdminID = ?");
    $own_check->bind_param("ii", $rso_id, $user_id);
    $own_check->execute();
    $admin_controls_this_event = $own_check->get_result()->num_rows > 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Details</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .star-rating {
            margin: 10px 0;
        }
        .stars {
            font-size: 1.8rem;
            color: #FFD700;
            cursor: pointer;
            user-select: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><?= htmlspecialchars($event['Name']) ?></h2>
    <p><strong>Category:</strong> <?= $event['Category'] ?></p>
    <p><strong>Description:</strong> <?= $event['Description'] ?></p>
    <p><strong>Date & Time:</strong>
        <?php
        $start = strtotime($event['EventTime']);
        echo date("F j, Y, g:i A", $start);

        if (!empty($event['EndTime'])) {
            $end = strtotime($event['EndTime']);
            echo " to " . date("F j, Y, g:i A", $end);
        }
        ?>
    </p>
    <p><strong>Location:</strong>
        <?php
        $location = $event['LocationName'];
        echo $location ? "<a href='https://www.google.com/maps/search/" . urlencode($location) . "' target='_blank'>$location</a>" : "Virtual";
        ?>
    </p>
    <p><strong>Contact:</strong> <?= $event['ContactEmail'] ?> <?= $event['ContactPhone'] ? "| " . $event['ContactPhone'] : "" ?></p>
    <p><strong>Publicity:</strong> <?= $event['Publicity'] ?></p>
    <p><strong>Average Rating:</strong>
        <?php if ($avg_rating !== "No ratings yet"): ?>
            <?php
            $avg = floatval($avg_rating);
            $filled = floor($avg);
            $half = ($avg - $filled) >= 0.5;
            $empty = 5 - $filled - ($half ? 1 : 0);
            echo str_repeat("★", $filled);
            echo $half ? "⯪" : "";
            echo str_repeat("☆", $empty);
            echo " ($avg_rating / 5)";
            ?>
        <?php else: ?>
            <?= $avg_rating ?>
        <?php endif; ?>
    </p>
    <a href="../index.php?view=day"><button>Back</button></a>

    <h3>Share This Event</h3>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode("http://yourwebsite.com/event_details.php?event_id=$event_id") ?>" target="_blank">Facebook</a> |
    <a href="https://twitter.com/intent/tweet?url=<?= urlencode("http://yourwebsite.com/event_details.php?event_id=$event_id") ?>&text=<?= urlencode($event['Name']) ?>" target="_blank">Twitter</a>

    <h3>Comments</h3>
    <?php while ($comment = $comments_result->fetch_assoc()): ?>
        <div class="comment" id="comment-<?= $comment['CommentID'] ?>">
            <p><strong><?= htmlspecialchars($comment['Username']) ?></strong> (<?= date("F j, Y g:i A", strtotime($comment['CreatedAt'])) ?>)</p>
            <p id="text-<?= $comment['CommentID'] ?>"><?= htmlspecialchars($comment['CommentText']) ?></p>
            <?php if ($comment['Type'] === 'Review'): ?>
                <p><strong>Rating:</strong>
                    <?= str_repeat("⭐", intval($comment['Rating'])) ?> (<?= $comment['Rating'] ?> / 5)
                </p>
            <?php endif; ?>

            <?php if ($is_logged_in && ($comment['UserID'] == $user_id || $admin_controls_this_event)): ?>
                <button onclick="toggleEditForm(<?= $comment['CommentID'] ?>)">Edit</button>
                <form method="POST" action="delete_comment.php" onsubmit="return confirm('Are you sure?');" style="display:inline;">
                    <input type="hidden" name="comment_id" value="<?= $comment['CommentID'] ?>">
                    <button type="submit" style="background-color: #d9534f;">Delete</button>
                </form>

                <form id="form-<?= $comment['CommentID'] ?>" method="POST" action="edit_comment.php" style="display:none; margin-top: 10px;">
                    <input type="hidden" name="comment_id" value="<?= $comment['CommentID'] ?>">
                    <textarea name="comment_text" required><?= htmlspecialchars($comment['CommentText']) ?></textarea>
                    <?php if ($comment['Type'] == 'Review'): ?>
                        <input type="number" name="rating" value="<?= $comment['Rating'] ?>" min="1" max="5" step="0.5" required>
                    <?php endif; ?>
                    <button type="submit">Save</button>
                    <button type="button" onclick="toggleEditForm(<?= $comment['CommentID'] ?>)">Cancel</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

    <?php if ($is_logged_in && $is_student): ?>
        <h3>Add a Comment</h3>
        <form method="POST" action="add_comment.php">
            <input type="hidden" name="event_id" value="<?= $event_id ?>">
            <textarea name="comment_text" required></textarea>
            <input type="hidden" name="type" value="Comment">
            <button type="submit">Submit Comment</button>
        </form>

        <?php if ($event_ended): ?>
            <?php if (!$has_left_review): ?>
                <h3>Add a Review</h3>
                <form method="POST" action="add_comment.php">
                    <input type="hidden" name="event_id" value="<?= $event_id ?>">
                    <textarea name="comment_text" required></textarea>

                    <div class="star-rating">
                        <input type="hidden" name="rating" id="rating-value" required>
                        <div class="stars" id="star-container">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star" data-value="<?= $i ?>">&#9734;</span>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <input type="hidden" name="type" value="Review">
                    <button type="submit">Submit Review</button>
                </form>
            <?php else: ?>
                <p><em>You’ve already submitted a review for this event.</em></p>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
const stars = document.querySelectorAll('.star');
const ratingInput = document.getElementById('rating-value');

stars.forEach((star, index) => {
    star.addEventListener('mousemove', (e) => {
        const percent = e.offsetX / star.offsetWidth;
        highlightStars(index + (percent >= 0.5 ? 1 : 0.5));
    });

    star.addEventListener('click', (e) => {
        const percent = e.offsetX / star.offsetWidth;
        const val = index + (percent >= 0.5 ? 1 : 0.5);
        ratingInput.value = val;
        highlightStars(val);
    });

    star.addEventListener('mouseleave', () => {
        highlightStars(parseFloat(ratingInput.value || 0));
    });
});

function highlightStars(value) {
    stars.forEach((star, index) => {
        if (value >= index + 1) {
            star.textContent = '★';
        } else if (value >= index + 0.5) {
            star.textContent = '⯪';
        } else {
            star.textContent = '☆';
        }
    });
}

function toggleEditForm(id) {
    const form = document.getElementById('form-' + id);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
</body>
</html>
