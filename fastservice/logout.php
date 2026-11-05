<?php
session_start();
$token = "text_token=".$_SESSION['user_token'];
unset($_SESSION['user_token']);
unset($_SESSION['user_id']);
header("Location: index.php?$token");
exit;
?>