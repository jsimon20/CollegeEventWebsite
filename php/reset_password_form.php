<?php
require '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['token'])) {
    $token = $_GET['token'];

    // Validate the token
    $stmt = $conn->prepare("SELECT UserID, ResetTokenExpiry FROM Users WHERE ResetToken = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $expiry);

    if ($stmt->fetch()) {
        if (strtotime($expiry) > time()) {
            // Token is valid, show the reset form
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Reset Password</title>
                <link rel="stylesheet" type="text/css" href="../css/styles.css">
                <script>
                    function goBack() {
                        window.history.back();
                    }
                </script>
            </head>
            <body>
                <div class="container">
                    <h2>Reset Password</h2>
                    <form method="POST" action="reset_password_form.php">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <button type="submit">Reset Password</button>
                    </form>
                    <button onclick="goBack()">Back</button> <!-- Back button added -->
                </div>
            </body>
            </html>
            <?php
        } else {
            echo "The reset token has expired.";
        }
    } else {
        echo "Invalid reset token.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token'])) {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    // Update the password in the database
    $stmt = $conn->prepare("UPDATE Users SET Password = ?, ResetToken = NULL, ResetTokenExpiry = NULL WHERE ResetToken = ?");
    $stmt->bind_param("ss", $new_password, $token);

    if ($stmt->execute()) {
        echo "Password reset successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>