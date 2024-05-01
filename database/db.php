<?php



$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo "Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut. (Fehlercode 1)";
    exit;
}

// ALLGEMEIN # START #

$resultName = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'website_name'");
$name = ($resultName->num_rows > 0) ? $resultName->fetch_assoc()["setting_value"] : null;

?>