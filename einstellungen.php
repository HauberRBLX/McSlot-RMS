<!-- settings.php -->

<meta name='og:title' content='Einkaufsliste • ELSystem'>
<meta name="description" content="Profil | ELSystem">
<meta name="keywords" content="Einkaufsliste, ELSystem">
<meta name="author" content="PvPMaster0001">
<meta name='copyright' content='ELSystem'>


<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: $login_url");
    exit;
}

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'];
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if ($user['gesperrt'] == 1) {
    session_destroy();
    header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
    exit;
}


if (isset($_GET['delete_confirm'])) {
    // Überprüfe, ob der Eintrag bereits in der Warteschlange vorhanden ist
    $checkSql = "SELECT * FROM queue WHERE user_id = " . $_SESSION['user_id'];
    $result = $conn->query($checkSql);

    if ($result->num_rows > 0) {
        // Eintrag existiert bereits, zeige Fehlermeldung
        $_SESSION['error_message'] = "Eintrag existiert bereits in der Warteschlange.";
    } else {
        // Füge neuen Eintrag in die Warteschlange ein
        $insertSql = "INSERT INTO queue (user_id) VALUES (" . $_SESSION['user_id'] . ")";
        $conn->query($insertSql);

        // Markiere Benutzer als gelöscht
        $updateSql = "UPDATE benutzer SET deleted = 1 WHERE id = " . $_SESSION['user_id'];
        $conn->query($updateSql);

        // Warte 1 Sekunde
        sleep(1);
        // Weiterleitung zur Login-Seite
        header("Location: " . ($phpenable === 'true' ? $siteurl . $reactive_url . '.php' : $siteurl . $reactive_url));
    }
}

$settingsSql = "SELECT effects FROM settings_users WHERE user_id = " . $_SESSION['user_id'];
$settingsResult = $conn->query($settingsSql);

if ($settingsResult->num_rows > 0) {
    $row = $settingsResult->fetch_assoc();
    $effectsaktiv = $row['effects'] == 1;
}

?>
<?php
include 'settings/config.php';
include 'settings/head.php';
include 'settings/header.php';

?>
<title>Einstellungen &mdash;
    <?= $name ?>
</title>

<body>
    <section class="pt-5 bg-section-secondary">
        <div class=container>
            <div class="row justify-content-center">
                <div class=col-lg-9>
                    <div class="row align-items-center">
                        <div class=col>
                            <span class=surtitle><?= ($translations['settings']['surtitle']) ?></span>
                            <h1 class="h2 mb-0"><?= ($translations['settings']['title']) ?></h1>
                        </div>
                    </div>
                    <div class="row align-items-center mt-4">
                        <div class=col>
                            <ul class="nav nav-tabs overflow-x">
                                <li class=nav-item>
                                    <a href="<?= ($phpenable === 'true' ? $settings_url . '.php' : $settings_url) ?>"
                                        class="nav-link active"><?= ($translations['settings']['nav']) ?></a>
                                </li>
                                <!--<li class=nav-item >
                                        <a href=#soon class=nav-link>Einstellungen</a>
                                    </li>-->
                                <li class=nav-item>
                                    <a href="<?= ($phpenable === 'true' ? $security_url . '.php' : $security_url) ?>"
                                        class="nav-link"><?= ($translations['security']['nav']) ?></a>
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
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">

                    <div class="col-lg-12">
                    </div>
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
<?php
$sql = "SELECT night_mode FROM settings_users WHERE user_id = " . $_SESSION['user_id']; // Hier müssen Sie Ihre eigene Bedingung setzen, um den richtigen Benutzer zu identifizieren

$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $night_mode = $row['night_mode'];
    $nightModeOnChecked = '';
    $nightModeOffChecked = '';
    $nightModeAutoChecked = '';
    $nightModeDeviceChecked = '';

    switch ($night_mode) {
        case 'on':
            $nightModeOnChecked = 'checked';
            break;
        case 'off':
            $nightModeOffChecked = 'checked';
            break;
        case 'auto':
            $nightModeAutoChecked = 'checked';
            break;
        case 'device':
            $nightModeDeviceChecked = 'checked';
            break;
        default:
            break;
    }
} else {
}
?>

<section class="card border-0 py-1 p-md-2 p-xl-3 p-xxl-4 mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center pb-4 mt-sm-n1 mb-0 mb-lg-1 mb-xl-3">
            <h2 class="h4 mb-0"><?= ($translations['settings']['night_mode']['title']) ?> <span class="badge badge-xs badge-light"><i class="fa-duotone fa-sparkles"></i> Neu</span></h2>
        </div>
        <form action="actions/update_settings.php" method="post">
            <input type="hidden" name="setting_identifier" value="night_mode">
            <div class="row align-items-center g-3 g-sm-4 pb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="custom-control custom-radio">
                            <input type="radio" id="nightMode-on" name="nightMode" value="on" class="custom-control-input" <?php echo $nightModeOnChecked; ?>>
                            <label class="custom-control-label" for="nightMode-on"><?= ($translations['settings']['night_mode']['on']) ?></label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="nightMode-off" name="nightMode" value="off" class="custom-control-input" <?php echo $nightModeOffChecked; ?>>
                            <label class="custom-control-label" for="nightMode-off"><?= ($translations['settings']['night_mode']['off']) ?></label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="nightMode-auto" name="nightMode" value="auto" class="custom-control-input" <?php echo $nightModeAutoChecked; ?>>
                            <label class="custom-control-label" for="nightMode-auto"><?= ($translations['settings']['night_mode']['auto']) ?></label>
                        </div>
                        <!--<div class="custom-control custom-radio">
                            <input type="radio" id="nightMode-device" name="nightMode" value="device" class="custom-control-input" <?php echo $nightModeAutoChecked; ?>>
                            <label class="custom-control-label" for="nightMode-device">Systemstandard</label>
                        </div>-->
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end pt-3">
                <button class="btn btn-primary" type="submit"><i class="fa-duotone fa-floppy-disk"></i> <?= ($translations['settings']['save']) ?></button>
            </div>
        </form>
    </div>
</section>



<section class="card border-0 py-1 p-md-2 p-xl-3 p-xxl-4 mb-4">
    <div class="card-body">
        <?php if ($user['email'] === NULL): ?>
            <div class="d-flex align-items-center pb-4 mt-sm-n1 mb-0 mb-lg-1 mb-xl-3">
                <h2 class="h4 mb-0"><?= ($translations['settings']['email']['add']) ?> <span class="badge badge-xs badge-danger"><i class="fa-regular fa-circle-exclamation"></i></span></h2>
            </div>
        <?php else: ?>
            <div class="d-flex align-items-center pb-4 mt-sm-n1 mb-0 mb-lg-1 mb-xl-3">
                <h2 class="h4 mb-0"><?= ($translations['settings']['email']['change']) ?></h2>
            </div>
        <?php endif; ?>
        <div class="row align-items-center g-3 g-sm-4 pb-3">
            <form action="actions/update_settings.php" method="post">
                <div class="col-sm-20">
                    <label class="form-label" for="name"><?= ($translations['settings']['email']['field']) ?></label>
                    <input class="form-control" type="email" required="" name="new_email" id="name">


                </div>
        </div>

        <?php if ($user['email'] === NULL): ?>
            
			<p class='text-danger'><?= ($translations['settings']['email']['text']['add']) ?></p>
        <?php else: ?>
			<?php
$real_email = $user['email'];
$email_parts = explode('@', $real_email);
$hidden_username = substr($email_parts[0], 0, 2) . '****';
$domain_part = $email_parts[1];
$user_email_hidden = $hidden_username . '@' . $domain_part;
			?>
			<p class='text-muted'><?= str_replace('{currentmail}', '<strong>' . $user_email_hidden . '</strong>', $translations['settings']['email']['currentmail']) ?><strong></strong></p>
            <p class='text-muted'><?= ($translations['settings']['email']['text']) ?></p>
        
			<?php endif; ?>

        <div class="d-flex justify-content-end pt-3">
            <input type="hidden" name="setting_identifier" value="email_change">
			
            <button class='btn btn-primary ms-3' type='submit'><i class='fa-duotone fa-floppy-disk'></i> <?= ($translations['settings']['save']) ?></button>
			
        </div>
        </form>
    </div>
</section>

                    <section class="card border-0 py-1 p-md-2 p-xl-3 p-xxl-4 mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center pb-4 mt-sm-n1 mb-0 mb-lg-1 mb-xl-3">
                                <h2 class="h4 mb-0"><?= ($translations['settings']['username']['title']) ?></h2>
                            </div>
                            <div class="row align-items-center g-3 g-sm-4 pb-3">
                                <form action="actions/update_settings.php" method="post">
                                    <div class="col-sm-20">
                                        <label class="form-label" for="name"><?= ($translations['settings']['username']['text_field']) ?></label>
                                        <input class="form-control" type="text" required="" name="new_username"
                                            id="name">
                                    </div>
                            </div>
                            <?php
                            $lastUsernameChangeTimestamp = strtotime($user['last_username_change']);
                            $changeLockDuration = 30 * 24 * 60 * 60;
                            $currentTimestamp = time();
                            $timeUntilChange = $lastUsernameChangeTimestamp + $changeLockDuration - $currentTimestamp;
                            $days = floor($timeUntilChange / (24 * 60 * 60));
                            if ($timeUntilChange > 0) {
                                echo "<p class='text-danger'>" . str_replace('{days}', '<strong>' . $days . ' Tagen</strong>', $translations['settings']['username']['text_disabled']) . "</p>";
                            } else {
                                echo "<p class='text-muted'>" . ($translations['settings']['username']['text']) . "</p>";
                            }
                            ?>

                            <div class="d-flex justify-content-end pt-3">
                                <input type="hidden" name="setting_identifier" value="username_change">

                                <?php
                                if ($timeUntilChange > 0) {
                                    echo "<button class='btn btn-danger ms-3' type='submit' disabled><i class='fa-solid fa-ban'></i> " . ($translations['settings']['save']) . "</button>";
                                } else {
                                    // Sperre ist nicht mehr aktiv oder abgelaufen, daher Button aktivieren
                                    echo "<button class='btn btn-primary ms-3' type='submit'><i class='fa-duotone fa-floppy-disk'></i> " . ($translations['settings']['save']) . "</button>";


                                }
                                ?>
                            </div>
                            </form>
                        </div>
                    </section>
                    <!--<section class="card border-0 py-1 p-md-2 p-xl-3 p-xxl-4 mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center pb-4 mt-sm-n1 mb-0 mb-lg-1 mb-xl-3">
                                <svg class="svg-inline--fa fa-image text-primary lead pe-1 me-2" aria-hidden="true"
                                    focusable="false" data-prefix="fad" data-icon="image" role="img"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="">
                                    <g class="fa-duotone-group">
                                        <path class="fa-secondary" fill="currentColor"
                                            d="M464 32h-416C21.49 32 0 53.49 0 80v352C0 458.5 21.49 480 48 480h416c26.51 0 48-21.49 48-48v-352C512 53.49 490.5 32 464 32zM111.1 96c26.51 0 48 21.49 48 48S138.5 192 111.1 192s-48-21.49-48-48S85.48 96 111.1 96zM446.1 407.6C443.3 412.8 437.9 416 432 416H82.01c-6.021 0-11.53-3.379-14.26-8.75c-2.73-5.367-2.215-11.81 1.334-16.68l70-96C142.1 290.4 146.9 288 152 288s9.916 2.441 12.93 6.574l32.46 44.51l93.3-139.1C293.7 194.7 298.7 192 304 192s10.35 2.672 13.31 7.125l128 192C448.6 396 448.9 402.3 446.1 407.6z">
                                        </path>
                                        <path class="fa-primary" fill="currentColor"
                                            d="M446.1 407.6C443.3 412.8 437.9 416 432 416H82.01c-6.021 0-11.53-3.379-14.26-8.75c-2.73-5.367-2.215-11.81 1.334-16.68l70-96C142.1 290.4 146.9 288 152 288s9.916 2.441 12.93 6.574l32.46 44.51l93.3-139.1C293.7 194.7 298.7 192 304 192s10.35 2.672 13.31 7.125l128 192C448.6 396 448.9 402.3 446.1 407.6z">
                                        </path>
                                    </g>
                                </svg>
                                <h2 class="h4 mb-0">Profilbild ändern</h2>
                            </div>
                    <form method="post" enctype="multipart/form-data">
                        <div class="row align-items-center g-3 g-sm-4 pb-3">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="col-form-label" for="file-input"></label>
                                    <input class="form-control" name="file" type="file" id="file-input">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end pt-3">
                            <div class="btn-group" role="group" aria-label="Solid button group">
                                <button class="btn btn-primary" name="action" value="uploadIcon"
                                    type="submit">Hochladen</button>
                                <button class="btn btn-danger" name="action" value="removeIcon"
                                    type="submit">Löschen</button>
                            </div>
                        </div>
                    </form>
                </div>
                </section>-->
                    <section class="card border-0 py-1 p-md-2 p-xl-3 p-xxl-4 mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center pb-4 mt-sm-n1 mb-0 mb-lg-1 mb-xl-3">
                                <h2 class="h4 mb-0"><?= ($translations['settings']['effects']['title']) ?></h2>
                            </div>
                            <form action="actions/update_settings.php" method="post">
                                <div class="row align-items-center g-3 g-sm-4 pb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="customSwitch1"
                                                    name="effects" <?php echo ($effectsaktiv) ? 'checked' : ''; ?>>

                                                <label class="custom-control-label" for="customSwitch1"><?= ($translations['settings']['effects']['toggle']) ?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($effectsaktiv): ?>
                                    <p class="text-success"><?= ($translations['settings']['effects']['text_on']) ?></p>
                                    <p class="text-info"><?= ($translations['settings']['effects']['text_on_1']) ?></p>

                                <?php else: ?>
                                    <p class="text-muted"><?= ($translations['settings']['effects']['text_gen']) ?></p>
                                <?php endif; ?>
                                <div class="d-flex justify-content-end pt-3">
                                    <input type="hidden" name="setting_identifier" value="effects">
                                    <button class="btn btn-primary" type="submit"><i class="fa-duotone fa-floppy-disk"></i> <?= ($translations['settings']['save']) ?></button>
                                </div>
                            </form>
                        </div>
                    </section>
                    <section class="card border-0 py-1 p-md-2 p-xl-3 p-xxl-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center pb-4 mt-sm-n1 mb-0 mb-lg-1 mb-xl-3">
                                <!-- <i class="fa-duotone fa-trash text-primary lead pe-1 me-2"></i> -->
                                <h2 class="h4 mb-0"><?= ($translations['settings']['delete_account']['title']) ?></h2>
                            </div>
                            <div class="alert alert-danger d-flex mb-4">
                                <!-- <i class="fa-duotone fa-exclamation-triangle fs-xl me-2"></i> -->
                                <p class="mb-0"><i class="fa-solid fa-triangle-exclamation"></i> <?= ($translations['settings']['delete_account']['text']) ?></p>
                            </div>
                            <div class="d-flex flex-column flex-sm-row justify-content-end pt-4 mt-sm-2 mt-md-3">
                                <button data-toggle="modal" data-target="#modal_5"
                                    class="btn btn-sm btn-danger btn-icon-label">
                                    <span class="btn-inner--icon">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </span>
                                    <span class="btn-inner--text"><?= ($translations['settings']['delete_account']['title']) ?></span>
                                </button>

                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
    </div>

    <?php
    include 'settings/footer.php';
    ?>
</body>

<div class="modal modal-dark fade" id="modal_5" tabindex="-1" role="dialog" aria-labelledby="modal_5"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h6" id="modal_title_6"><?= ($translations['settings']['delete_account']['title']) ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="py-3 text-center">
                    <i class="fas fa-exclamation-circle fa-4x"></i>
                    <h5 class="heading h4 mt-4"><?= ($translations['settings']['delete_account']['modal']['subtitle']) ?></h5>
                    <p><?= ($translations['settings']['delete_account']['modal']['text']) ?></p>
                </div>
            </div>

            <div class="modal-footer">
                <form action="?delete_confirm" method="post">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><i class="fa-solid fa-xmark"></i> <?= ($translations['settings']['delete_account']['modal']['cancel']) ?></button>
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i> <?= ($translations['settings']['delete_account']['modal']['delete']) ?></button>
                </form>
            </div>

            </html>