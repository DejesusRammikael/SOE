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
                            if ($remember) {
                                // Set authentication cookies
                                setcookie('seller_id', $_SESSION['seller_id'], time() + (86400 * 30), "/");
                                setcookie('stall_id', $_SESSION['stall_id'], time() + (86400 * 30), "/");
                                // Set email and password cookies for pre-filling the form
                                setcookie('remembered_email', $username, time() + (86400 * 30), "/");
                                setcookie('remembered_password', $password, time() + (86400 * 30), "/");
                            } else {
                                // Remove cookies if they exist
                                setcookie('seller_id', '', time() - 3600, "/");
                                setcookie('stall_id', '', time() - 3600, "/");
                                setcookie('remembered_email', '', time() - 3600, "/");
                                setcookie('remembered_password', '', time() - 3600, "/");
                            }
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
                            <input type="text" id="email" name="email" placeholder="Email" required
                                value="<?php echo isset($_COOKIE['remembered_email']) ? htmlspecialchars($_COOKIE['remembered_email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <input type="password" id="password" name="password" placeholder="Password" required
                                value="<?php echo isset($_COOKIE['remembered_password']) ? htmlspecialchars($_COOKIE['remembered_password']) : ''; ?>">
                        </div>
                        
                        <div class="form-options">
                            <div class="remember-me">
                                <input type="checkbox" id="remember" name="remember"
                                    <?php if (isset($_COOKIE['remembered_email'])) echo 'checked'; ?>>
                                <label for="remember">Remember Me</label>
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