<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
    exit;
}

function daysDifference($date1, $date2)
{
    $diff = strtotime($date2) - strtotime($date1);
    return floor($diff / (60 * 60 * 24));
}

// Überprüfe, ob genügend Zeit seit der letzten Anfrage vergangen ist
if (isset($_SESSION['last_request_time']) && time() - $_SESSION['last_request_time'] < 5) {
    $_SESSION['error_message'] = "Bitte warte 5 Sekunden, bevor du das Formular erneut absendest.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
    exit;
}

$_SESSION['last_request_time'] = time(); // Speichere die Zeit der aktuellen Anfrage


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $setting_identifier = $_POST['setting_identifier'];

    switch ($setting_identifier) {
        
        case 'night_mode':
            $night_mode = htmlspecialchars($_POST['nightMode'], ENT_QUOTES, 'UTF-8');

            // Überprüfen, ob der gewählte Nachtmodus gültig ist
            if (!in_array($night_mode, ['on', 'off', 'auto'])) {
                $_SESSION['error_message'] = "Ungültiger Nachtmodus.";
                header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
                exit;
            }

            // Überprüfen, ob der Benutzer bereits eine Einstellung hat
            $check_settings_stmt = $conn->prepare("SELECT user_id FROM settings_users WHERE user_id = ?");
            $check_settings_stmt->bind_param("i", $user_id);
            $check_settings_stmt->execute();
            $check_settings_result = $check_settings_stmt->get_result();

            if ($check_settings_result->num_rows > 0) {
                // Update der Einstellung, da bereits vorhanden
                $update_stmt = $conn->prepare("UPDATE settings_users SET night_mode = ? WHERE user_id = ?");
                $update_stmt->bind_param("si", $night_mode, $user_id);
                $update_stmt->execute();
            } else {
                // Neue Einstellung einfügen, da nicht vorhanden
                $insert_stmt = $conn->prepare("INSERT INTO settings_users (user_id, night_mode) VALUES (?, ?)");
                $insert_stmt->bind_param("is", $user_id, $night_mode);
                $insert_stmt->execute();
            }

            $_SESSION['success_message'] = "Nachtmodus erfolgreich aktualisiert.";
            header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
            exit;

            break;
        
        case 'username_change':
            $new_username = htmlspecialchars($_POST['new_username'], ENT_QUOTES, 'UTF-8');

            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $new_username)) {
                $_SESSION['error_message'] = "Der Benutzername muss mit einem Buchstaben beginnen und darf nur Buchstaben und Zahlen enthalten.";
                header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
                exit;
            }
            if (strlen($new_username) < 3 || strlen($new_username) > 20) {
                $_SESSION['error_message'] = "Der Benutzername muss zwischen 3 und 20 Zeichen lang sein.";
            }

            $check_last_change_stmt = $conn->prepare("SELECT last_username_change FROM benutzer WHERE id = ?");
            $check_last_change_stmt->bind_param("i", $user_id);
            $check_last_change_stmt->execute();
            $check_last_change_result = $check_last_change_stmt->get_result();

            if ($check_last_change_result->num_rows > 0) {
                $row = $check_last_change_result->fetch_assoc();
                $last_change_date = $row['last_username_change'];

                $remaining_days = 30 - daysDifference($last_change_date, date('Y-m-d'));

                if ($remaining_days > 0) {
                    $_SESSION['error_message'] = "Du kannst deinen Benutzernamen in <strong>$remaining_days Tagen</strong> wieder ändern.";
                    header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
                    exit;
                }
            }

            $check_stmt = $conn->prepare("SELECT id FROM benutzer WHERE name = ?");
            $check_stmt->bind_param("s", $new_username);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $_SESSION['error_message'] = "Der Benutzername existiert bereits. Bitte wählen Sie einen anderen.";
                header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
                exit;
            } else {
                $update_stmt = $conn->prepare("UPDATE benutzer SET name = ?, last_username_change = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $new_username, date('Y-m-d'), $user_id);
                $update_stmt->execute();

                $_SESSION['success_message'] = "Benutzername erfolgreich geändert: $new_username";
                header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
                exit;
            }

            break;
			
case 'email_change':
    $new_email = htmlspecialchars($_POST['new_email'], ENT_QUOTES, 'UTF-8');

    // Überprüfen Sie die Rolle des Benutzers
    $role_check_stmt = $conn->prepare("SELECT role FROM benutzer WHERE id = ?");
    $role_check_stmt->bind_param("i", $user_id);
    $role_check_stmt->execute();
    $role_check_stmt->bind_result($user_role);
    $role_check_stmt->fetch();
    $role_check_stmt->close();

    // Überprüfen, ob der Benutzer die erforderliche Rolle hat, um die Domain-Endung '.mcslot.net' zu verwenden
    if ($user_role !== 'Owner' && $user_role !== 'Admin' && $user_role !== 'Supporter') {
        $new_email_parts = explode('@', $new_email);
        if (isset($new_email_parts[1]) && $new_email_parts[1] === 'mcslot.net') {
            $_SESSION['error_message'] = "Nur Teammitglieder können E-Mails mit mcslot.net nutzen.";
            header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
            exit;
        }
    }

    // Überprüfen, ob die neue E-Mail-Adresse bereits verwendet wird
    $check_email_stmt = $conn->prepare("SELECT id FROM benutzer WHERE email = ?");
    $check_email_stmt->bind_param("s", $new_email);
    $check_email_stmt->execute();
    $check_email_stmt->store_result();

    if ($check_email_stmt->num_rows > 0) {
        $_SESSION['error_message'] = "Die eingegebene E-Mail-Adresse existiert bereits.";
        $check_email_stmt->close();
        header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
        exit;
    } else {
        // Aktualisieren Sie die E-Mail-Adresse, wenn alles in Ordnung ist
        $update_email_stmt = $conn->prepare("UPDATE benutzer SET email = ? WHERE id = ?");
        $update_email_stmt->bind_param("si", $new_email, $user_id);
        $update_email_stmt->execute();
        $update_email_stmt->close();

        $_SESSION['success_message'] = "E-Mail-Adresse erfolgreich aktualisiert.";
        header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
        exit;
    }
    break;



			
        case 'effects':
            $effects = isset($_POST['effects']) ? 1 : 0;

            $check_effects_stmt = $conn->prepare("SELECT user_id, effects FROM settings_users WHERE user_id = ?");
            $check_effects_stmt->bind_param("i", $user_id);
            $check_effects_stmt->execute();
            $check_effects_result = $check_effects_stmt->get_result();

            if ($check_effects_result->num_rows > 0) {
                $existing_effects = $check_effects_result->fetch_assoc()['effects'];

                // Überprüfen, ob der aktuelle Status dem gewünschten Status entspricht
                if ($existing_effects == $effects) {
                    $_SESSION['error_message'] = "Die Effekte sind bereits " . ($effects ? "aktiviert" : "deaktiviert");
                    header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
                    exit;
                } else {
                    $stmt = $conn->prepare("UPDATE settings_users SET effects = ? WHERE user_id = ?");
                    $stmt->bind_param("ii", $effects, $user_id);
                    $stmt->execute();
                }
            } else {
                $create_effects_stmt = $conn->prepare("INSERT INTO settings_users (user_id, effects) VALUES (?, ?)");
                $create_effects_stmt->bind_param("ii", $user_id, $effects);
                $create_effects_stmt->execute();
            }

            $_SESSION['success_message'] = "Die Effekte wurden " . ($effects ? "aktiviert" : "deaktiviert");
            header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
            exit;

            break;


        default:
            $_SESSION['error_message'] = "Ungültige Einstellungs-ID";
            header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
            exit;

            break;
    }
} else {
    $_SESSION['error_message'] = "Ungültige Einstellungs-ID";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url));
    exit;
}
?>