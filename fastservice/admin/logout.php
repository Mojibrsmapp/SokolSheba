<?php
// Start session
session_start();
$token = "admin_token=".$_SESSION['admin_token'];
// Destroy all session variables
unset($_SESSION['admin_token']);
unset($_SESSION['admin_id']);

// Redirect to login page (you can modify this to the correct login page path)
header("Location: index.php?$token");
exit;
?>