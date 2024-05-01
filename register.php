<!-- register.php -->

<meta name='og:title' content='Einkaufsliste • ELSystem'>
<meta name="description" content="Registrieren | ELSystem">
<meta name="keywords" content="Einkaufsliste, ELSystem">
<meta name="author" content="PvPMaster0001">
<meta name='copyright' content='ELSystem'>
<?php
date_default_timezone_set('Europe/Berlin');

$currentDate = date('Y-m-d');

if (date('m', strtotime($currentDate)) == 12) {
    echo '<script src="' . $siteurl . '/assets/style/js/snow.js"></script>';
}

if ((date('m-d', strtotime($currentDate)) >= '01-01' && date('m-d', strtotime($currentDate)) <= '01-08')) {
    echo '<script src="' . $siteurl . '/assets/style/js/new_year.js"></script>';
}
function logMessage($message) {
    $logFile = 'logs/register_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logID = microtime(true); // Verwende den Zeitstempel als eindeutige ID
    $logMessage = "[$timestamp - $logID] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
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

session_start();

require 'db_connection.php';

if (isset($_SESSION['user_id'])) {
    header("Location: $siteurl");
    exit;
}

$kontonummer = rand(1000000, 9999999);
$success_message = '0';
$gesperrt = 0;
$error_message = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $register_setting_sql = "SELECT setting_value FROM settings WHERE setting_name = 'register'";
    $register_setting_result = $conn->query($register_setting_sql);
    if ($register_setting_result) {
        $register_setting_row = $register_setting_result->fetch_assoc();
        $register_setting = $register_setting_row['setting_value'];
		sleep(1);
        if ($register_setting == 0) {
            $error_message = "Die Registrierung ist derzeit deaktiviert.";
            logMessage("FEHLER: $error_message");
        } elseif ($register_setting == 1) {
        $name = htmlspecialchars($_POST['name']);
		$ipAddress = get_client_ip();
        $browserAgent = $_SERVER['HTTP_USER_AGENT'];
        logMessage("Neue Registrierungsanfrage von Benutzer: $name, IP: $ipAddress, Browser: $browserAgent");
        if (strpos($name, ' ') !== false) {
            $error_message = "Der Benutzername darf keine Leerzeichen enthalten. Verwende stattdessen Unterstriche.";
            logMessage("FEHLER: Benutzer: $name, $error_message");
        } elseif (preg_match('/^[0-9]/', $name)) {
            $error_message = "Der Benutzername darf nicht mit einer Zahl beginnen.";
            logMessage("FEHLER: Benutzer: $name, $error_message");
        } elseif (!preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $name)) {
            $error_message = "Der Benutzername muss mit einem Buchstaben beginnen und darf nur Buchstaben und Zahlen enthalten.";
            logMessage("FEHLER: Benutzer: $name, $error_message");
        } else {
            $check_username_sql = "SELECT id FROM benutzer WHERE name = ?";
            $check_username_stmt = $conn->prepare($check_username_sql);
            $check_username_stmt->bind_param("s", $name);
            $check_username_stmt->execute();
            $check_username_result = $check_username_stmt->get_result();
            if ($check_username_result->num_rows > 0) {
                $error_message = "Dieser Benutzername ist bereits vergeben.";
                logMessage("FEHLER: Benutzer: $name, $error_message");
            }  else {
                $real_ip = get_client_ip();
                $result = checkIPThreats($real_ip);

                // Überprüfe, ob Bedrohungen erkannt wurden
                if ($result['is_vpn'] || $result['is_tor'] || $result['is_proxy'] || $result['is_datacenter']) {
                    $error_message = 'Die Verwendung von VPNs, Tor, Proxies oder als missbräuchlich markierten IP-Adressen ist nicht erlaubt.';
                    logMessage("FEHLER: Benutzer: $name, $error_message");
                } else {
                    // Prüfe, ob die IP bereits verwendet wird
                    $check_ip_sql = "SELECT id FROM benutzer WHERE letzte_ip = ?";
                    $check_ip_stmt = $conn->prepare($check_ip_sql);
                    $ip = get_client_ip(); // Den Wert der IP-Adresse in einer Variablen speichern
                    $check_ip_stmt->bind_param("s", $ip); // Den Wert der Variablen binden
                    $check_ip_stmt->execute();
                    $check_ip_result = $check_ip_stmt->get_result();

                    if ($check_ip_result->num_rows > 0) {
                        $error_message = "Du darfst keine mehrere Accounts haben. Bitte versuche es später erneut.";
                        logMessage("FEHLER: Benutzer: $name, $error_message");
                    } else {
                        // IP ist nicht verbunden mit einem vorhandenen Benutzer, fortfahren mit der Benutzererstellung
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        if (!checkPasswordStrength($_POST['password'])) {
                            $error_message = "Das Passwort muss mindestens 8 Zeichen lang sein und mindestens eine Zahl, einen Großbuchstaben und einen Kleinbuchstaben enthalten.";
                        	logMessage("FEHLER: Benutzer: $name, $error_message");
                        } else {
                            $sql = "INSERT INTO benutzer (name, password, kontonummer, gesperrt, letzte_ip) VALUES (?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ssiss", $name, $password, $kontonummer, $gesperrt, get_client_ip());
                            $stmt->execute();
                            $success_message = 'Benutzer erfolgreich erstellt!';
                            logMessage("ERFOLGREICH: Benutzer: $name, $success_message");
                            $_SESSION['user_id'] = $stmt->insert_id;
                            $_SESSION['username'] = $name;
                            date_default_timezone_set('Europe/Berlin');
                            $created_account_date = date('Y-m-d H:i:s');
                            $update_created_account_sql = "UPDATE benutzer SET created = ? WHERE id = ?";
                            $update_created_account_stmt = $conn->prepare($update_created_account_sql);
                            $update_created_account_stmt->bind_param("si", $created_account_date, $_SESSION['user_id']);
                            $update_created_account_stmt->execute();
                            header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
                        }
                    }
                }
            }
        }
    }
    }
    }
function checkPasswordStrength($password) {
    // Mindestlänge des Passworts
    $min_length = 8;
    
    // Überprüfe, ob das Passwort die Mindestlänge erfüllt
    if (strlen($password) < $min_length) {
        return false;
    }
    
    // Überprüfe, ob das Passwort Zahlen enthält
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    // Überprüfe, ob das Passwort Großbuchstaben enthält
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    // Überprüfe, ob das Passwort Kleinbuchstaben enthält
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    // Das Passwort erfüllt alle Anforderungen
    return true;
}

include 'settings/config.php';
include 'settings/head.php';
?>


<title>
    <?= str_replace('{websiteName}', $name, $translations['register']['title']) ?>
</title>


<body>
    <div class="container" style="max-width: 400px;">
        <br><br><br>
        <div style="text-align: center;">
            <img src="<?= $logourl_dark ?>" alt="McSlot Banner" style="max-width: 100%; height: auto;">
        </div>


        <h1 class="text-center mt-5">
            <?= str_replace('{websiteName}', $name, $translations['register']['register_title']) ?>
        </h1>
        <?php

        // IP CHECK [START]
        
        function checkIPThreats($get_client_ip)
        {
            // Füge die IP-Adresse zur Whitelist hinzu
            $whitelisted_ips = ['167.235.30.57']; // Füge hier weitere IPs hinzu, falls nötig
        
            // Füge die IP-Adresse zur Blacklist hinzu
            $blacklisted_ips = ['192.168.1.1', '10.0.0.1']; // Füge hier weitere IPs hinzu, die blockiert werden sollen
        
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
        
        if ($error_message) {
            echo '<div class="alert alert-danger" role="alert">
            <div class="alert-group-prepend">
            <span class="alert-group-icon text-">
            <i class="fa-solid fa-circle-exclamation"></i>
            </span> ' . $error_message . '
            </div>
            </div>';
            
        }
        ?>
        <?php

        if ($error_message_login) {
            echo '<div class="alert alert-danger">' . $error_message_login . '</div>';
        }
        ?>

        <form action="<?= ($phpenable === 'true' ? $register_url . '.php' : $register_url) ?>" method="post"
            class="mt-3">

            <div class="mb-3">
                <label for="login" class="form-label">
                    <?= str_replace('{websiteName}', $name, $translations['register']['username']) ?>
                </label>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-user"></i> </span>
                    <input type="text" name="name" class="form-control" aria-describedby="basic-addon1" maxlength="30"
                        required>
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">
                    <?= str_replace('{websiteName}', $name, $translations['register']['password']) ?>
                </label>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-key"></i></span>
                    <input type="password" name="password" id="password" class="form-control"
                        aria-describedby="basic-addon1" required>
                </div>
            </div>

            <center><button type="submit" class="btn btn-primary" onclick="showLoader(this)">
    <span id="buttonContent"><?= str_replace('{websiteName}', $name, $translations['register']['register_button']) ?></span>
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
}</script>
        </form>
    </div>
    <p class="text-center">
        <br>
        <a href="<?= ($phpenable === 'true' ? $login_url . '.php' : $login_url) ?>"
            class="btn btn-animated btn-primary btn-animated-x">
            <span class="btn-inner--visible">
                <?= str_replace('{websiteName}', $name, $translations['register']['login_button']) ?>
            </span>
            <span class="btn-inner--hidden">
                <i class="fa-solid fa-right-to-bracket"></i>
            </span>
        </a>

    </p><br>
    <p class="text-success text-center"><i class="fas fa-lock"></i>
        <?= str_replace('{websiteName}', $name, $translations['register']['data_security']) ?>
    </p>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"> </script>



</body>

</html>

<?php
include 'settings/footer.php';

?>