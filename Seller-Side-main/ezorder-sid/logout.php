<?php
<?php
session_start();
session_unset();
session_destroy();
// Remove cookies
setcookie('seller_id', '', time() - 3600, "/");
setcookie('stall_id', '', time() - 3600, "/");
header("Location: login.php");
exit;
?>