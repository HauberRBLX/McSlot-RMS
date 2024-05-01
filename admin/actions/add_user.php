<?php
session_start();
require '../../db_connection.php';

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'] . " AND (role = 'Admin' OR role = 'Owner')";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "Du hast keine Berechtigung für diese Aktion.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

// Überprüfen, ob das Formular gesendet wurde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Daten aus dem Formular extrahieren
    $newUserName = $_POST['newUserName'];
    $newUserPassword = $_POST['newUserPassword'];
    $newUserAdminRights = $_POST['newUserAdminRights'];

    // Validierung (hier musst du deine eigenen Validierungen hinzufügen)
    if (empty($newUserName) || empty($newUserPassword)) {
        $_SESSION['error_message'] = "Benutzername und Passwort dürfen nicht leer sein.";
        header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $users_url_admin . '.php' : $siteurl . $admin_directory . $users_url_admin));
        exit;
    }

    // Überprüfen, ob der Benutzername Zahlen am Anfang enthält oder Leerzeichen enthält
    if (preg_match('/^\d/', $newUserName) || strpos($newUserName, ' ') !== false) {
        $_SESSION['error_message'] = "Benutzername darf keine Zahlen am Anfang und keine Leerzeichen enthalten.";
        header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $users_url_admin . '.php' : $siteurl . $admin_directory . $users_url_admin));
        exit;
    }

    // Überprüfen, ob der Benutzername bereits existiert
    $checkUsernameQuery = "SELECT id FROM benutzer WHERE name = ?";
    $checkUsernameStmt = $conn->prepare($checkUsernameQuery);
    $checkUsernameStmt->bind_param("s", $newUserName);
    $checkUsernameStmt->execute();
    $checkUsernameResult = $checkUsernameStmt->get_result();

    if ($checkUsernameResult->num_rows > 0) {
        // Benutzername ist bereits vergeben
        $_SESSION['error_message'] = "Benutzername ist bereits vergeben. Bitte wähle einen anderen.";
        header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $users_url_admin . '.php' : $siteurl . $admin_directory . $users_url_admin));
        exit;
    }

    // Automatische Generierung einer Kontonummer
    $generatedAccountNumber = generateUniqueAccountNumber();

    // Erstellungsdatum festlegen
    $creationDate = date('Y-m-d H:i:s'); // Aktuelles Datum und Uhrzeit

    // Hash des Passworts erstellen (sicherer als das reine Speichern des Passworts)
    $hashedPassword = password_hash($newUserPassword, PASSWORD_DEFAULT);

    // SQL-Abfrage zum Einfügen des neuen Benutzers
    $insertUserQuery = "INSERT INTO benutzer (kontonummer, name, password, admin, gesperrt, created) VALUES (?, ?, ?, ?, 0, ?)";
    $insertUserStmt = $conn->prepare($insertUserQuery);
    $insertUserStmt->bind_param("issis", $generatedAccountNumber, $newUserName, $hashedPassword, $newUserAdminRights, $creationDate);


    // Überprüfen, ob die Abfrage erfolgreich ausgeführt wurde
    if ($insertUserStmt->execute()) {

        $roleLabel = ($newUserAdminRights == 1) ? 'Admin' : 'Mitglied';

        // Info-Meldung mit Benutzername, Rolle und Kontonummer
        $infoMessage = "Benutzername: $newUserName, Rolle: $roleLabel, Kontonummer: $generatedAccountNumber";

        $_SESSION['info_message'] = $infoMessage;

        // Erfolgsmeldung
        $_SESSION['success_message'] = "Benutzer erfolgreich hinzugefügt.";
    } else {
        $_SESSION['error_message'] = "Fehler beim Hinzufügen des Benutzers: " . $insertUserStmt->error;
    }

    // Verbindungen schließen
    $checkUsernameStmt->close();
    $insertUserStmt->close();
    $conn->close();

    // Weiterleitung zur Benutzerverwaltung
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $users_url_admin . '.php' : $siteurl . $admin_directory . $users_url_admin));
    exit;
} else {
    // Falls das Formular nicht gesendet wurde, zum Administrationsbereich umleiten
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $users_url_admin . '.php' : $siteurl . $admin_directory . $users_url_admin));
    exit;
}

// Funktion zur automatischen Generierung einer eindeutigen Kontonummer
function generateUniqueAccountNumber()
{
    global $conn;

    // Hier kannst du deine Logik zur Generierung der Kontonummer implementieren
    // Zum Beispiel könntest du eine Zufallszahl erzeugen oder einen Algorithmus verwenden
    // Hier ist ein einfaches Beispiel, das eine zufällige 6-stellige Kontonummer generiert

    do {
        $generatedAccountNumber = mt_rand(1000000, 9999999);

        // Überprüfen, ob die Kontonummer bereits existiert
        $checkAccountNumberQuery = "SELECT id FROM benutzer WHERE kontonummer = ?";
        $checkAccountNumberStmt = $conn->prepare($checkAccountNumberQuery);
        $checkAccountNumberStmt->bind_param("i", $generatedAccountNumber);
        $checkAccountNumberStmt->execute();
        $checkAccountNumberResult = $checkAccountNumberStmt->get_result();

    } while ($checkAccountNumberResult->num_rows > 0);

    return $generatedAccountNumber;
}
?>