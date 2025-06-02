<?php
session_start(); 
require 'db.php'; 

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($hashedPassword);
        $stmt->fetch();

        if ($password === $hashedPassword) {
            $_SESSION['email'] = $email; 
            header("Location: dashboard.php"); 
            exit();
        } else {
            $error = "Invalid password. Try again.";
        }
    } else {
        $error = "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>EZ-ORDER | Sign In</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div class="container">
    <div class="left">
      <img src="EZ-logo.png" alt="EZ-ORDER Logo">
      <h2>EZ-ORDER</h2>
      <p>Order. Grab. Eat</p>
    </div>
    <div class="right">
      <h2>Sign In</h2>

      <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label><span class="icon">👤</span> Email</label>
          <input type="email" name="email" required>
        </div>
        <div class="form-group">
          <label><span class="icon">🔒</span> Password</label>
          <input type="password" name="password" id="password" required>
        </div>
        <div class="form-group inline">
          <input type="checkbox" id="showPassword" onclick="togglePassword()">
          <label for="showPassword">Show Password</label>
        </div>
        <button type="submit">SIGN IN</button>
      </form>
    </div>
  </div>

  <script>
    function togglePassword() {
      const pass = document.getElementById("password");
      pass.type = pass.type === "password" ? "text" : "password";
    }
  </script>
</body>
</html>
