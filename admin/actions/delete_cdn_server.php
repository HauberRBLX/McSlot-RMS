<?php
session_start();
require '../../db_connection.php';

// Überprüfe, ob der Benutzer angemeldet ist
if (!isset($_SESSION['user_id'])) {
    // Benutzer nicht angemeldet, leite ihn zur Anmeldeseite weiter
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

// Überprüfe die Berechtigung des Benutzers
$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'] . " AND (role = 'Admin' OR role = 'Owner')";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    // Benutzer hat keine Berechtigung, leite ihn zur Anmeldeseite weiter
    $_SESSION['error_message'] = "Du hast keine Berechtigung für diese Aktion.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

// Überprüfe, ob die 'id' des Hosters in der GET-Parameter vorhanden ist
if (isset($_GET['id'])) {
    // 'id' des Hosters aus den GET-Parametern holen
    $hoster_id = $_GET['id'];

    // SQL-Abfrage, um den Hoster anhand der 'id' zu löschen
    $delete_sql = "DELETE FROM hoster_list WHERE id = $hoster_id";

    if ($conn->query($delete_sql) === TRUE) {
        // Erfolgreich gelöscht, leite zur vorherigen Seite weiter
        $_SESSION['success_message'] = "Hoster wurde erfolgreich gelöscht ";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        // Fehler beim Löschen, Weiterleitung mit Fehlermeldung
        $_SESSION['error_message'] = "Fehler beim Löschen des Hosters: " . $conn->error;
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // 'id' des Hosters nicht vorhanden, Weiterleitung mit Fehlermeldung
    $_SESSION['error_message'] = "Fehler: Hoster-ID nicht angegeben.";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>