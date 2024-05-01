<?php
// Verbindung zur Datenbank herstellen
session_start();
require '../../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'] . " AND (role = 'Admin' OR role = 'Owner')";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "Du hast keine Berechtigung für diese Aktion.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Überprüfen, ob die erforderlichen Felder gesendet wurden
    if (isset($_POST['changeDomain']) && isset($_POST['hoster_id'])) {
        $changeDomain = $_POST['changeDomain'];
        $hosterId = $_POST['hoster_id'];
        $maintenanceStatus = isset($_POST['maintenanceSwitch']) ? 'maintenance' : 'Online'; // 'maintenance' wenn Wartungsarbeiten aktiviert sind, ansonsten 'Online'
        
        // SQL-Befehl vorbereiten
        $sql = "UPDATE hoster_list SET hoster_url = ?, status = ? WHERE id = ?";
        
        // SQL-Befehl vorbereiten und Parameter binden
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $changeDomain, $maintenanceStatus, $hosterId);
        
        // SQL-Befehl ausführen
        if ($stmt->execute()) {
            // Erfolgreich aktualisiert
            $_SESSION['success_message'] = "Domain wurde erfolgreich aktualisiert.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            // Fehler beim Aktualisieren
            $_SESSION['error_message'] = "Fehler beim Aktualisieren der Domain: " . $conn->error;
            header("Location: " . $_SERVER['HTTP_REFERER']);
        }
        
        // Statement schließen
        $stmt->close();
    } else {
        // Fehler, falls erforderliche Felder nicht gesendet wurden
        $_SESSION['error_message'] = "Erforderliche Felder fehlen.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }
} else {
    // Fehler, falls die Anfrage nicht POST war
    $_SESSION['error_message'] = "Ungültige Anfrage.";
    header("Location: " . $_SERVER['HTTP_REFERER']);
}

// Verbindung zur Datenbank schließen
$conn->close();
?>
