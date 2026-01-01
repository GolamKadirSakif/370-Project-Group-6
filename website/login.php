<?php
  session_start();
  require_once "database.php";
  //includes the database connection file

  $error = "";
  



  if ($_SERVER["REQUEST_METHOD"] === "POST") {

      $email = trim($_POST["email"]);
      $password = $_POST["password"];

      // Get user info
      $stmt = $conn->prepare("
          SELECT User_ID, Password, Admin_ID
          FROM User
          WHERE Email = ?
          ");
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows === 1) {
          $user = $result->fetch_assoc();

          // Verify password (hashed)
          if (password_verify($password, $user['Password'])) {

              $user_id = $user['User_ID'];

              // Store base session data
              $_SESSION['user_id'] = $user_id;
              $_SESSION['email']   = $email;

              /* -------- Determine Role -------- */

              // Check Developer
              $stmtDev = $conn->prepare(
                  "SELECT User_ID FROM Developer WHERE User_ID = ?"
              );
              $stmtDev->bind_param("i", $user_id);
              $stmtDev->execute();
              $isDeveloper = $stmtDev->get_result()->num_rows > 0;

              // Check Investor
              $stmtInv = $conn->prepare(
                  "SELECT User_ID FROM Investor WHERE User_ID = ?"
              );
              $stmtInv->bind_param("i", $user_id);
              $stmtInv->execute();
              $isInvestor = $stmtInv->get_result()->num_rows > 0;

              // Admin check
              if (!is_null($user['Admin_ID'])) {
                  $_SESSION['role'] = 'admin';
                  header("Location: admin_dashboard.php");
              }
              elseif ($isDeveloper) {
                  $_SESSION['role'] = 'developer';
                  header("Location: developer_dashboard.php");
              }
              elseif ($isInvestor) {
                  $_SESSION['role'] = 'investor';
                  header("Location: investor_dashboard.php");
              }
              else {
                  $error = "User role not assigned.";
              }

              exit;

          } else {
              $error = "Invalid email or password.";
          }
      } else {
          $error = "Invalid email or password.";
      }
  }




?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TIN | Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="login.css">

</head>
<body>

  <div class="login-container">
    <div class="logo">
      <h1>TIN</h1>
      <span>Tech Investment Platform</span>
    </div>

    <h2>Login to your account</h2>

    <form action="#" method="POST">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required />
      </div>

      <button type="submit" class="login-btn">Login</button>
    </form>

    <div class="extra-links">
      <a href="#">Forgot password?</a>
      <a href="sign_up.php">Create account</a>
    </div>

    <div class="footer-text">
      © 2025 TIN · All rights reserved
    </div>
  </div>

</body>
</html>
<?php
  // Placeholder for future PHP login handling code
?>