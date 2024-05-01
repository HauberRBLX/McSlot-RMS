<?php
session_start();
require '../../db_connection.php';

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

// Überprüfen, ob der Benutzer Admin oder Eigentümer ist
$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'] . " AND (role = 'Admin' OR role = 'Owner')";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "Du hast keine Berechtigung für diese Aktion.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

// Überprüfen, ob das Formular gesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Daten aus dem Formular abrufen
    $newDomain = $_POST['newDomain'];

    // SQL-Befehl zum Einfügen des CDN-Servers in die Datenbank vorbereiten und Status auf "checking" setzen
    $sql = "INSERT INTO hoster_list (hoster_url, status) VALUES ('$newDomain', 'checking')";

    // SQL-Befehl ausführen
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "CDN-Server wurde erfolgreich hinzugefügt und der Status wird überprüft.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        $_SESSION['error_message'] = "Fehler beim Hinzufügen des CDN-Servers: " . $conn->error;
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }

    // Zurück zur vorherigen Seite leiten
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>