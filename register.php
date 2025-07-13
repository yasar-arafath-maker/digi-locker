<?php
include "includes/db_connect.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$password = isset($_POST["password"]) ? $_POST["password"] : "";
$confirm_password = isset($_POST["confirm_password"]) ? $_POST["confirm_password"] : "";


    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION["user_id"] = $stmt->insert_id;
                $_SESSION["name"] = $name;
                $_SESSION["role"] = "user";

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register - Digi Locker</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="container">
    <h2>📝 Register</h2>

    <?php if (!empty($error)) : ?>
        <div class="error-message">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label>👤 Name</label>
        <input type="text" name="name" required value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" />

        <label>📧 Email</label>
        <input type="email" name="email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" />

        <label>🔒 Password</label>
        <input type="password" name="password" required />

        <label>🔒 Confirm Password</label>
        <input type="password" name="confirm_password" required />

        <button type="submit">🚀 Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a> 🔑</p>
</div>
</body>
</html>
