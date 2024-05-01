<?php
session_start();
require '../../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /admin/login");
    exit;
}

// Holen Sie die Benutzerinformationen aus der Datenbank
$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'] . " AND (role = 'Admin' OR role = 'Owner')";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "Du hast keine Berechtigung für diese Aktion.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['userId'];
    $userName = $_POST['userName'];
    $userSperre = $_POST['userSperre']; // Neues Feld für Sperre
    $userTicketSperre = $_POST['userTicketSperre']; // Neues Feld für Ticket_Sperre
    $userSperrgrund = $_POST['userSperrgrund'];
    $userVerifiziert = $_POST['userVerifiziert'];
    $userRole = $_POST['userRole']; // Rolle des Benutzers

    // Überprüfen, ob der angemeldete Benutzer die Rolle "Owner" hat
    if ($user['role'] === 'Owner') {
        // Wenn der angemeldete Benutzer die Rolle "Owner" hat, überprüfen Sie, ob der zu bearbeitende Benutzer nicht auch die Rolle "Owner" hat
        if ($userRole !== 'Owner') {
            // Wenn der zu bearbeitende Benutzer nicht die Rolle "Owner" hat, führen Sie die Bearbeitung durch
            updateUser($conn, $userId, $userName, $userSperre, $userTicketSperre, $userSperrgrund, $userVerifiziert, $userRole);
        } else {
            $_SESSION['error_message'] = "Du hast keine Berechtigung, einen Nutzer mit der Rolle 'Owner' zu bearbeiten.";
            redirectToUserManagementPage();
        }
    } else {
        // Wenn der angemeldete Benutzer nicht die Rolle "Owner" hat, führen Sie die Bearbeitung durch
        updateUser($conn, $userId, $userName, $userSperre, $userTicketSperre, $userSperrgrund, $userVerifiziert, $userRole);
    }
} else {
    // Wenn das Skript über eine GET-Anfrage aufgerufen wird, leiten Sie es zur Benutzerverwaltungsseite zurück
    $_SESSION['error_message'] = "Fehler";
    redirectToUserManagementPage();
}

// Funktion zur Aktualisierung des Benutzers
function updateUser($conn, $userId, $userName, $userSperre, $userTicketSperre, $userSperrgrund, $userVerifiziert, $userRole) {
    if ($_SESSION['user_id'] == $userId) {
        // Der angemeldete Benutzer versucht, seinen eigenen Account zu bearbeiten
        $_SESSION['error_message'] = "Du darfst deinen eigenen Account nicht bearbeiten.";
        redirectToUserManagementPage();
    }

    if (!empty($_POST['userPassword'])) {
        $userPassword = $_POST['userPassword']; // Plain text password

        // Hash das neue Passwort sicher
        $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

        // Update the user's information in the database, einschließlich des Passworts
        $updateSql = "UPDATE benutzer SET name = ?, password = ?, gesperrt = ?, ticket_sperre = ?, sperrgrund = ?, verifed = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ssiisssi", $userName, $hashedPassword, $userSperre, $userTicketSperre, $userSperrgrund, $userVerifiziert, $userRole, $userId);
    } else {
        // Das Passwortfeld wurde leer gelassen, also aktualisiere die Datenbank ohne das Passwort
        $updateSql = "UPDATE benutzer SET name = ?, gesperrt = ?, ticket_sperre = ?, sperrgrund = ?, verified = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("siisssi", $userName, $userSperre, $userTicketSperre, $userSperrgrund, $userVerifiziert, $userRole, $userId);
    }

    if ($stmt->execute()) {
        // Benutzerinformationen erfolgreich aktualisiert
        $_SESSION['success_message'] = "Die Benutzer-Daten wurden erfolgreich geändert.";
        redirectToUserManagementPage();
    } else {
        // Fehlerbehandlung im Falle eines Datenbankupdatefehlers
        // Sie können eine Fehlermeldung festlegen und zur Benutzerverwaltungsseite zurückleiten
        $_SESSION['error_message'] = "Fehler";
        redirectToUserManagementPage();
    }
}

// Funktion zur Weiterleitung zur Benutzerverwaltungsseite
function redirectToUserManagementPage() {
    global $phpenable, $siteurl, $admin_directory, $users_url_admin;
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $users_url_admin . '.php' : $siteurl . $admin_directory . $users_url_admin));
    exit;
}
?>
