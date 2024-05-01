<?php
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

$registerValue = isset($_POST['register']) && $_POST['register'] === 'on' ? 1 : 0;
$loginValue = isset($_POST['login']) && $_POST['login'] === 'on' ? 1 : 0;

$MaintenanceValue = isset($_POST['maintenance']) && $_POST['maintenance'] === 'on' ? 1 : 0;
$lockdownValue = isset($_POST['lockdown']) && $_POST['lockdown'] === 'on' ? 1 : 0; // Neu hinzugefügt

$updateRegisterSql = "UPDATE settings SET setting_value = ? WHERE setting_name = 'register'";
$updateLoginSql = "UPDATE settings SET setting_value = ? WHERE setting_name = 'login'";

$MaintenanceSql = "UPDATE settings SET setting_value = ? WHERE setting_name = 'maintenance'";
$LockdownSql = "UPDATE settings SET setting_value = ? WHERE setting_name = 'lockdown'";

// Führe die SQL-Befehle aus
if ($stmt = $conn->prepare($LockdownSql)) { // Vorbereitung für Lockdown SQL-Befehl
    $stmt->bind_param("i", $lockdownValue); // Bindung des Parameters für Lockdown
    $stmt->execute();
    $stmt->close();

        if ($stmt = $conn->prepare($MaintenanceSql)) {
            $stmt->bind_param("i", $MaintenanceValue);
            $stmt->execute();
            $stmt->close();

            if ($stmt = $conn->prepare($updateRegisterSql)) {
                $stmt->bind_param("i", $registerValue);
                $stmt->execute();
                $stmt->close();

                if ($stmt = $conn->prepare($updateLoginSql)) {
                    $stmt->bind_param("i", $loginValue);
                    $stmt->execute();
                    $stmt->close();

                    $_SESSION['success_message'] = "Die Einstellungen wurden erfolgreich geändert.";
                    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $settings_url_admin . '.php' : $siteurl . $admin_directory . $settings_url_admin));
                } else {
                    $_SESSION['success_message'] = "Fehler beim Aktualisieren der Einstellungen:";
                    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $settings_url_admin . '.php' : $siteurl . $admin_directory . $settings_url_admin));
                }
        } else {
            $_SESSION['success_message'] = "Fehler beim Aktualisieren der Einstellungen:";
            header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $settings_url_admin . '.php' : $siteurl . $admin_directory . $settings_url_admin));
        }
    } else {
        $_SESSION['success_message'] = "Fehler beim Aktualisieren der Einstellungen:";
        header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $settings_url_admin . '.php' : $siteurl . $admin_directory . $settings_url_admin));
    }
} else {
    $_SESSION['success_message'] = "Fehler beim Aktualisieren der Einstellungen:";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $settings_url_admin . '.php' : $siteurl . $admin_directory . $settings_url_admin));
}

// Schließe die Datenbankverbindung
$conn->close();
?>
