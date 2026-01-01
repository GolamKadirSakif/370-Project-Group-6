<?php
session_start();
require_once "database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name     = trim($_POST["name"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];
    $role     = $_POST["role"];

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert into User table
        $stmt = $conn->prepare(
            "INSERT INTO User (Name, Email, Password) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $name, $email, $hashedPassword);

        if ($stmt->execute()) {

            $user_id = $stmt->insert_id;

            // Insert role-specific data
            if ($role === "developer") {
                $stmtRole = $conn->prepare(
                    "INSERT INTO Developer (User_ID) VALUES (?)"
                );
            } else {
                $stmtRole = $conn->prepare(
                    "INSERT INTO Investor (User_ID) VALUES (?)"
                );
            }

            $stmtRole->bind_param("i", $user_id);
            $stmtRole->execute();

            $success = "Account created successfully. You can now login.";
        } else {
            $error = "Email already exists.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TIN | Sign Up</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="sign_up.css">
</head>
<body>

  <div class="signup-container">
    <div class="logo">
      <h1>TIN</h1>
      <span>Tech Investment Platform</span>
    </div>

    <h2>Create your account</h2>

    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
      <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST">

      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" placeholder="Your name" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="you@example.com" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>

      <div class="form-group">
        <label>Account Type</label>
        <select name="role" required>
          <option value="">Select role</option>
          <option value="investor">Investor</option>
          <option value="developer">Developer</option>
        </select>
      </div>

      <button type="submit" class="signup-btn">Create Account</button>
    </form>

    <div class="extra-links">
      <a href="login.php">Already have an account? Login</a>
    </div>

    <div class="footer-text">
      © 2025 TIN · All rights reserved
    </div>
  </div>

</body>
</html>
