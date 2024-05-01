<?php
include '../error/maintenance.php';
exit();
session_start();

// Funktion, um die IP-Adresse des Clients zu erhalten
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

function get_current_datetime()
{
    return date('d.m.Y H:i:s');
}

// Funktion zum Überprüfen der IP-Adresse
function check_ip_info($ipAddress)
{
    // API-URL
    $api_url = "https://api.incolumitas.com/?q=$ipAddress";
    // API-Aufruf
    $response = file_get_contents($api_url);
    // JSON in ein Array umwandeln
    $data = json_decode($response, true);
    // Rückgabe der Daten
    return $data;
}

// Gültige Site-Passwörter definieren
$validPasswords = array(
    "crunchyroll" => "18711",
    // Weitere Seiten und Passwörter hier hinzufügen
);

// Prüfen, ob das Passwort korrekt ist
if(isset($_POST['site_password'])) {
    $enteredPassword = $_POST['site_password'];
    // Prüfen, ob das eingegebene Passwort in den gültigen Passwörtern enthalten ist
    if(in_array($enteredPassword, $validPasswords)) {
        // Setze das Flag, dass der Benutzer eingeloggt ist
        $_SESSION['logged_in'] = true;
        // Setze den Typ der Seite für den Zugriff auf die Crunchyroll-Daten
        $_SESSION['page_type'] = 'crunchyroll';
        // Setze die Zeit für die automatische Abmeldung in 30 Minuten
        $_SESSION['expire_time'] = time() + 300; // 30 Minuten in Sekunden
        // Weiterleitung zur Seite mit dem Typ crunchyroll
        header("Location: ?type=crunchyroll");
        exit();
    } else {
        echo "<div class='alert alert-danger text-center' role='alert'>Falsches Passwort. Bitte versuche es erneut.</div>";
    }
}

// Überprüfe die Abmeldungszeit
if (isset($_SESSION['expire_time']) && $_SESSION['expire_time'] < time()) {
    // Wenn die Zeit abgelaufen ist, zerstöre die Session und leite zur Anmeldeseite weiter
    session_unset();
    session_destroy();
    header("Refresh:0");
    exit();
}

// IP-Adresse und User-Agent in log_acc.txt speichern
$ipAddress = get_client_ip();
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$currentDateTime = get_current_datetime();
$logMessage = "[$currentDateTime] ZUGRIFF: IP-Adresse: $ipAddress - User-Agent: $userAgent\n\n";

// Überprüfe die IP-Informationen beim Anmelden
$ip_info = check_ip_info($ipAddress);

// Wenn die IP als VPN identifiziert wird, gib eine Nachricht aus und beende das Skript
if ($ip_info['is_vpn'] === true) {
    echo "Es wurde erkannt, dass du eine VPN-Verbindung verwendest. Der Zugriff ist nicht gestattet.";
    exit();
}

// Wenn die IP als TOR identifiziert wird, gib eine Nachricht aus und beende das Skript
if ($ip_info['is_tor'] === true) {
    echo "Es wurde erkannt, dass du das TOR-Netzwerk verwendest. Der Zugriff ist nicht gestattet.";
    exit();
}

// Schreibe die Log-Nachricht nur, wenn weder VPN noch TOR erkannt wurden
file_put_contents("log_acc.txt", $logMessage, FILE_APPEND);

?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="robots" content="noindex">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accounts</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #121212;
      color: #fff;
    }
  </style>
</head>
<body>
  <div class="container mt-5">
    <?php
$currentHour = date('H');
if ($currentHour < 14 || $currentHour >= 18) {
    // Wenn nicht, gib eine Nachricht aus und beende das Skript
    echo "Die Seite ist außerhalb der Öffnungszeiten nicht verfügbar. (14:00 - 18:00)";
    exit();
}
// Prüfen, ob der Benutzer eingeloggt ist
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    // Wenn eingeloggt, prüfe den Seitentyp und zeige die entsprechenden Daten an
    if(isset($_GET['type']) && $_GET['type'] === 'crunchyroll') {
        // Crunchyroll-Daten definieren
        $crunchyrollData = array(
            array("CR5","albornozeduado246@gmail.com", "eduardoww", "Ja"),
            array("CR4","frodomoss@gmail.com", "supNatch#1", "Ja"),
            array("CR3","florentmayen@hotmail.fr", "teammpm73", "Ja"),
            array("CR2","morganmark9876@gmail.com", "karma12525", "Ja"),
            array("CR1","prodorrah@gmail.com", "3T3rn1ty*", "ERROR")
        );
        // Tabelle mit Crunchyroll-Daten anzeigen
        echo "<h1>Crunchyroll</h1>";
        echo "<table class='table table-dark table-striped'>";
        echo "<thead><tr><th scope='col'>#</th><th scope='col'>E-Mail</th><th scope='col'>Passwort</th><th scope='col'>Geprüft</th></tr></thead>";
        echo "<tbody>";
        foreach ($crunchyrollData as $data) {
            echo "<tr>";
            foreach ($data as $value) {
                if ($value === 'Ja') {
                    echo "<td class='text-success'>$value</td>";
                } elseif ($value === 'ERROR') {
                    echo "<td class='text-danger'>$value</td>";
                } else {
                    echo "<td>$value</td>";
                }
            }
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo "<center>Letzte Änderungen: 06.03.2024 14:11</center><hr>";
        // Berechne die verbleibende Zeit bis zur automatischen Abmeldung
        $remainingTime = $_SESSION['expire_time'] - time();
        echo "<center id='remainingTime'>Automatische Abmeldung in: " . gmdate("H:i:s", $remainingTime) . "</center><br>";
        echo "<center id='countdown'></center>";
    } else {
        echo "<p>Ungültiger oder fehlender type-Parameter.</p>";
    }
} else {
    // Wenn nicht eingeloggt, zeige das Passwortformular an
    ?>
    <h1>Site-Passwort erforderlich</h1>
    <form method="post">
        <div class="mb-3">
            <label for="site_password" class="form-label">Passwort</label>
            <input type="password" class="form-control" id="site_password" name="site_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Einloggen</button>
    </form>
<?php
}
?>
  </div>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Aktualisiere die verbleibende Zeit bis zur automatischen Abmeldung alle Sekunde
    setInterval(function() {
        var currentTime = Math.floor(new Date().getTime() / 1000); // Aktuelle Zeit in Sekunden seit dem Epoch
        var expireTime = <?php echo $_SESSION['expire_time']; ?>;
        var remainingTime = expireTime - currentTime;
        
        if (remainingTime > 0) {
            var hours = Math.floor(remainingTime / 3600);
            var minutes = Math.floor((remainingTime % 3600) / 60);
            var seconds = remainingTime % 60;
            // Führende Nullen hinzufügen, wenn die Zeit unter 10 ist
            hours = ('0' + hours).slice(-2);
            minutes = ('0' + minutes).slice(-2);
            seconds = ('0' + seconds).slice(-2);
            document.getElementById("remainingTime").innerHTML = "Automatische Abmeldung in: " + hours + ":" + minutes + ":" + seconds;
        } else {
            // Wenn die Zeit abgelaufen ist, zeige eine Meldung und lösche das Cookie
            document.getElementById("remainingTime").innerHTML = "Sitzung abgelaufen.";
            document.cookie = 'PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            // Weiterleitung zur Login-Seite
            location.reload();
        }
    }, 1000);
</script>
  
<script>
// Funktion, um den Countdown zu aktualisieren
function updateCountdown() {
    var currentTime = new Date();
    var targetTime = new Date(currentTime);
    // Setze das Ziel auf 18:00 Uhr heute
    targetTime.setHours(18, 0, 0, 0);
    
    // Wenn die aktuelle Zeit nach 18:00 Uhr liegt, setze das Ziel auf 18:00 Uhr morgen
    if (currentTime.getHours() >= 18) {
        targetTime.setDate(targetTime.getDate() + 1);
    }
    
    var timeDifference = targetTime - currentTime;
    
    // Konvertiere die Differenz in Stunden, Minuten und Sekunden
    var hours = Math.floor(timeDifference / (1000 * 60 * 60));
    var minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);
    
    // Aktualisiere das HTML-Element mit dem Countdown
    document.getElementById("countdown").innerHTML = "Die Seite schließt in: " + hours + " Stunden " + minutes + " Minuten " + seconds + " Sekunden <br>(14:00 - 18:00)";
    
    // Überprüfe, ob es 18:00 Uhr ist und lade die Seite neu
    if (currentTime.getHours() == 18 && currentTime.getMinutes() == 0 && currentTime.getSeconds() == 0) {
        location.reload();
    }
    
    // Aktualisiere alle 1 Sekunde
    setTimeout(updateCountdown, 1000);
}

// Starte die Aktualisierung des Countdowns
updateCountdown();
</script>


</body>
</html>
