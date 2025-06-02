<!DOCTYPE html>
<html>
<head>
    <title>Sign In on Ezorder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo-section">
                <div class="logo-content">
                    <img src="Picture/logo2.png" alt="EZ-ORDER" class="logo">
                    <h2>EZ-ORDER</h2>
                    <p>Order. Eat. Enjoy</p>
                </div>
            </div>
            <div class="form-section">
                <div class="form-wrapper">
                    <p class="sign-in-text">Sign In</p>
                    <?php
                    session_start();
                    require_once 'function.php';
                    $login_message = "";
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        $username = $_POST['email'];
                        $password = $_POST['password'];
                        $remember = isset($_POST['remember']);

                        if (seller_login($username, $password, $remember)) {
                            header("Location: dashboard.php");
                            exit();
                        } else {
                            $login_message = "<div class='alert error'>Invalid username or password!</div>";
                        }
                    }
                    if ($login_message) {
                        echo $login_message;
                    }
                    ?>
                    <form action="login.php" method="post" class="login-form">
                        <div class="form-group">
                            <input type="text" id="email" name="email" placeholder="Email" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="password" id="password" name="password" placeholder="Password" required>
                        </div>
                        
                        <div class="form-options">
                            <div class="remember-me">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">Remember Password</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="sign-in-button">SIGN IN</button>
                        
                        <div class="register-link">
                            <p>Don't have an account? <a href="register.php">Sign Up</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html><?php
function login_admin($username, $password, $remember) {
    return seller_login($username, $password, $remember);
}
?>