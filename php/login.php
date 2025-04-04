<?php
session_start();
require '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT UserID, Password, UserType, UniversityID FROM Users WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $hashed_password, $user_type, $university_id);

    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['university_id'] = $university_id;

        // Redirect based on user type
        if ($user_type === 'SuperAdmin') {
            header("Location: ../dashboard.php"); // Redirect Super Admin to the dashboard
        } elseif ($user_type === 'Admin') {
            header("Location: ../dashboard.php"); // Redirect Admin to the dashboard
        } elseif ($user_type === 'Student') {
            header("Location: ../index.php"); // Redirect Student to the main page
        } else {
            echo "Invalid user type.";
        }
        exit();
    } else {
        echo "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <style>
        .password-container {
            position: relative;
            width: 100%;
        }

        .password-container input[type="password"],
        .password-container input[type="text"] {
            width: 100%;
            padding-right: 40px; /* Space for the icon */
        }

        .password-container .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            width: 24px;
            height: 24px;
            background-size: cover;
        }

        .password-container .toggle-password.show {
            background-image: url('../assets/show.png'); /* Path to show.png */
        }

        .password-container .toggle-password.hide {
            background-image: url('../assets/hide.png'); /* Path to hide.png */
        }
    </style>
    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('show');
                toggleIcon.classList.add('hide');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('hide');
                toggleIcon.classList.add('show');
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST" action="login.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <span id="togglePassword" class="toggle-password show" onclick="togglePasswordVisibility()"></span>
            </div>
            <br>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
        <p><a href="reset_password.php">Forgot Password?</a></p>
    </div>
</body>
</html>