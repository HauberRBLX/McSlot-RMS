<!-- users.php -->
<meta name='og:title' content='DLSystem • Admin'>
<meta name="description" content="Users | DLSystem">
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
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'];
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if ($user['gesperrt'] == 1) {
    // Benutzer ausloggen
    session_destroy();
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

include '../settings/config.php';
include '../settings/head_admin.php';
include '../settings/header_admin.php';

// edit_user.php

// Überprüfe, ob die Kontonummer als Parameter übergeben wurde
if (isset($_GET['kn'])) {
    $kontonummer = $_GET['kn'];

    // Führe eine Abfrage durch, um den Benutzer mit dieser Kontonummer zu erhalten
    $sql = "SELECT * FROM benutzer WHERE kontonummer = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $kontonummer);
    $stmt->execute();
    $result = $stmt->get_result();

    // Überprüfe, ob der Benutzer gefunden wurde
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        ?>

<title>Benutzer editieren - <?= $name ?></title>
        <body>
        <section class="pt-5 bg-section-secondary">
    <div class=container>
        <div class="row justify-content-center">
            <div class=col-lg-9>
                <div class="row align-items-center">
                    <div class=col>
                        <span class=surtitle>Admin</span>
                        <h1 class="h2 mb-0">Benutzer bearbeiten</h1>
                    </div>
                </div>

                <div class="row align-items-center mt-4">
                    <div class=col>
                        <ul class="nav nav-tabs overflow-x">
                            <li class=nav-item>
                                <a href="<?= $siteurl ?><?= $admin_directory ?>" class="nav-link">Übersicht</a>
                            </li>
                            <li class=nav-item>
                                <a href=" <?= ($phpenable === 'true' ? $users_url_admin . '.php' : $users_url_admin) ?>"
                                    class="
                                        nav-link active">Benutzer</a>
                            </li>
                            <li class=nav-item>
                                <a href=" <?= ($phpenable === 'true' ? $settings_url_admin . '.php' : $settings_url_admin) ?>"
                                    class=" nav-link">Einstellungen</a>
                            </li>
                            <li class=nav-item>
                                <a href=" <?= ($phpenable === 'true' ? $codes_url_admin . '.php' : $codes_url_admin) ?>"
                                    class="nav-link">Codes</a>
                            </li>
                            <li class=nav-item>
                                <a href=" <?= ($phpenable === 'true' ? $tickets_url_admin . '.php' : $tickets_url_admin) ?>"
                                    class="nav-link">Tickets</a>
                            </li>
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
                            <h3>Benutzer <strong><?= $row['name'] ?></strong></h3>
                            <h6>Kontonummer <strong><?= $row['kontonummer'] ?></strong></h6>
                        </center>
                        
                    </div>
                    <form action="actions/update_user.php" method="post">
    <div class="form-group">
        <label for="userName">Benutzername</label>
        <input type="text" class="form-control" name="userName" value="<?= $row['name'] ?>">
    </div>
    <div class="form-group">
        <label for="userPassword">Passwort</label>
        <input type="password" class="form-control" name="userPassword" placeholder="Neues Passwort" autocomplete="new-password">
    </div>
    <div class="form-group">
        <label for="userRole">Rolle</label>
    <select class="form-control" name="userRole">
        <option value="Mitglied" <?php if (isset($row['role']) && $row['role'] == 'Mitglied') echo 'selected'; ?>>Mitglied</option>
        <option value="Supporter" <?php if (isset($row['role']) && $row['role'] == 'Supporter') echo 'selected'; ?>>Supporter</option>
        <option value="Admin" <?php if (isset($row['role']) && $row['role'] == 'Admin') echo 'selected'; ?>>Admin</option>
        <option value="Owner" <?php if (isset($row['role']) && $row['role'] == 'Owner') echo 'selected'; ?>>Owner</option>
    </select>
</div>
    <div class="form-group">
    <label for="userSperre">Sperre</label>
    <select class="form-control" name="userSperre" onchange="toggleSperrgrund()" id="userSperre">
        <option value="1" <?= $row['gesperrt'] ? 'selected' : '' ?>>An</option>
        <option value="0" <?= $row['gesperrt'] ? '' : 'selected' ?>>Aus</option>
    </select>
</div>

<div class="form-group" id="sperrgrundDiv" <?= $row['gesperrt'] ? '' : 'style="display:none;"' ?>>
    <label for="userSperrgrund">Sperrgrund</label>
    <input class="form-control" name="userSperrgrund" id="userSperrgrund" rows="3" value="<?= $row['sperrgrund'] ?>" required>
</div>

<script>
    function toggleSperrgrund() {
        var sperrSelect = document.getElementById("userSperre");
        var sperrgrundDiv = document.getElementById("sperrgrundDiv");

        if (sperrSelect.value == 1) {
            sperrgrundDiv.style.display = "block";
            document.getElementById("userSperrgrund").setAttribute("required", "true");
        } else {
            sperrgrundDiv.style.display = "none";
            document.getElementById("userSperrgrund").removeAttribute("required");
        }
    }
    // Initialen Aufruf der Funktion, um sicherzustellen, dass die Anzeige korrekt ist
    toggleSperrgrund();
</script>
</script>
    <div class="form-group">
        <label for="userTicketSperre">Ticket-Sperre</label>
        <select class="form-control" name="userTicketSperre">
            <option value="1" <?= $row['ticket_sperre'] ? 'selected' : '' ?>>An</option>
            <option value="0" <?= $row['ticket_sperre'] ? '' : 'selected' ?>>Aus</option>
        </select>
    </div>
    <div class="form-group">
        <label for="userVerifiziert">Verifiziert</label>
        <select class="form-control" name="userVerifiziert">
            <option value="1" <?= $row['verified'] ? 'selected' : '' ?>>An</option>
            <option value="0" <?= $row['verified'] ? '' : 'selected' ?>>Aus</option>
        </select>
    </div>
    <input type="hidden" name="userId" value="<?= $row['id'] ?>">
    <input type="hidden" name="userKN" value="<?= $row['kontonummer'] ?>">
    <div class="modal-footer">
    <button type="button" class="btn btn-secondary" onclick="window.location.href='<?= ($phpenable === 'true' ? $siteurl . $admin_directory . $users_url_admin . '.php' : $siteurl . $admin_directory . $users_url_admin) ?>';">Abbrechen</button>
        <button type="submit" class="btn btn-primary">Änderungen speichern</button>
    </div>
</form>


                </div>
            </div>
        </div>
    </div>
</body>
    <?php
        // ...
        // Weitere Informationen und Bearbeitungsformular hier einfügen
    } else {
        echo '<p>Benutzer nicht gefunden.</p>';
    }
} else {
    echo '<p>Keine Kontonummer angegeben.</p>';
}

include '../settings/footer.php';
?>

