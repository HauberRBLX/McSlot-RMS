<!-- logout.php -->

<?php
include '../settings/config.php';
session_start();
session_destroy();
header("Location: " . ($phpenable === 'true' ? $siteurl . $logout_url . '.php' : $siteurl . $logout_url));
exit;
?>