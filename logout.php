<!-- logout.php -->

<?php

include 'settings/config.php';

session_start();
session_destroy();
header("Location: $siteurl");
exit;
?>