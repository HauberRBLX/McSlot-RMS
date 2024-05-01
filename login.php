<!-- login.php -->



<?php

date_default_timezone_set('Europe/Berlin');

$currentDate = date('Y-m-d');

if (date('m', strtotime($currentDate)) == 12) {
    echo '<script src="' . $siteurl . '/assets/style/js/snow.js"></script>';
}

if ((date('m-d', strtotime($currentDate)) >= '01-01' && date('m-d', strtotime($currentDate)) <= '01-08')) {
    echo '<script src="' . $siteurl . '/assets/style/js/new_year.js"></script>';
}

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


function logMessage($message) {
    $logFile = 'logs/login_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logID = microtime(true); // Verwende den Zeitstempel als eindeutige ID
    $logMessage = "[$timestamp - $logID] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

include 'settings/config.php';
include 'settings/head.php';

session_set_cookie_params(5 * 24 * 60 * 60);
session_start();
require 'db_connection.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $dash_url . '.php' : $siteurl . $dash_url));
    exit;
}


$sqlLoginStatus = "SELECT setting_value FROM settings WHERE setting_name = 'login'";
$resultLoginStatus = $conn->query($sqlLoginStatus);
$loginAktiviert = $resultLoginStatus->fetch_assoc()['setting_value'];

function isIPBanned($ip_address)
{
    global $conn;

    $sql = "SELECT COUNT(*) as count FROM ip_bans WHERE ip_address = ? AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ip_address);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];

    return $count > 0;
}

function checkLoginAttempts($ip_address)
{
    global $conn;

    $sql = "SELECT COUNT(*) as attempt_count FROM login_attempts WHERE ip_address = ? AND login_status = 'abgelehnt' AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ip_address);
    $stmt->execute();
    $result = $stmt->get_result();
    $attempt_count = $result->fetch_assoc()['attempt_count'];

    return $attempt_count;
}
// LOGIN SYSTEM

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ip_address = get_client_ip();

    // Überprüfe, ob die IP-Adresse gesperrt ist
    $ban_info = getBanInfo($ip_address);

    if ($ban_info && isBanExpired($ban_info['ban_time'])) {
        // Sperre ist abgelaufen, entferne die Sperre
        removeIPBan($ip_address);
        clearLoginAttempts($ip_address);
        $ban_info = null; // Setze $ban_info auf null, um die Sperre nicht weiter zu behandeln
    }

    if ($ban_info) {
        // IP-Adresse ist gesperrt, zeige eine entsprechende Nachricht oder leite sie auf eine Sperrseite um
        $error_message_login = "Deine IP-Adresse ist gesperrt. Bitte kontaktiere den Support.";
    } else {
        if ($loginAktiviert == 0) {
            $error_message_login = "Die Anmeldung ist derzeit deaktiviert. Bitte versuche es später erneut.";
        } else {
            $login = $_POST['login'];
            $password = $_POST['password'];
            $remember_me = isset($_POST['remember_me']) ? true : false;

            $sql = "SELECT * FROM benutzer WHERE name = ? OR kontonummer = ? OR email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $login, $login, $login);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    if (!empty($user['totp_secret'])) {
                        $login_key = bin2hex(random_bytes(8)); // Eindeutiger Schlüssel generieren
                        $sql = "INSERT INTO two_factor (user, login_key) VALUES (?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ss", $user['id'], $login_key);
                        $stmt->execute();
                        header("Location: two_factor.php?key=$login_key");
                        exit;
                    } else {
                        $expiry = time() + 5 * 24 * 3600; // 5 Tage Ablaufzeit
                        setcookie("user_name", $user['name'], $expiry, "/");
                        setcookie("PHPSESSID", session_id(), $expiry, "/"); // Verlängere die Lebensdauer des Session-Cookies


                        if ($user['gesperrt'] == 1) {
                            if (!empty($user['sperrgrund'])) {
                                $error_message = "Dieses Konto wurde von einem Administrator gesperrt. <br>Grund: " . $user['sperrgrund'];
                            } else {
                                $error_message = "Dieses Konto wurde von einem Administrator gesperrt.";
                            }
                        } else {
                            $_SESSION['user_id'] = $user['id'];
                            $expiry = time() + 30 * 24 * 3600; // 30 Tage Ablaufzeit
                            setcookie("user_name", $user['name'], $expiry, "/");
                            setcookie("PHPSESSID", session_id(), $expiry, "/"); // Verlängere die Lebensdauer des Session-Cookies


                            if ($remember_me) {
                                $token = bin2hex(random_bytes(32));
                                setcookie("remember_me_token", $token, $expiry, "/");
                                setcookie("user_id", $user['id'], $expiry, "/");
                                setcookie("user_name", $user['name'], $expiry, "/");
                            }
                            $real_ip = get_client_ip();
                            $result = checkIPThreats($real_ip);

                            // Überprüfe, ob Bedrohungen erkannt wurden
                            if ($result['is_vpn'] || $result['is_tor'] || $result['is_proxy'] || $result['is_datacenter']) {
                                $error_message = 'Die Verwendung von VPNs, Tor, Proxies oder als missbräuchlich markierten IP-Adressen ist nicht erlaubt.';
                            } else {
                                if ($user['deleted'] == 1) {
                                    header("Location: reactive.php");
                                    exit;
                                }
                                sleep(1);
                                $ip_address = get_client_ip();
                                $update_ip_sql = "UPDATE benutzer SET letzte_ip = ? WHERE id = ?";
                                $stmt = $conn->prepare($update_ip_sql);
                                $stmt->bind_param("si", $ip_address, $_SESSION['user_id']);
                                $stmt->execute();
                                $login_status = 'erfolgreich';
                                $sql = "INSERT INTO login_history (user_id, login_time, ip_address, login_status) VALUES (?, NOW(), ?, ?)";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("iss", $user['id'], $ip_address, $login_status);
                                $stmt->execute();
                                $_SESSION['success_message'] = "Willkommen zurück, " . $user['name'] . ".";
                                header("Location: " . ($phpenable === 'true' ? $siteurl . $dash_url . '.php' : $siteurl . $dash_url));
                                exit;
                            }
                        }
                    }
                } else {
                    $ip_address = get_client_ip();
                    if (isIPBanned($ip_address)) {
                        $error_message_login = "Deine IP-Adresse ist gesperrt. Bitte kontaktiere den Support.";

                    }

                    // Überprüfe die Anzahl der Anmeldeversuche
                    $login_attempts = checkLoginAttempts($ip_address);

                    if ($login_attempts >= 5) {
                        // Speichere den IP-Ban in der Datenbank
                        $ban_reason = "Fünf aufeinanderfolgende fehlgeschlagene Anmeldeversuche";
                        $sqlBanIP = "INSERT INTO ip_bans (ip_address, ban_reason) VALUES (?, ?)";
                        $stmtBanIP = $conn->prepare($sqlBanIP);
                        $stmtBanIP->bind_param("ss", $ip_address, $ban_reason);
                        $stmtBanIP->execute();

                        // Optional: Blockiere die IP-Adresse direkt in der Firewall oder auf Anwendungsebene
                        $error_message_login = "Deine IP-Adresse wurde gesperrt. Bitte kontaktiere den Support.";

                    }

                    // Fehlgeschlagener Login
                    $error_message = "Ungültige Anmeldeinformationen";
                    $login_status = 'abgelehnt';
                    $sql = "INSERT INTO login_attempts (ip_address, login_status) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $ip_address, $login_status);
                    $stmt->execute();

                    $sql = "INSERT INTO login_history (user_id, login_time, ip_address, login_status) VALUES (?, NOW(), ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iss", $user['id'], $ip_address, $login_status);
                    $stmt->execute();
                }
            } else {
                $error_message = "Dieses Konto existiert nicht.";
            }
        }
    }
}


function saveLoginKey($user_id, $key) {
    // Annahme: $conn ist die Verbindung zur Datenbank

    // SQL-Statement zum Einfügen des Schlüssels in die Datenbank
    $sql = "INSERT INTO two_factor (user_id, login_key) VALUES (?, ?)";
    
    // Vorbereiten des SQL-Statements
    $stmt = $conn->prepare($sql);
    
    // Binden der Parameter
    $stmt->bind_param("is", $user_id, $key);
    
    // Ausführen des vorbereiteten Statements
    $stmt->execute();
    
    // Schließen des Statements
    $stmt->close();
}

function isBanExpired($ban_time)
{
    // Überprüfe, ob die Sperre älter als 30 Minuten ist
    $current_time = time();
    $ban_time_timestamp = strtotime($ban_time);
    $expiration_time = $ban_time_timestamp + 30 * 60; // 30 Minuten in Sekunden
    return $current_time > $expiration_time;
}

function getBanInfo($ip_address)
{
    // Hole Informationen zur IP-Sperre aus der Datenbank
    global $conn;
    $sql = "SELECT * FROM ip_bans WHERE ip_address = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ip_address);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Es gibt eine Sperre für die IP-Adresse
        return $result->fetch_assoc();
    } else {
        // Es gibt keine Sperre für die IP-Adresse
        return null;
    }
}

function clearLoginAttempts($ip_address)
{
    // Lösche die login_attempts für die angegebene IP-Adresse
    global $conn;
    $sql = "DELETE FROM login_attempts WHERE ip_address = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ip_address);
    $stmt->execute();
}

function removeIPBan($ip_address)
{
    // Entferne die IP-Sperre aus der Datenbank
    global $conn;
    $sql = "DELETE FROM ip_bans WHERE ip_address = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ip_address);
    $stmt->execute();
}

// IP CHECK [START]

function checkIPThreats($get_client_ip)
{
    // Füge die IP-Adresse zur Whitelist hinzu
    $whitelisted_ips = ['167.235.30.57']; // Füge hier weitere IPs hinzu, falls nötig

    // Füge die IP-Adresse zur Blacklist hinzu
    $blacklisted_ips = ['87.164.204.28', '10.0.0.1']; // Füge hier weitere IPs hinzu, die blockiert werden sollen

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

?>

<title>
    <?= str_replace('{websiteName}', $name, $translations['login']['title']) ?>
</title>

<body>
    <div class="container" style="max-width: 400px;">
        <br><br><br>
        <div style="text-align: center;">
            <img src="<?= $logourl_dark ?>" alt="McSlot Banner" style="max-width: 100%; height: auto;">
        </div>
        <h1 class="text-center mt-5">
            <?= str_replace('{websiteName}', $name, $translations['login']['login_title']) ?>
        </h1>

        <?php
        if ($error_message) {
            echo '<div class="alert alert-danger" role="alert">
            <div class="alert-group-prepend">
            <span class="alert-group-icon text-">
            <i class="fa-solid fa-circle-exclamation"></i>
            </span> ' . $error_message . '
            </div>
            </div>';
            
        }

        if ($error_message_login) {
            echo '<div class="alert alert-danger">' . $error_message_login . '</div>';
        }


        ?>
        <?php if (isset($_SESSION['info_message'])) { ?>
               <div class="alert alert-info" role="alert">
               <div class="alert-group-prepend">
               <span class="alert-group-icon text-">
               <i class="fa-solid fa-circle-info"></i>
               </span>
               <?php echo $_SESSION['info_message']; ?>
            </div>
               </div>
            <?php unset($_SESSION['info_message']); // Lösche die Session-Variablen nach der Anzeige ?>
        <?php } ?>

        <?php if (isset($_SESSION['success_message'])) { ?>
               <div class="alert alert-success" role="alert">
               <div class="alert-group-prepend">
               <span class="alert-group-icon text-">
               <i class="fa-solid fa-circle-info"></i>
               </span>
               <?php echo $_SESSION['success_message']; ?>
            </div>
               </div>
            <?php unset($_SESSION['success_message']); // Lösche die Session-Variablen nach der Anzeige ?>
        <?php } ?>
<?php
					     echo '<div class="alert alert-group alert-danger alert-icon" role="alert">
    <div class="alert-group-prepend">
        <span class="alert-group-icon text-">
            <i class="fa-regular fa-circle-exclamation"></i>
        </span>
    </div>
    <div class="alert-content">
        McSlot Schließt am 10.05.2024
    </div>
    <div class="alert-action">
		<a href="closed" class="btn btn-neutral" aria-label="Hinzufügen">Weitere Informationen</a>
    </div>
</div>';
		?>
        <center>
            <div class="alert alert-dark">
                <?= str_replace('{here}', '<a href="changelogs/">' . $translations['login']['changelogs_here'] . '</a>', $translations['login']['changelogs']) ?>
            </div>
        </center>
        <form action="<?= ($phpenable === 'true' ? $login_url . '.php' : $login_url) ?>" method="post" class="mt-3">
            <div class="mb-3">
                <label for="login" class="form-label">
                    <?= str_replace('{websiteName}', $name, $translations['login']['username']) ?>
                </label>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-user"></i> </span>
                    <input type="text" name="login" id="login" class="form-control" aria-describedby="basic-addon1"
                        required>
                </div>
            </div>

            <div class="mb-3">
<div class="d-flex align-items-center justify-content-between"><div><label class="form-control-label"><?= str_replace('{websiteName}', $name, $translations['login']['password']) ?></label></div><div class="mb-2"><a href="<?= ($phpenable === 'true' ? $reset_url . '.php' : $reset_url) ?>" class="small text-muted text-underline--dashed border-primary" data-toggle="password-text" data-target="#input-password">Passwort vergessen</a></div></div>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-key"></i> </span>
                    <input type="password" name="password" id="password" class="form-control"
                        aria-describedby="basic-addon1" required>
                </div>
            </div>

            <center>
                <div class="form-check">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="customCheck2" name="remember_me">
                        <label class="custom-control-label" for="customCheck2">
                            <?= str_replace('{websiteName}', $name, $translations['login']['remember_me']) ?>
                        </label>
                    </div>
                </div>
            </center><br>

            <center>
<button type="submit" class="btn btn-primary" onclick="showLoader(this)" id="submitButton">
    <span id="buttonContent"><?= str_replace('{websiteName}', $name, $translations['login']['login_button']) ?></span>
    <span id="loader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
</button>


				<script>
function showLoader(button) {
    var buttonContent = button.querySelector('#buttonContent');
    var loader = button.querySelector('#loader');

    buttonContent.style.display = 'none';
    loader.style.display = 'inline-block';
    setTimeout(function() {
        loader.style.display = 'none';
        buttonContent.style.display = 'inline';
    }, 2000);
}


				</script>
            </center>
        </form>
        <p class="text-center">
            <br>
            <a href="<?= ($phpenable === 'true' ? $register_url . '.php' : $register_url) ?>"
                class="btn btn-animated btn-primary btn-animated-x">
                <span class="btn-inner--visible">
                    <?= str_replace('{websiteName}', $name, $translations['login']['register_button']) ?>
                </span>
                <span class="btn-inner--hidden">
                    <i class="fa-solid fa-user-plus"></i>
                </span>
            </a>
        </p><br>
        <p class="text-success text-center"><i class="fas fa-lock"></i>
            <?= str_replace('{websiteName}', $name, $translations['login']['data_security']) ?>
        </p>


    </div>
</body>

</html>


<?php

  include 'settings/footer.php';
  
  ?>