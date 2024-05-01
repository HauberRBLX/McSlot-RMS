<?php
session_start();
require 'db_connection.php';

function get_client_ip()
{
    $ipAddress = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
        $ipAddress = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }
    return $ipAddress;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
    exit;
}

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'];
$result = $conn->query($sql);
$user = $result->fetch_assoc();

$last_download_timestamp = isset($_SESSION['last_download_timestamp']) ? $_SESSION['last_download_timestamp'] : 0;
$current_timestamp = time();
$time_difference = $current_timestamp - $last_download_timestamp;
if ($time_difference < 30) {
    $_SESSION['error_message'] = "Du kannst nur alle 30 Sekunden eine Datei herunterladen. Bitte warte einen Moment.";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

if ($user['role'] == 'Supporter' || $user['role'] == 'Admin' || $user['role'] == 'Owner') {
    // Führe den Code aus, wenn die Rolle Supporter, Admin oder Owner ist
} else {
    $_SESSION['last_download_timestamp'] = $current_timestamp;
}

if ($user['gesperrt'] == 1) {
    session_destroy();
    header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
    exit;
}

if (isset($_GET['file']) && !empty($_GET['file'])) {
    // Extrahiere den Schlüssel aus der URL
    $key = md5(uniqid());

    // Überprüfe, ob ein gültiger Schlüssel vorhanden ist
if (!empty($key)) {
    $real_ip = get_client_ip();
    $file_ = $_GET['file'];
    $file_sql = "SELECT released FROM files WHERE name = '" . $_GET['file'] . "'";
    $file_result = $conn->query($file_sql);

    if ($file_result->num_rows > 0) {
        $file = $file_result->fetch_assoc();

        if ($file['released'] == 'Yes' || ($file['released'] == 'Only Verfied' && $user['verified'] == 1)) {
            // Nur wenn die Datei freigegeben ist oder der Benutzer verifiziert ist, fügen Sie den Zugriff in die Datenbank ein
            $sql = "INSERT INTO files_access (`key`, user, file) VALUES ('$key', " . $_SESSION['user_id'] . ", '" . $_GET['file'] . "')";
            include 'database/db_cdn.php';
            $remote_conn = new mysqli($cdn_servername, $cdn_username, $cdn_password, $cdn_dbname);

            if ($remote_conn->connect_error) {
                die("Verbindung zum anderen MySQL-Server fehlgeschlagen: " . $remote_conn->connect_error);
            }

            if ($remote_conn->query($sql) === TRUE) {
                // Erfolgreich in die Datei-Zugriffstabelle eingefügt, fügen Sie nun den Eintrag in die Datei-Download-Historie ein
                $download_key = $key;
                $file_name = $_GET['file'];

                $history_sql = "INSERT INTO files_history (user, download_key, file, date, cdn) 
        		VALUES (" . $_SESSION['user_id'] . ", '$download_key', '$file_name', NOW(), '$cdn_link')";

                if ($remote_conn->query($history_sql) === TRUE) {
                    // Erfolgreich in die Datei-Download-Historie eingefügt
                    $remote_conn->close();
                } else {
                    echo "Fehler beim Einfügen des Eintrags in die Datei-Download-Historie: " . $remote_conn->error;
                    $remote_conn->close();
                    exit;
                }
            } else {
                echo "Fehler beim Einfügen des Schlüssels in die remote Datenbank: " . $remote_conn->error;
                $remote_conn->close();
                exit;
            }
        }
    }
}

    // Überprüfe, ob die Datei zum Download freigegeben ist
    $file_sql = "SELECT released FROM files WHERE name = '" . $_GET['file'] . "'";
    $file_result = $conn->query($file_sql);
    if ($file_result->num_rows > 0) {
        $file = $file_result->fetch_assoc();
        if ($file['released'] == 'Yes' || ($file['released'] == 'Only Verfied' && $user['verified'] == 1)) {
            $hoster_sql = "SELECT * FROM hoster_list WHERE status = 'Online' ORDER BY RAND() LIMIT 1";
            $hoster_result = $conn->query($hoster_sql);

            if ($hoster_result->num_rows > 0) {
                $hoster = $hoster_result->fetch_assoc();
                $download_url = $hoster['hoster_url'] . '?file=' . $_GET['file'] . '&key=' . $key;
                header("Location: " . $download_url);
                exit;
            } else {
                $_SESSION['error_message'] = "Es gibt keine aktiven Hoster für den Download. Bitte versuche es später erneut.";
            }
        } elseif ($file['released'] == 'Only Verfied') {
            $_SESSION['error_message'] = "Du bist nicht berechtigt, diese Datei herunterzuladen, da du kein verifizierter Benutzer bist.";
        } else {
            $_SESSION['error_message'] = "Die Datei ist für den Download noch nicht freigegeben.";
        }
    } else {
        $_SESSION['error_message'] = "Die angeforderte Datei existiert nicht.";
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    $_SESSION['error_message'] = "Es gab einen Fehler mit dem Download-Server, bitte versuche es später erneut";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}


// IP CHECK [START]


function checkIPThreats($get_client_ip)
{
    // Füge die IP-Adresse zur Whitelist hinzu
    $whitelisted_ips = ['167.235.30.57']; // Füge hier weitere IPs hinzu, falls nötig

    // Füge die IP-Adresse zur Blacklist hinzu
    $blacklisted_ips = []; // Füge hier weitere IPs hinzu, die blockiert werden sollen

    // Überprüfe, ob die IP in der Whitelist ist
    if (in_array($get_client_ip, $whitelisted_ips)) {
        return ['is_whitelisted' => true];
    }

    // Überprüfe, ob die IP in der Blacklist ist
    if (in_array($get_client_ip, $blacklisted_ips)) {
        return ['is_blacklisted' => true, 'message' => 'Der Zugriff von dieser IP-Adresse ist blockiert.'];
    }

    $api_url = "https://api.incolumitas.com/?q=" . $get_client_ip;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $threat_data = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $threat_message = '';

    if ($threat_data['is_vpn']) {
        $threat_message = 'Die Verwendung von VPNs ist nicht erlaubt.';
    } elseif ($threat_data['is_proxy']) {
        $threat_message = 'Die Verwendung von Proxies ist nicht erlaubt.';
    } elseif ($threat_data['is_tor']) {
        $threat_message = 'Die Verwendung von Tor ist nicht erlaubt.';
    } elseif ($threat_data['is_datacenter']) {
        $threat_message = 'Die Verwendung von als missbräuchlich markierten IP-Adressen ist nicht erlaubt.';
    }

    return $threat_data;
}

$real_ip = get_client_ip();
$result = checkIPThreats($real_ip);

// IP CHECK [END]
?>
