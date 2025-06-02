
<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Waiting for Approval</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6fb; text-align: center; padding-top: 100px; }
        .wait-box { background: #fff; padding: 40px 30px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 24px rgba(0,0,0,0.07);}
        h1 { color: #ffb347; }
        p { color: #888; }
    </style>
</head>
<body>
    <div class="wait-box">
        <h1>Your account is pending approval</h1>
        <p>Please wait for the admin to approve your registration.<br>
        You will be notified once your account is activated.</p>
        <a href="login.php" style="color:#ffb347;text-decoration:underline;">Logout</a>
    </div>
</body>
</html>