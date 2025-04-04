<?php
require '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT UserID FROM Users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate a unique reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour

        // Save the token and expiry in the database
        $stmt->close();
        $stmt = $conn->prepare("UPDATE Users SET ResetToken = ?, ResetTokenExpiry = ? WHERE Email = ?");
        $stmt->bind_param("sss", $token, $expiry, $email);
        if ($stmt->execute()) {
            // Send the reset link to the user's email
            $reset_link = "http://localhost/CollegeEventWebsite/php/reset_password_form.php?token=$token";
            mail($email, "Password Reset Request", "Click the link to reset your password: $reset_link");
            echo "A password reset link has been sent to your email.";
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        // Email does not exist in the database
        echo "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Password Reset</title>
    <style>
        .back-button {
            background-color: #007BFF;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 1em;
            text-decoration: none;
        }

        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>Request Password Reset</h2>
    <form method="POST" action="reset_password.php">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Send Reset Link</button>
    </form>
    <br>
    <!-- Back button -->
    <a href="javascript:history.back();" class="back-button">Back</a>
</body>
</html>