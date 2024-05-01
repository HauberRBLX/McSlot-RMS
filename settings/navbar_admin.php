<?php
// Annahme: Die Statusinformationen der CDN-Server sind in der Tabelle hoster_list gespeichert
$statusSql = "SELECT status FROM hoster_list";
$statusResult = $conn->query($statusSql);

// Variablen für die Zählung der verschiedenen Status
$onlineCount = 0;
$offlineCount = 0;
$errorCount = 0;
$maintenanceCount = 0;

// Zähle die verschiedenen Status
while ($row = $statusResult->fetch_assoc()) {
    switch ($row['status']) {
        case 'Online':
            $onlineCount++;
            break;
        case 'Offline':
            $offlineCount++;
            break;
        case 'error':
            $errorCount++;
            break;
        case 'maintenance':
            $maintenanceCount++;
            break;
        default:
            // Handhabung für andere Statuswerte, falls erforderlich
            break;
    }
}

// Generiere entsprechende Benachrichtigung basierend auf den Status
if ($onlineCount > 0 && $offlineCount == 0 && $errorCount == 0 && $maintenanceCount == 0) {
    // Alle Server sind online
    echo '<div class="alert alert-outline-success" role="alert"><center><i class="fas fa-check-circle"></i> Alle CDN-Server sind online.</div></center>';
} elseif (($offlineCount > 0 || $errorCount > 0) && $onlineCount > 0) {
    // Einige Server haben Störungen
    echo '<div class="alert alert-outline-warning" role="alert"><center><i class="fas fa-exclamation-triangle"></i> Einige CDN-Server haben Störungen.</div></center>';
} elseif ($offlineCount == 0 && $errorCount == 0 && $onlineCount == 0) {
    // Alle Server sind offline oder haben Fehler
    echo '<div class="alert alert-outline-warning" role="alert"><center><i class="fas fa-exclamation-triangle"></i> Alle CDN-Server sind offline oder haben Fehler.</div></center>';
}

if ($maintenanceCount > 0 && ($onlineCount > 0 || $offlineCount > 0 || $errorCount > 0)) {
    // Einige Server befinden sich in Wartungsarbeiten
    echo '<div class="alert alert-outline-primary" role="alert"><center><i class="fas fa-wrench"></i> Einige CDN-Server befinden sich in Wartungsarbeiten.</div></center>';
} elseif ($maintenanceCount == ($onlineCount + $offlineCount + $errorCount)) {
    // Alle Server befinden sich in Wartungsarbeiten
    echo '<div class="alert alert-outline-primary" role="alert"><center><i class="fas fa-wrench"></i> Alle CDN-Server befinden sich in Wartungsarbeiten.</div></center>';
}

$query_nav = "SELECT role FROM benutzer WHERE id = " . $_SESSION['user_id'];
$result_nav = $conn->query($query_nav);

// Überprüfen, ob die Abfrage erfolgreich war und ob eine Rolle gefunden wurde
if ($result_nav && $result_nav->num_rows > 0) {
    // Benutzerrolle aus dem Abfrageergebnis extrahieren
    $row_nav = $result_nav->fetch_assoc();
    $user_role = $row_nav['role'];

    // Definiere die Links und ihre entsprechenden Rollen wie zuvor
  $links = array(
      array(
          "text" => "Übersicht",
          "url" => $siteurl . $admin_directory,
          "file" => "index",
          "roles" => array("Supporter", "Admin", "Owner")
      ),
      array(
          "text" => "Benutzer",
          "url" => $base_url_admin . ($phpenable === 'true' ? $users_url_admin . $base_url_suffix : $users_url_admin),
          "file" => "users",
          "roles" => array("Supporter", "Admin", "Owner")
      ),
      array(
          "text" => "Einstellungen",
          "url" => $base_url_admin . ($phpenable === 'true' ? $settings_url_admin . $base_url_suffix : $settings_url_admin),
          "file" => "settings",
          "roles" => array("Admin", "Owner")
      ),
      array(
          "text" => "Tickets",
          "url" => $base_url_admin . ($phpenable === 'true' ? $tickets_url_admin . $base_url_suffix : $tickets_url_admin),
          "file" => "tickets",
          "roles" => array("Supporter", "Admin", "Owner")
      ),
      array(
          "text" => "Download Logs <!--<span class='badge badge-primary'><i class='fa-solid fa-sparkles'></i> Neu</span>-->",
          "url" => $base_url_admin . ($phpenable === 'true' ? $dl_logs_url_admin . $base_url_suffix : $dl_logs_url_admin),
          "file" => "dl_logs",
          "roles" => array("Supporter", "Admin", "Owner")
      ),
      array(
          "text" => "CDN Hoster <span class='badge badge-primary'><i class='fa-solid fa-sparkles'></i> Neu</span>",
          "url" => $base_url_admin . ($phpenable === 'true' ? $cdn_hoster_url_admin . $base_url_suffix : $cdn_hoster_url_admin),
          "file" => "cdn_hoster",
          "roles" => array("Supporter", "Admin", "Owner")
      )
  );

    // Funktion zum Überprüfen, ob der Benutzer die erforderliche Rolle für einen Link hat (wie zuvor)
    function canAccessLink($user_role, $allowed_roles) {
        return in_array($user_role, $allowed_roles);
    }

    // Aktuelle Seite ermitteln
    $current_page = basename($_SERVER['PHP_SELF'], ".php");

    // Ausgabe der Links (wie zuvor)
    ?>
    <ul class="nav nav-tabs overflow-x">
        <?php foreach ($links as $link): ?>
            <?php if (canAccessLink($user_role, $link['roles'])): ?>
                <li class="nav-item">
                    <a href="<?= $link['url'] ?>" class="nav-link <?= ($current_page == $link['file']) ? 'active' : ''; ?>"><?= $link['text'] ?></a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <?php
} else {
}


?>
