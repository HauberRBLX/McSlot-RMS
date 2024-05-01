<?php
session_start();
require 'vendor/autoload.php'; // Pfad zur autoload.php von PHPMailer
include 'db_connection.php';

include 'settings/head.php';
include 'settings/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Konfiguration für den SMTP-Mailserver (ersetze die Platzhalter entsprechend)
$smtp_host = 'mail.auroa.cloud';
$smtp_username = 'noreply@mcslot.net';
$smtp_password = '?,45,AsToNY';
$smtp_port = 587; // Beispielport (kann je nach deinem Mailserver variieren)


// Konfiguration für die Website (ersetze die Platzhalter entsprechend)
$website_name = 'McSlot';
$website_url = 'https://www.mcslot.net';

function sendEmail($to, $subject, $body) {
    global $smtp_host, $smtp_username, $smtp_password, $smtp_port, $website_name;
    
    $mail = new PHPMailer(true);
    try {
        // Einstellungen für den SMTP-Server
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = 'tls'; // Verschlüsselungstyp (tls oder ssl)
        $mail->Port = $smtp_port;

        $mail->CharSet = 'UTF-8'; // Setze Zeichensatz auf UTF-8
        $mail->Subject = mb_encode_mimeheader($subject, 'UTF-8', 'B'); // Kodiere den Betreff mit mb_encode_mimeheader

        $mail->setFrom($smtp_username, $website_name);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Body = $body;

        // E-Mail senden
        $mail->send();
		

		
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Überprüfe, ob ein Zurücksetzungs-ID-Parameter vorhanden ist
if (isset($_GET['key'])) {
    $reset_key = $_GET['key'];

    // Überprüfe, ob die ID in der Datenbank existiert
    $query = $conn->prepare("SELECT email FROM password_reset WHERE verify_key = ?");
    $query->bind_param("s", $reset_key);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        // ID ist gültig, zeige das Passwortänderungsformular an
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password === $confirm_password) {
                // Passwort bestätigt, aktualisiere das Passwort in der Datenbank
            $query_username = $conn->prepare("SELECT name FROM benutzer WHERE email = ?");
            $query_username->bind_param("s", $email);
            $query_username->execute();
            $result_username = $query_username->get_result();
            $row_username = $result_username->fetch_assoc();
            $username = $row_username['name'];
                $email = $result->fetch_assoc()['email'];
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // Passwort hashen

                $update_query = $conn->prepare("UPDATE benutzer SET password = ? WHERE email = ?");
                $update_query->bind_param("ss", $hashed_password, $email);
                $update_query->execute();

                // Lösche den Eintrag aus der password_reset-Tabelle
                $delete_query = $conn->prepare("DELETE FROM password_reset WHERE verify_key = ?");
                $delete_query->bind_param("s", $reset_key);
                $delete_query->execute();

                // Sende Erfolgsmeldung per E-Mail
                $success_email_template = file_get_contents('settings/mail/password_reset_success_html.html');
                $success_email_template = str_replace('{name}', $username, $success_email_template);
                $success_email_template = str_replace('{email}', $email, $success_email_template);
                sendEmail($email, 'Passwort erfolgreich zurückgesetzt', $success_email_template);
                $query = $conn->prepare("INSERT INTO email_delivered (email, status, type) VALUES (?, 'delivered', 'Forget Password E-Mail success')");
                $query->bind_param("s", $email);
                $query->execute();
                // Weiterleitung zur Erfolgsmeldung oder Anmeldeseite
                $_SESSION['success_message'] = "Dein Passwort wurde nun zurückgesetzt";
                header("Location: " . ($phpenable === 'true' ? $siteurl . $dash_url . '.php' : $siteurl . $dash_url));
                exit();
            } else {
                // Passwort und Bestätigung stimmen nicht überein, zeige eine Fehlermeldung
                $error_message = "Die eingegebenen Passwörter stimmen nicht überein.";
            }
        }
		?>
<!-- HTML für das Passwortänderungsformular -->

<title>
    Passwort zurücksetzen — McSlot</title>
<body>
    <div class="container" style="max-width: 400px;">
        <br><br><br>
        <div style="text-align: center;">
            <img src="<?= $logourl_dark ?>" alt="McSlot Banner" style="max-width: 100%; height: auto;">
        </div>
        <h1 class="text-center mt-5">
            Passwort zurücksetzen
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


    <form action="" method="post">
		<div class="mb-3">
		<label for="new_password" class="form-label">
		Neues Passwort:</label>
		<div class="input-group">
		<span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-user"></i> </span>
		<input type="password" name="new_password" id="new_password" class="form-control" aria-describedby="basic-addon1" required="">
		</div>
		</div>
		<div class="mb-3">
		<label for="confirm_password" class="form-label">
		Neues Passwort:</label>
		<div class="input-group">
		<span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-user"></i> </span>
		<input type="password" name="confirm_password" id="confirm_password" class="form-control" aria-describedby="basic-addon1" required="">
		</div>
		</div>
        <center><button type="submit" class="btn btn-primary" onclick="if (!window.__cfRLUnblockHandlers) return false; showLoader(this)" id="submitButton">
<span id="buttonContent">Neues Passwort setzen</span>
<span id="loader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
			</button></center>
    </form>
</body>
</html>
	
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
</div>
<?php include 'settings/footer.php';
				?>
<?php
} else {
    // ID ist ungültig, zeige eine Fehlermeldung
    header("Location: " . ($phpenable === 'true' ? $siteurl . $dash_url . '.php' : $siteurl . $dash_url));
}
} else {
    // Kein key-Parameter im URL, zeige das Zurücksetzungsformular für die E-Mail-Eingabe an
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Überprüfe, ob die E-Mail im POST vorhanden ist
        if (isset($_POST['login'])) {
            sleep("1");
            $email = $_POST['login'];

            // Überprüfe, ob die E-Mail in der Datenbank existiert
            $query = $conn->prepare("SELECT id FROM benutzer WHERE email = ?");
            $query->bind_param("s", $email);
            $query->execute();
            $result = $query->get_result();

            $query_username = $conn->prepare("SELECT name FROM benutzer WHERE email = ?");
            $query_username->bind_param("s", $email);
            $query_username->execute();
            $result_username = $query_username->get_result();
            $row_username = $result_username->fetch_assoc();
            $username_2 = $row_username['name'];

            if ($result->num_rows > 0) {
                // E-Mail existiert, überprüfe, ob eine E-Mail in den letzten 10 Minuten gesendet wurde
                $ten_minutes_ago = date('Y-m-d H:i:s', strtotime('-10 minutes'));
                $check_query = $conn->prepare("SELECT id FROM password_reset WHERE email = ? AND requested > ?");
                $check_query->bind_param("ss", $email, $ten_minutes_ago);
                $check_query->execute();
                $check_result = $check_query->get_result();

                if ($check_result->num_rows == 0) {
                    // Es wurde in den letzten 10 Minuten keine E-Mail gesendet, generiere einen eindeutigen Schlüssel und speichere ihn in der Datenbank
                    $reset_key = bin2hex(random_bytes(16)); // Beispiel für einen eindeutigen Schlüssel (32 Zeichen)

                    $insert_query = $conn->prepare("INSERT INTO password_reset (verify_key, email, requested) VALUES (?, ?, NOW())");
                    $insert_query->bind_param("ss", $reset_key, $email);
                    $insert_query->execute();

                    // Sende eine E-Mail mit dem Zurücksetzungslink
                    $reset_link = "$website_url/reset?key=$reset_key";

                    $email_template = file_get_contents('settings/mail/password_reset_html.html');

                    // Ersetze Platzhalter im HTML-Template durch die entsprechenden Werte
                    $email_template = str_replace('{reset_link}', $reset_link, $email_template);
                    $email_template = str_replace('{email}', $email, $email_template);
                    $email_template = str_replace('{name}', $username_2, $email_template);

                    // Verwende PHPMailer zum Senden der E-Mail
                    $mail = new PHPMailer(true);
                    try {
                        // Einstellungen für den SMTP-Server
                        $mail->isSMTP();
                        $mail->Host = $smtp_host;
                        $mail->SMTPAuth = true;
                        $mail->Username = $smtp_username;
                        $mail->Password = $smtp_password;
                        $mail->SMTPSecure = 'tls'; // Verschlüsselungstyp (tls oder ssl)
                        $mail->Port = $smtp_port;

                        $mail->CharSet = 'UTF-8'; // Setze Zeichensatz auf UTF-8
                        $mail->Subject = 'Passwort zurücksetzen - ' . $website_name;
                        $mail->Subject = mb_encode_mimeheader($mail->Subject, 'UTF-8', 'B'); // Kodiere den Betreff mit mb_encode_mimeheader

                        $mail->setFrom($smtp_username, $website_name);
                        $mail->addAddress($email);
                        $mail->isHTML(true);
                        $mail->Body = $email_template;
                        $mail->Priority = 1;
                        // E-Mail senden
                        if ($mail->send()) {
                            // Wenn die E-Mail erfolgreich gesendet wurde, aktualisiere den Status und Typ in der Datenbank
                            $query = $conn->prepare("INSERT INTO email_delivered (email, status, type) VALUES (?, 'delivered', 'Forget Password E-Mail')");
                            $query->bind_param("s", $email);
                            $query->execute();

                            $_SESSION['info_message'] = "Eine E-Mail mit Anweisungen zum Zurücksetzen des Passworts wurde an Deine E-Mail-Adresse gesendet.";
                        } else {
                            // Wenn ein Fehler beim Senden der E-Mail auftritt, aktualisiere den Status und Typ in der Datenbank
                            $query = $conn->prepare("INSERT INTO email_delivered (email, status, type) VALUES (?, 'error', 'Forget Password E-Mail')");
                            $query->bind_param("s", $email);
                            $query->execute();

                            $error_message = "Die E-Mail konnte nicht gesendet werden. Bitte versuche es später erneut.";
                        }
                    } catch (Exception $e) {
                        $error_message = "Die E-Mail konnte nicht gesendet werden. Bitte versuche es später erneut.";
                    }
                } else {
                    // Es wurde bereits in den letzten 10 Minuten eine E-Mail gesendet
                    $error_message = "Es wurde bereits eine E-Mail zum Zurücksetzen des Passworts in den letzten 10 Minuten gesendet. Bitte warte etwas, bevor du eine weitere E-Mail anforderst.";
                }
            } else {
                $error_message = "Die eingegebene E-Mail-Adresse ist nicht registriert.";
            }
        }
    }
?>



<title>
    Passwort zurücksetzen — McSlot</title>
<body>
    <div class="container" style="max-width: 400px;">
        <br><br><br>
        <div style="text-align: center;">
            <img src="<?= $logourl_dark ?>" alt="McSlot Banner" style="max-width: 100%; height: auto;">
        </div>
        <h1 class="text-center mt-5">
            Passwort zurücksetzen
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


    <form action="" method="post">
        <div>
			
            <div class="mb-3">
                <label for="reset" class="form-label">E-Mail Adresse</label>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-at"></i> </span>
                    <input type="email" name="login" id="login" class="form-control" aria-describedby="basic-addon1"
                        required>
                </div>
            </div>
			
			
        </div><br>
        <center><button type="submit" class="btn btn-primary" onclick="showLoader(this)" id="submitButton">
    <span id="buttonContent">E-Mail senden</span>
    <span id="loader" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
			</button></center>
		<br>
        <p class="text-success text-center"><i class="fas fa-lock"></i>
            <?= str_replace('{websiteName}', $name, $translations['login']['data_security']) ?>
        </p>
		
		
    </form>
</body>
</html>
	
	<script>
function showLoader(button) {
    var buttonContent = button.querySelector('#buttonContent');
    var loader = button.querySelector('#loader');

    buttonContent.style.display = 'none';
    loader.style.display = 'inline-block';
    setTimeout(function() {
        loader.style.display = 'none';
        buttonContent.style.display = 'inline';
    }, 5000);
}
</script>
</div>
<?php include 'settings/footer.php'; ?>
<?php
}
?>
