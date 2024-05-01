<!-- cdn_hoster.php -->
<html>
<meta name='og:title' content='CDN-Hoster • Admin'>
<meta name="description" content="CDN-Hoster | DLSystem">
<meta name="keywords" content="DLSystem">
<meta name="author" content="PvPMaster0001">
<meta name='copyright' content='DLSystem'>

<?php
session_start();
require '../db_connection.php';
include '../database/db_cdn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

$sql    = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'] . " AND (role = 'Supporter' OR role = 'Admin' OR role = 'Owner')";
$result = $conn->query($sql);
$user   = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "Du hast keine Berechtigung für diese Aktion.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

$sql    = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'];
$result = $conn->query($sql);
$user   = $result->fetch_assoc();

if ($user['gesperrt'] == 1) {
    // Benutzer ausloggen
    session_destroy();
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

include '../settings/config.php';
include '../settings/head_admin.php';
include '../settings/header_admin.php';

$sql_hoster = "SELECT * FROM hoster_list ORDER BY hoster_url";
$result_hoster = $conn->query($sql_hoster);
?>

<title>CDN-Hoster &mdash; Admin</title>

<body>
    <section class="pt-5 bg-section-secondary">
        <div class=container>
            <div class="row justify-content-center">
                <div class=col-lg-9>
                    <div class="row align-items-center">
                        <div class=col>
                            <span class=surtitle>Admin</span>
                            <h1 class="h2 mb-0">CDN-Hoster</h1>
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
        <div class=container>
            <div class="row justify-content-center">
                <div class=col-lg-9>
            <?php
if (isset($_SESSION['success_message'])) {
?>
              <div class="alert alert-success" role="alert">
               <div class="alert-group-prepend">
               <span class="alert-group-icon text-">
               <i class="fa-solid fa-circle-check"></i>
               </span>
               <?php
    echo $_SESSION['success_message'];
?>
           </div>
               </div>
              
               <?php
    unset($_SESSION['success_message']); // Lösche die Session-Variablen nach der Anzeige 
?>
           <?php
}
?>

            <?php
if (isset($_SESSION['error_message'])) {
?>
              <div class="alert alert-danger" role="alert">
               <div class="alert-group-prepend">
               <span class="alert-group-icon text-">
               <i class="fa-solid fa-circle-exclamation"></i>
               </span>
               <?php
    echo $_SESSION['error_message'];
?>
           </div>
               </div>


               <?php
    unset($_SESSION['error_message']); // Lösche die Session-Variablen nach der Anzeige 
?>
           <?php
}
?>
                   <div class=row>
                        <div class=col-lg-12>
                            <center>
                                <h3>CDN-Hoster</h3>
                            </center>
                          <?php if ($user['role'] === 'Admin' || $user['role'] === 'Owner') { ?>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCDNHostModal">
        CDN Host hinzufügen
    </button>
<?php } ?>
                          
                          <?php if ($user['role'] === 'Admin' || $user['role'] === 'Owner') { ?>
                            <div class="modal fade" id="addCDNHostModal" tabindex="-1" role="dialog"
                            aria-labelledby="addCDNHostModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addCDNHostModalLabel">CDN Server hinzufügen</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Formular für das Hinzufügen eines Benutzers -->
                                        <form action="actions/add_cdn_server.php" method="post">
                                            <div class="form-group">
                                                <label for="newDomain">Domain</label>
                                                <input type="url" class="form-control" name="newDomain" required>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Schließen</button>
                                                <button type="submit" class="btn btn-primary">CDN Server hinzufügen</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
<?php } ?>
                          
                          <br><hr>
                          <div class="table-responsive">
                            <table class="table table-cards align-items-center">
                                <thead>
                                    <tr>
                                        <th scope="col">Link</th>
                                        <th scope="col">Standort</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
<?php


if ($result_hoster->num_rows > 0) {
    // Aktuelles Datum und Zeit erhalten
    $current_datetime = time();
    
    while ($row_hoster = $result_hoster->fetch_assoc()) {
      
        $hoster_url = $row_hoster['hoster_url'];
        $hoster_url = preg_replace("(^https?://)", "", $hoster_url); // Remove http:// or https:// from the beginning
        
        $location    = $row_hoster['location'];
        $status      = $row_hoster['status'];
        $id          = $row_hoster['id'];
        $badge_class = "";
        
        switch ($status) {
            case 'Online':
                $badge_class_status = "badge-success";
                break;
            case 'Offline':
                $badge_class_status = "badge-danger";
                break;
            case 'error':
                $status             = "Fehler";
                $badge_class_status = "badge-warning";
                break;
            case 'maintenance':
                $status             = "Wartungen";
                $badge_class_status = "badge-primary";
                break;
            case 'checking':
                $status             = "Überprüfung";
                $badge_class_status = "badge-info";
                break;
            default:
                $status             = "Unbekannt";
                $badge_class_status = "badge-secondary";
        }
        
        // Ausgabe der Daten in der Tabelle
        echo "<tr>";
        echo "<th><a href='" . $row_hoster['hoster_url'] . "' target='_blank'>" . $hoster_url . "</a></th>";
        echo "<td>" . $location . "</td>";
        echo "<td><span class='badge badge-dot'><i class='" . ($badge_class_status ?? 'NA') . "'></i>" . ($status ?? 'NA') . "</span></td>";

        
        echo "<td class='text-right'>";
        echo '<div class="float-right">';
        
        
        if ($user['role'] === 'Admin' || $user['role'] === 'Owner') {
            echo '<a class="mr-3" data-toggle="modal" href="#editHoster' . $row_hoster['id'] . '"><i class="fas fa-edit"></i></a>';
        echo '<a class="text-danger" href="actions/delete_cdn_server.php?id=' . $row_hoster['id'] . '" onclick="return confirm(\'Sind Sie sicher, dass Sie diesen CDN-Server löschen möchten?\');"><i class="fas fa-trash-alt"></i></a>';
        }
        
        echo "</div>";
        echo "</td>";
        echo "</tr>";
       // MODAL
        echo '<div class="modal fade" id="editHoster' . $row_hoster['id'] . '" tabindex="-1" role="dialog" aria-labelledby="editHosterLabel" aria-hidden="true">';
        echo '<div class="modal-dialog" role="document">';
        echo '<div class="modal-content">';
        echo '<div class="modal-header">';
        echo '<h5 class="modal-title" id="editHosterLabel">Domain bearbeiten</h5>';
        echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close">x';
        echo '<span aria-hidden="true">&times;</span>';
        echo '</button>';
        echo '</div>';
        echo '<div class="modal-body">';
        echo '<form action="actions/edit_cdn_server.php" method="post">';
        echo '<div class="form-group">';
        echo '<p class="col-form-label">Domain ändern</p>';
        echo '<input type="url" class="form-control" id="changeDomain" name="changeDomain" value="' . $row_hoster['hoster_url'] . '">';
        echo '</div>';
        #echo '<div class="form-group">';
        #echo '<p class="col-form-label">Standort ändern</p>';
        #echo '<input type="text" class="form-control" id="changeLocation" name="changeLocation" value="' . $location . '">';
        #echo '</div>';
        echo '<div class="form-group">';
        if ($status == 'Wartungen') {
            echo "<p class='col-form-label'>Wartungsarbeiten <span class='badge badge-success badge-pill'>Aktiv</span> </p>";
        } else {
            echo "<p class='col-form-label'>Wartungsarbeiten <span class='badge badge-danger badge-pill'>Inaktiv</span></p>";
        }

        echo '<div class="custom-control custom-switch">';
        $isChecked = $status == 'Wartungen' ? 'checked' : ''; // Überprüfen, ob Wartungsarbeiten aktiv sind
        echo '<input type="checkbox" class="custom-control-input" id="maintenanceSwitch' . $row_hoster['id'] . '" name="maintenanceSwitch" value="maintenance" ' . $isChecked . '>';
        echo '<label class="custom-control-label" for="maintenanceSwitch' . $row_hoster['id'] . '">Wartungsarbeiten</label>';

        echo '</div>';
        echo '</div>';
        echo '<input type="hidden" name="hoster_id" value="' . $row_hoster['id'] . '">';
        echo '<div class="modal-footer">';
        echo '<button type="button" class="btn btn-secondary" data-dismiss="modal">Schließen</button>';
        echo '<button type="submit" class="btn btn-primary">Speichern</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    // Zeit des letzten Überprüfungszeitpunkts abrufen
} else {
    echo "<tr><td colspan='3'>Keine Daten gefunden</td></tr>";
}


?>




                                </tbody>
                            </table>
                            
                       </div>
<?php
$sql_last_checked    = "SELECT last_execution_datetime FROM queue_last_execution WHERE id = 2";
$result_last_checked = $conn->query($sql_last_checked);
$row_last_checked    = $result_last_checked->fetch_assoc();
$last_checked_time   = strtotime($row_last_checked['last_execution_datetime']);
$time_diff_seconds   = time() - $last_checked_time;

if ($time_diff_seconds < 60) {
    if ($time_diff_seconds == 1) {
        echo "<p style='text-align:center;'>vor <strong>" . $time_diff_seconds . " Sekunde</strong> geprüft</p>";
    } else {
        echo "<p style='text-align:center;'>vor <strong>" . $time_diff_seconds . " Sekunden</strong> geprüft</p>";
    }
} else {
    $last_checked_minutes_ago = floor($time_diff_seconds / 60); // Umrechnung in Minuten und Abrunden
    if ($last_checked_minutes_ago == 1) {
        echo "<p style='text-align:center;'>vor <strong>" . $last_checked_minutes_ago . " Minute</strong> geprüft</p>";
    } else {
        echo "<p style='text-align:center;'>vor <strong>" . $last_checked_minutes_ago . " Minuten</strong> geprüft</p>";
    }
}
?>
                   </div>
                </div>
            </div>
        </div>
    </div>
    </div>


    <?php
include '../settings/footer.php';
?>
</body>

</html>