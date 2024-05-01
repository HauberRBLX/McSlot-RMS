<?php

$cdn_servername = "localhost";
$cdn_username = "RMSystem_CDN";
$cdn_password = "zBf9567s^";
$cdn_dbname = "RMSystem_CDN";

$remote_conn = new mysqli($cdn_servername, $cdn_username, $cdn_password, $cdn_dbname);

if ($remote_conn->connect_error) {
    echo "Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut. (Fehlercode 1-DB-CDN)";
    exit;
}

?>