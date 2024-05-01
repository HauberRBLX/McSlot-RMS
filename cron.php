<?php
require 'db_connection.php';
include 'settings/head.php';

date_default_timezone_set("Europe/Berlin");

function updateLastExecution()
{
    global $conn;
    $updateLastExecution = $conn->prepare("UPDATE queue_last_execution SET last_execution_datetime = NOW() WHERE id = 1");
    return $updateLastExecution->execute();
}

function addTimestampAndDeleteUsers()
{
    global $conn;

    $addTimestamp = $conn->prepare("UPDATE queue SET deletion_date = NOW(), complete = 1 WHERE TIMESTAMPDIFF(DAY, creation_date, NOW()) >= 7");

    if ($addTimestamp->execute()) {
      
        $deleteSettingsUsers = $conn->prepare("DELETE FROM settings_users WHERE user_id IN (SELECT user_id FROM queue WHERE TIMESTAMPDIFF(DAY, creation_date, NOW()) >= 7)");

        if ($deleteSettingsUsers->execute()) {
            $deleteOldUsers = $conn->prepare("DELETE FROM benutzer WHERE id IN (SELECT user_id FROM queue WHERE TIMESTAMPDIFF(DAY, creation_date, NOW()) >= 7)");

            if ($deleteOldUsers->execute()) {
                $countDeleted = $deleteOldUsers->affected_rows;

                if ($deleteOldUsers->error) {
                    return "<div class='alert alert-danger'>Fehler beim Löschen: " . $deleteOldUsers->error . "</div>";
                } else {
                    return ($countDeleted > 0) ? "<div class='alert alert-success'>Erfolg: Es wurde(n) $countDeleted Benutzer gelöscht.</div>" : "<div class='alert alert-success'>Es wurden keine Benutzer gelöscht, die älter als 7 Tage in der Löschqueue sind.</div>";
                 http_response_code(200);
				}
            } else {
                return "<div class='alert alert-danger'>Fehler: Das Löschen der alten Benutzer ist fehlgeschlagen.</div>";
            }
        } else {
            return "<div class='alert alert-danger'>Fehler: Das Löschen der Einträge in settings_users ist fehlgeschlagen.</div>";
        }
    } else {
        return "<div class='alert alert-danger'>Fehler: Das Hinzufügen des Zeitstempels ist fehlgeschlagen.</div>";
    }
}

function cdnKeyDelete()
{
    $cdnConn = new mysqli('localhost', 'RMSystem_CDN', 'zBf9567s^', 'RMSystem_CDN');

    if ($cdnConn->connect_error) {
        die("<div class='alert alert-danger'>Fehler bei der Verbindung zur CDN-Datenbank: " . $cdnConn->connect_error . "</div>");
    }

    $deleteFilesAccess = $cdnConn->prepare("DELETE FROM files_access WHERE complete = 1");

    if ($deleteFilesAccess->execute()) {
        $deletedRows = $deleteFilesAccess->affected_rows;
        echo "<div class='alert alert-success'>Erfolg: $deletedRows Einträge in files_access wurden gelöscht.</div>";
    } else {
        echo "<div class='alert alert-danger'>Fehler: Löschen von Einträgen in files_access fehlgeschlagen.</div>";
    }
}


function deleteAccount()
{
    if (updateLastExecution()) {
        $result = addTimestampAndDeleteUsers();
        http_response_code($result === "<div class='alert alert-success'>Erfolg: Es wurde(n) 0 Benutzer gelöscht.</div>" ? 200 : 500);
        echo $result;
    } else {
        http_response_code(500);
        echo "<div class='alert alert-danger'>Fehler: Die Operation ist fehlgeschlagen.</div>";
    }
}

if (isset($_GET['key'], $_GET['type']) && $_GET['type'] === 'account_delete') {
    $receivedKey = $_GET['key'];

    $getCronKey = $conn->prepare("SELECT setting_value FROM settings WHERE setting_name = 'cron_key'");

    if ($getCronKey->execute()) {
        $getCronKey->bind_result($cronKey);
        $getCronKey->fetch();

        if ($cronKey !== null) {
            if ($receivedKey === $cronKey) {
                require 'settings/config.php';
                require 'db_connection.php';
                deleteAccount();
                $conn->close();
                return;
            } else {
                http_response_code(404);
                echo "<div class='alert alert-danger'>Fehler: Ungültiger Sicherheits-Schlüssel.</div>";
            }
        } else {
            http_response_code(404);
            echo "<div class='alert alert-danger'>Fehler: Der cron_key konnte nicht aus der Datenbank abgerufen werden.</div>";
        }
    } else {
        http_response_code(404);
        echo "<div class='alert alert-danger'>Fehler: Die Abfrage des cron_key ist fehlgeschlagen.</div>";
    }
} elseif (isset($_GET['key'], $_GET['type']) && $_GET['type'] === 'cdn_key_delete') {
  $getCronKey = $conn->prepare("SELECT setting_value FROM settings WHERE setting_name = 'cron_key'");
      if ($getCronKey->execute()) {
        $getCronKey->bind_result($cronKey);
        $getCronKey->fetch();
      }
    $receivedKey = $_GET['key'];

    if ($receivedKey === $cronKey) {
        cdnKeyDelete();
    } else {
        http_response_code(404);
        echo "<div class='alert alert-danger'>Fehler: Ungültiger Sicherheits-Schlüssel für CDN-Löschung.</div>";
    }
} else {
    http_response_code(404);
    echo "<div class='alert alert-danger'>Fehler: Ungültige Anfrage. Überprüfe den Sicherheits-Schlüssel und den Typ.</div>";
}
?>