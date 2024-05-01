<!-- settings.php -->
<html>
<meta name='og:title' content='DLSystem • Admin'>
<meta name="description" content="Settings | DLSystem">
<meta name="keywords" content="DLSystem">
<meta name="author" content="PvPMaster0001">
<meta name='copyright' content='DLSystem'>

<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'] . " AND (role = 'Admin' OR role = 'Owner')";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "Du hast keine Berechtigung für diese Aktion.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . '' : $siteurl . $admin_directory));
    exit;
}

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'];
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if ($user['gesperrt'] == 1) {
    // Benutzer ist gesperrt; ausloggen und zur Anmeldeseite umleiten
    session_destroy();
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

include '../settings/config.php';
include '../settings/head_admin.php';
include '../settings/header_admin.php';

// Einstellungen für Registrierung und Login aus der Datenbank abrufen
$settingsSql = "SELECT setting_name, setting_value FROM settings";
$settingsResult = $conn->query($settingsSql);
$settings = [];

while ($row = $settingsResult->fetch_assoc()) {
    $settings[$row['setting_name']] = $row['setting_value'];
}

// Überprüfen, ob Registrierung und Login aktiviert sind
$registrierungAktiviert = $settings['register'] == 1;
$loginAktiviert = $settings['login'] == 1;
$MaintenanceAktiviert = $settings['maintenance'] == 1;
$LockdownAktiviert = $settings['lockdown'] == 1;

?>

<title>Einstellungen &mdash; Admin</title>

<body>
    <section class="pt-5 bg-section-secondary">
        <div class=container>
            <div class="row justify-content-center">
                <div class=col-lg-9>
                    <div class="row align-items-center">
                        <div class=col>
                            <span class=surtitle>Admin</span>
                            <h1 class="h2 mb-0">Einstellungen</h1>
                        </div>
                    </div>
                    <div class="row align-items-center mt-4">
                        <div class=col>
<?php 
                      include '../settings/navbar_admin.php';
                      ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="slice slice-sm bg-section-secondary">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                <?php if (isset($_SESSION['success_message'])) { ?>
               <div class="alert alert-success" role="alert">
               <div class="alert-group-prepend">
               <span class="alert-group-icon text-">
               <i class="fa-solid fa-circle-check"></i>
               </span>
               <?php echo $_SESSION['success_message']; ?>
            </div>
               </div>
               
               <?php unset($_SESSION['success_message']); // Lösche die Session-Variablen nach der Anzeige ?>
            <?php } ?>

            <?php if (isset($_SESSION['error_message'])) { ?>
               <div class="alert alert-danger" role="alert">
               <div class="alert-group-prepend">
               <span class="alert-group-icon text-">
               <i class="fa-solid fa-circle-exclamation"></i>
               </span>
               <?php echo $_SESSION['error_message']; ?>
            </div>
               </div>


               <?php unset($_SESSION['error_message']); // Lösche die Session-Variablen nach der Anzeige ?>
            <?php } ?>
                    <div class="col-lg-12">
                        <center>
                            <h3>Einstellungen</h3>
                        </center>
                        <h5>Anmeldungen und Registrierung</h5>
                    </div>
                    <form method="post" action="actions/update_settings.php">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="customSwitch1" name="register" <?php echo $registrierungAktiviert ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="customSwitch1">Registrierung: Aus/An</label>
                        </div>

                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="customSwitch2" name="login" <?php echo $loginAktiviert ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="customSwitch2">Login: Aus/An</label>
                        </div>
                        <hr>
                        <div class="col-lg-12">
                            <h5>Website Einstellungen</h5>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="customSwitch4" name="maintenance"
                                <?php echo $MaintenanceAktiviert ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="customSwitch4">Wartungsarbeiten: Aus/An</label>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="customSwitch5" name="lockdown"
                                <?php echo $LockdownAktiviert ? 'checked' : ''; ?>>
								<label class="custom-control-label" for="customSwitch5">Lockdown: Aus/An <span class="text-danger"><i class="fa-duotone fa-circle-exclamation"></i> Nur im Notfall!</span></label>
                        </div>

                        <div class="mt-3 text-center">
                            <button type="submit" class="btn btn-primary">Einstellungen Speichern</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <?php
    include '../settings/footer.php';
    ?>
</body>

</html>