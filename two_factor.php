<?php
require 'vendor/autoload.php';

use OTPHP\TOTP;

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

include 'db_connection.php';
include 'settings/config.php';
include 'settings/head.php';

// Funktion zur Anzeige von Bootstrap-Alerts
function displayAlert($message, $type = 'info') {
    echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
              ' . $message . '
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}

// Alert-Nachrichten erstellen
$alertMessage = '';
$alertType = 'info';

if (isset($_GET['key']) && !empty($_GET['key'])) {
    $key = $_GET['key'];

    $sql = "SELECT user FROM two_factor WHERE login_key = '$key'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userId = $row["user"];

        $sql = "SELECT totp_secret FROM benutzer WHERE id = $userId";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $secret = $row["totp_secret"];

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Validate the OTP code
                $otpCode = '';
                for ($i = 1; $i <= 6; $i++) {
                    if (isset($_POST['otp' . $i]) && ctype_digit($_POST['otp' . $i])) {
                        $otpCode .= $_POST['otp' . $i];
                    } else {
                        $alertMessage = 'Ungültiger OTP-Code.';
                        $alertType = 'danger';
                        break;
                    }
                }

                if (strlen($otpCode) === 6) {
                    // Proceed with OTP verification
                    $otp = TOTP::createFromSecret($secret);
                    $check = $otp->verify($otpCode);

                    if ($check) {
                        // OTP valid
                        $ip_address = get_client_ip();
                        $update_ip_sql = "UPDATE benutzer SET letzte_ip = ? WHERE id = ?";
                        $stmt = $conn->prepare($update_ip_sql);
                        $stmt->bind_param("si", $ip_address, $userId);
                        $stmt->execute();

                        $login_status = 'erfolgreich';
                        $sql = "INSERT INTO login_history (user_id, login_time, ip_address, login_status) VALUES (?, NOW(), ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("iss", $userId, $ip_address, $login_status);
                        $stmt->execute();

                        $deleteSql = "DELETE FROM two_factor WHERE login_key = '$key'";
                        if ($conn->query($deleteSql) === TRUE) {
                            $_SESSION['user_id'] = $userId;
                            $expiry = time() + 30 * 24 * 3600; // 30 Tage Ablaufzeit
                            setcookie("user_name", $row['name'], $expiry, "/");
                            setcookie("PHPSESSID", session_id(), $expiry, "/"); // Verlängere die Lebensdauer des Session-Cookies
                            $_SESSION['success_message'] = "Willkommen zurück, " . $row['name'] . ".";
                            sleep(1);
                            header("Location: " . ($phpenable === 'true' ? $siteurl . $dash_url . '.php' : $siteurl . $dash_url));
                            exit(); // Beende das Skript nach der Weiterleitung
                        } else {
                            // Fehler beim Löschen des Eintrags
                            $alertMessage = 'Fehler beim Löschen des Eintrags: ' . $conn->error;
                            $alertType = 'danger';
                        }
                    } else {
                        // OTP invalid
                        $alertMessage = 'Ungültiger OTP-Code.';
                        $alertType = 'danger';
                    }
                } else {
                    // Invalid OTP length
                    $alertMessage = 'Der OTP-Code muss aus 6 Zahlen bestehen.';
                    $alertType = 'danger';
                }
            }
        } else {
            // Secret not found for the user
            $alertMessage = 'Benutzer-Schlüssel nicht gefunden.';
            $alertType = 'danger';
        }
    } else {
        // Key not found in two_factor table
        header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
        exit();
    }
} else {
    // Key parameter not provided in the URL
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit();
}

// Abmeldelogik
if (isset($_POST['logout'])) {
    $deleteSql = "DELETE FROM two_factor WHERE login_key = '$key'";
    if ($conn->query($deleteSql) === TRUE) {
        header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
        exit();
    } else {
        $alertMessage = 'Fehler beim Löschen des Eintrags: ' . $conn->error;
        $alertType = 'danger';
    }
}

// Funktion zum Schließen der Datenbankverbindung
function closeConnection($conn) {
    $conn->close();
}
?>

<title>2FA &mdash; <?= $name ?></title>
<br><br>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card mb-n7 position-relative zindex-100">
            <h1 class="text-center mt-5">2FA Code Überprüfung</h1>
            <div class="card-body px-5">
				
                <div class="container">
					
                    <div class="row justify-content-center">
						
                        <div class="col-md-6 text-center">
							<?php
	if (!empty($alertMessage)) {
    echo '<div class="alert alert-' . $alertType . ' alert-dismissible fade show" role="alert">
              ' . $alertMessage . '
          </div>';
}
	?>
                            <p class="text opacity-8">Bitte gebe deinen OTP-Code ein:</p>
                            <br>
<form method="post" id="otpForm">
    <div class="form-row justify-content-center" id="otpContainer">
        <?php
        $numberOfDigits = 6;
        for ($i = 1; $i <= $numberOfDigits; $i++) {
            echo '
                <div class="form-group col-auto">
                    <input type="number" class="form-control otp-input" maxlength="1" id="digit' . $i . '" name="otp' . $i . '" autocomplete="off" required>
                </div>
            ';
        }
        ?>
		<style>
			input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
		</style>
    </div>
    <br>
    <button type="submit" class="btn btn-primary" onclick="showLoader(this)" id="submitButton">
    <span id="buttonContent">Überprüfen</span>
    <span id="loader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
</button>
							</form>
<form>
    <button type="submit" value="Abmelden" class="btn btn-danger" onclick="showLoader(this)" id="submitButton">
    <span id="buttonContent">Abmelden</span>
    <span id="loader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
</button>
</form>

                            <br><br>
							<p class="text opacity-8">ID: <?= $key ?></p>
                            
							
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br><br><br><br><br><br>

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

<script>
	$(document).ready(function () {
    // Eingabe in die OTP-Felder steuern
    $('.otp-input').on('input', function () {
        var $this = $(this);
        var maxLength = parseInt($this.attr('maxlength'));
        var sanitizedValue = $this.val().replace(/\D/g, ''); // Nur Zahlen zulassen

        if (sanitizedValue.length > maxLength) {
            sanitizedValue = sanitizedValue.slice(0, maxLength); // Maximal zulässige Länge begrenzen
        }

        $this.val(sanitizedValue); // Gesäuberten Wert in das Feld setzen

        if (sanitizedValue.length === maxLength) {
            // Finde das nächste Eingabefeld
            var $nextInput = $this.closest('.form-group').next().find('.otp-input');

            if ($nextInput.length > 0) {
                $nextInput.focus(); // Fokus auf das nächste Eingabefeld setzen
            } else {
                $this.blur(); // Fokus aus dem aktuellen Feld entfernen
            }
        }
    });

    // Eingefügten Text aus der Zwischenablage verarbeiten
    $('.otp-input').on('paste', function (event) {
        var clipboardData = (event.originalEvent || event).clipboardData;
        var pastedText = clipboardData.getData('text');
        var sanitizedText = pastedText.replace(/\D/g, ''); // Nur Zahlen zulassen

        if (/^\d{6}$/.test(sanitizedText)) {
            // Falls der eingefügte Text 6 Zahlen enthält, fülle die Felder entsprechend
            var digits = sanitizedText.split('');
            $('.otp-input').each(function (index) {
                $(this).val(digits[index]);
            });
        }

        event.preventDefault(); // Standardverhalten des Einfügens unterdrücken
    });

    // Navigation beim Klick oder Touch auf ein OTP-Feld
    $('.otp-input').on('touchstart', function () {
        var $this = $(this);
        var maxLength = parseInt($this.attr('maxlength'));

        // Falls das Feld leer ist, entferne den Inhalt aller folgenden Felder
        if ($this.val() === '') {
            $('.otp-input').slice($('.otp-input').index($this), 6).val('');
        }
    });

    $('.otp-input').on('touchend', function () {
        var $this = $(this);
        var maxLength = parseInt($this.attr('maxlength'));
        var currentIndex = $('.otp-input').index($this);

        // Fokus auf das aktuelle Feld setzen
        $this.focus();
    });
});
</script>

    <style>
        .otp-input {
            width: 60px;
            height: 60px;
            font-size: 20px;
            text-align: center;
            padding: 10px;
        }
    </style>

<?php 
include 'settings/footer.php'; 
closeConnection($conn); // Schließe die Datenbankverbindung
?>
