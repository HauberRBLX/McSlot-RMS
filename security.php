<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
    exit;
}

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'];
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if ($user['gesperrt'] == 1) {
    // Benutzer ausloggen
    session_destroy();
    header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
    exit;
}


if (isset($_POST['savePassword'])) {
    $name = $_POST['name'];
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (password_verify($oldPassword, $user['password'])) {
        if ($newPassword == $confirmPassword) {
            $newPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE benutzer SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $newPassword, $_SESSION['user_id']);
            $stmt->execute();
            $success_message = "Das Passwort wurde erfolgreich geändert.";
        } else {
            $error_message = "Das Passwort stimmt nicht überein. Bitte versuche es erneut.";
        }
    } else {
        $error_message = "Das alte Passwort ist falsch. Bitte versuche es erneut.";
    }
}


if (isset($_POST['disable2fa'])) {
    // Setze das 2FA-Geheimnis auf NULL
    $sql = "UPDATE benutzer SET totp_secret = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $error_message = "Time-Based-Authentifizierung wurde erfolgreich deaktiviert.";
}
?>

<?php
include 'settings/config.php';
include 'settings/head.php';
include 'settings/header.php';

?>
<title>Sicherheit &mdash;
    <?= $name ?>
</title>
<section class="pt-5 bg-section-secondary">
    <div class=container>
        <div class="row justify-content-center">
            <div class=col-lg-9>
                <div class="row align-items-center">
                    <div class=col>
                        <span class=surtitle><?= ($translations['security']['surtitle']) ?></span>
                        <h1 class="h2 mb-0"><?= ($translations['security']['nav']) ?></h1>
                    </div>
                </div>
                <div class="row align-items-center mt-4">
                    <div class=col>
                        <ul class="nav nav-tabs overflow-x">
                            <li class=nav-item>
                            <a href="<?= ($phpenable === 'true' ? $settings_url . '.php' : $settings_url) ?>"
                                        class="nav-link"><?= ($translations['settings']['nav']) ?></a>
                            </li>
                            <!--<li class=nav-item >
                                        <a href=#soon class=nav-link>Einstellungen</a>
                                    </li>-->
                            <li class=nav-item>
                                <a href="<?= ($phpenable === 'true' ? $security_url . '.php' : $security_url) ?>"
                                    class="nav-link active"><?= ($translations['security']['nav']) ?></a>
                            </li>
                            <!--<li class=nav-item>
                                        <a href=#soon class=nav-link>Einladen</a>
                                    </li>-->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

               


<div class="slice slice-sm bg-section-secondary">
    <div class=container>
        <div class="row justify-content-center">
            <div class=col-lg-9>
                <div class=row>
                    <div class=col-lg-12>

						
						
                        <?php if (isset($success_message)) { ?>
                            <div class="alert alert-success" role="alert">
            <div class="alert-group-prepend">
            <span class="alert-group-icon text-">
            <i class="fa-solid fa-circle-check"></i>
            </span> <?php echo $success_message; ?>
            </div>
                        <?php } ?>

                        <?php if (isset($error_message)) { ?>
                            <div class="alert alert-danger" role="alert">
            <div class="alert-group-prepend">
            <span class="alert-group-icon text-">
            <i class="fa-solid fa-circle-exclamation"></i>
            </span> <?php echo $error_message; ?>
            </div>
                        </div>
                              
            
                        <?php } ?>
								

								

<section class="card border-0 py-1 p-md-2 p-xl-3 p-xxl-4 mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center pb-4 mt-sm-n1 mb-0 mb-lg-1 mb-xl-3">
            <h2 class="h4 mb-0">Time-Based-Authentifizierung (2FA) 
                <?php if ($user['totp_secret'] !== NULL) { ?>
                    <span class="badge badge-xs badge-success"><i class="fa-duotone fa-check"></i> Aktiviert</span>
                <?php } else { ?>
                    <span class="badge badge-xs badge-danger"><i class="fa-duotone fa-screwdriver-wrench"></i> Deaktiviert</span>
                <?php } ?>
            </h2>
        </div>
        <form method="post">
            <div class="row align-items-center g-3 g-sm-4 pb-3">
                <div class="col-md-10">
					<p>Aktiviere Time-Based-Authentifizierung auf deinem Account.</p>
                </div>
            </div>
            <div class="d-flex justify-content-end pt-3">
               
                <?php if ($user['totp_secret'] !== NULL) { ?>
        <form method="post" onsubmit="return confirm('Bist du sicher, dass du Time-Based-Authentifizierung deaktivieren möchtest?');">
            <button type="submit" name="disable2fa" class="btn btn-danger ml-2">Deaktivieren</button>
        </form>
				
               
                <?php } else { ?>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#otpmodal"> <i class="fa-duotone fa-shield-halved"></i> Aktivieren</button>
               
                <?php } ?>
                
            </div>
        </form>
    </div>
</section>

			
								
								
								 
<div class="modal fade" id="otpmodal" tabindex="-1" role="dialog" aria-labelledby="otpmodal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Time-Based-Authentifizierung (2FA) Aktivieren</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
<?php
use OTPHP\TOTP;

require 'vendor/autoload.php';

// Initialisieren Sie das TOTP-Objekt und generieren Sie das Geheimnis nur einmalig
session_start(); // Starten Sie die Sitzung, um das Geheimnis zwischen den Seitenzugriffen zu speichern
if (!isset($_SESSION['secret'])) {
    $otp = TOTP::generate();
    $_SESSION['secret'] = $otp->getSecret();
}
		  

// Wenn das Formular abgeschickt wurde, verwenden Sie das vorhandene Geheimnis, um das TOTP-Objekt zu erstellen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['otp']) && isset($_POST['enable2fa']) && $_POST['enable2fa'] === 'true') {
        $otpInput = $_POST['otp'];
        
        // Verwenden Sie das in der Sitzung gespeicherte Geheimnis, um das TOTP-Objekt zu erstellen
        $otp = TOTP::createFromSecret($_SESSION['secret']);
        
        try {
            // Überprüfen des OTP-Codes
            $check = $otp->verify($otpInput);

            // Ausgabe basierend auf dem Überprüfungsergebnis
            if ($check) {
                $success_message = "2FA wurde erfolgreich aktiviert.";
                $secretKey = $_SESSION['secret'];
                $userId = $_SESSION['user_id'];
                $statement = $conn->prepare("UPDATE benutzer SET totp_secret = ? WHERE id = ?");
                $statement->execute([$secretKey, $userId]);
				header("Location: " . ($phpenable === 'true' ? $security_url . '.php' : $security_url));
            } else {
                echo 'Der OTP-Code ist falsch';
            }
        } catch (Exception $e) {
            echo 'Fehler beim Erstellen des TOTP-Objekts: ' . $e->getMessage();
        }
    }
}


// Generiere den QR-Code
$userName = htmlspecialchars($user['name']);
$otpLabel = "McSlot - " . $userName;

// Verwenden Sie das in der Sitzung gespeicherte Geheimnis, um das TOTP-Objekt zu erstellen
$otp = TOTP::createFromSecret($_SESSION['secret']);
$otp->setLabel($otpLabel);

$grCodeUri = $otp->getQrCodeUri(
    'https://quickchart.io/qr?text=[DATA]&size=200x200',
    '[DATA]'
);

// Anzeige des QR-Codes und des Formulars
echo "<center><img src='{$grCodeUri}'></center>\n  ";
echo "<br><center>Scanne den Code mit einer beliebigen App (Google-Authenticator, Authy, etc.)</center>";

?>

<form method="post">
    <center><input type="number" class="form-control" placeholder="" id="otp" name="otp" required></center>
    <br>
    <input type="hidden" id="enable2fa" name="enable2fa" value="true">
    <center><input type="submit" value="Aktivieren" class="btn btn-primary"></center>
</form>




      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Schließen</button>
      </div>
    </div>
  </div>
</div>


								
<section class="card border-0 py-1 p-md-2 p-xl-3 p-xxl-4 mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center pb-4 mt-sm-n1 mb-0 mb-lg-1 mb-xl-3">
            <h2 class="h4 mb-0"><?= ($translations['security']['password']['title']) ?></h2>
        </div>
        <form method="post">
            <div class="row align-items-center g-3 g-sm-4 pb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="old_password" class="form-label">
                            <?= ($translations['security']['password']['old']) ?></label>
                        <input type="password" name="old_password" placeholder="<?= ($translations['security']['password']['old']) ?>"
                            class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                </div>
                              <div class="col-md-6">
                    <div class="form-group">
                        <label for="new_password" class="form-label">
                            <?= ($translations['security']['password']['new_1']) ?></label>
                        <input type="password" name="new_password" id="password"
                            placeholder="<?= ($translations['security']['password']['new_1']) ?>" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            <?= ($translations['security']['password']['new_2']) ?></label>
                        <input type="password" name="confirm_password"
                            placeholder="<?= ($translations['security']['password']['new_2']) ?>" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end pt-3">
<button class="btn btn-light" type="button" onclick="window.location.href='<?= ($phpenable === 'true' ? $reset_url . '.php' : $reset_url) ?>';"><i class="fa-duotone fa-unlock"></i> <?= ($translations['security']['forget']) ?></button>


				<button class="btn btn-primary" type="submit" name="savePassword"><i class="fa-duotone fa-floppy-disk"></i> <?= ($translations['security']['save']) ?></button>
            </div>
        </form>
    </div>
</section>

                        
                        <hr class=my-5>
                        <h5><i class="fa-duotone fa-clock-rotate-left"></i> <?= ($translations['security']['login_history']['title']) ?></h5>


                        <?php
                        $itemsPerPage = 10; // Anzahl der Einträge pro Seite
                        $page = isset($_GET['page']) ? $_GET['page'] : 1;

                        $sql = "SELECT COUNT(*) FROM login_history WHERE user_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $_SESSION['user_id']);
                        $stmt->execute();
                        $totalRecords = $stmt->get_result()->fetch_row()[0];

                        $totalPages = ceil($totalRecords / $itemsPerPage);

                        if (isset($_POST['deleteAll'])) {
                            $deleteSql = "DELETE FROM login_history WHERE user_id = ?";
                            $deleteStmt = $conn->prepare($deleteSql);
                            $deleteStmt->bind_param("i", $_SESSION['user_id']);
                            $deleteStmt->execute();
                            sleep("1");
                            header("Location: " . ($phpenable === 'true' ? $security_url . '.php' : $security_url));
                        }

                        // Überprüfen, ob die angegebene Seite gültig ist
                        if ($page < 1) {
                            $page = 1;
                        } elseif ($page > $totalPages) {
                            $page = $totalPages;
                        }

                        $offset = ($page - 1) * $itemsPerPage;
                        $sql = "SELECT * FROM login_history WHERE user_id = ? ORDER BY login_time DESC LIMIT ?, ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("iii", $_SESSION['user_id'], $offset, $itemsPerPage);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        // Tabelle erstellen, um den Login-Verlauf anzuzeigen
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-cards align-items-center">';
                        echo '<thead><tr><th scope="col">' . ($translations['security']['login_history']['table']['text_1']) . '</th><th scope="col">' . ($translations['security']['login_history']['table']['text_2']) . '</th><th scope="col">' . ($translations['security']['login_history']['table']['text_3']) . '</th></tr></thead>';
                        echo '<tbody class="list">';

                        if ($totalRecords == 0) {
                            echo "<th>";
                            echo '<td colspan="3">' . ($translations['security']['login_history']['table']['empty']) . '</td>';
                            echo "</th>";
                        } else {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<th>" . date('d.m.Y H:i', strtotime($row['login_time'])) . "</th>";
                                echo "<td>" . $row['ip_address'] . "</td>";
                                if ($row['login_status'] == 'erfolgreich') {
                                    echo '<td><span class="badge badge-success">' . ($translations['security']['login_history']['table']['success']) . '</span></td>';
                                } elseif ($row['login_status'] == 'abgelehnt') {
                                    echo '<td><span class="badge badge-danger">' . ($translations['security']['login_history']['table']['fail']) . '</span></td>';
                                } else {
                                    echo '<td>' . $row['login_status'] . '</td>';
                                }
                                echo "</tr>";
                            }
                        }

                        echo '</tbody></table>';
                        echo '</div>';

                        // Pagination-Links anzeigen
                        echo '<nav aria-label="Page navigation example"><ul class="pagination">';
                        if ($page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
                        }
                        for ($i = 1; $i <= $totalPages; $i++) {
                            if ($i == $page) {
                                echo '<li class="page-item active"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                            } else {
                                echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                            }
                        }
                        if ($page < $totalPages) {
                            echo '<li class="page-item"><a class="page-link" href "?page=' . ($page + 1) . '" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
                        }
                        echo '</ul></nav>';
                        ?>
                    </div>
                </div><br>
                <form method="post">
                    <?php if ($totalRecords > 0) { ?>
                        <button type="submit" name="deleteAll" class="btn btn-sm btn-danger btn-icon-label">
                            <span class="btn-inner--icon">
                                <i class="fa-solid fa-trash-can"></i>
                            </span>
                            <span class="btn-inner--text"><?= ($translations['security']['login_history']['delete']) ?></span>
                        </button>


                    <?php } ?>
                </form>


            </div>

        </div>


    </div>

</div>

<?php
include 'settings/footer.php';
?>