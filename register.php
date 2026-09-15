<?php
require_once "config.php";
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($username == "" || $email == "" || $password == "") {
        $error = "Please fill in all fields.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Username or email is already taken.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $username, $email, $hashed_password);

            if ($insert->execute()) {
                $success = "Account created! You can now log in.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Smart Study Planner</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Create an Account</h2>

        <?php if ($error != "") { ?>
            <p class="error-msg"><?php echo $error; ?></p>
        <?php } ?>

        <?php if ($success != "") { ?>
            <p class="success-msg"><?php echo $success; ?> <a href="login.php">Login here</a></p>
        <?php } ?>

        <form method="POST" action="register.php">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>

            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
