<?php

$currentScript = basename($_SERVER['PHP_SELF']);

// Überprüfen, ob der Benutzer bereits auf der Seite security.php ist
$isOnSecurityPage = ($currentScript == 'security.php' || $currentScript == 'security');

if (!$isOnSecurityPage) {
    // Der Benutzer ist nicht auf der Seite security.php

    // SQL-Abfrage, um die Benutzerrolle abzurufen
    $sqlUserRole = "SELECT role FROM benutzer WHERE id = " . $_SESSION['user_id'];

    $resultUserRole = $conn->query($sqlUserRole);

    if ($resultUserRole->num_rows > 0) {
        $rowUserRole = $resultUserRole->fetch_assoc();
        $currentUserRole = $rowUserRole["role"];

        // Überprüfen, ob die Rolle des Benutzers das Modal anzeigen darf
        $allowedRoles = ['Supporter', 'Admin', 'Owner'];
        if (in_array($currentUserRole, $allowedRoles)) {
            // SQL-Abfrage, um die Einstellung für die Zwei-Faktor-Authentifizierung für das Team abzurufen
            $sql2FASetting = "SELECT setting_value FROM settings WHERE setting_name = '2fa_requirement_team'";

            $result2FASetting = $conn->query($sql2FASetting);

            if ($result2FASetting->num_rows > 0) {
                $row2FASetting = $result2FASetting->fetch_assoc();
                $requirementTeam2FA = $row2FASetting["setting_value"];

                if ($requirementTeam2FA == 1) {
                    // SQL-Abfrage, um das TOTP-Geheimnis des Benutzers abzurufen
                    $sqlTOTPSecret = "SELECT totp_secret FROM benutzer WHERE id = " . $_SESSION['user_id'];

                    $resultTOTPSecret = $conn->query($sqlTOTPSecret);

                    if ($resultTOTPSecret->num_rows > 0) {
                        $rowTOTPSecret = $resultTOTPSecret->fetch_assoc();
                        $userTOTPSecret = $rowTOTPSecret["totp_secret"];

                        if ($userTOTPSecret === NULL) {
                            // Das TOTP-Geheimnis des Benutzers ist NULL

                            echo '<div class="modal modal-danger fade"  id="securityModal" tabindex="-1" role="dialog" aria-labelledby="securityModalLabel" aria-hidden="true">';
                            echo '<div class="modal-dialog" role="document">';
                            echo '<div class="modal-content">';
                            echo '<div class="modal-header">';
                            echo '<h5 class="modal-title" id="securityModalLabel">Zwei-Faktor-Authentifizierung erforderlich</h5>';
                            
                            echo '</div>';
							
                            echo '<div class="modal-body">';
                            echo '<p>Um fortzufahren, ist eine Zwei-Faktor-Authentifizierung erforderlich. Klicken Sie auf "Zur Sicherheitsseite", um sie einzurichten.</p>';
                            echo '</div>';
                            echo '<div class="modal-footer">';
                            echo '<a href="' . ($phpenable === 'true' ? $siteurl . "security.php" : $siteurl . "security") . '" class="btn btn-light">Zur Sicherheitsseite</a>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';

                            // Modal anzeigen (JavaScript)
                            echo '<script>$(document).ready(function() { $("#securityModal").modal("show"); });</script>';
                        }
                    }
                }
            }
        }
    }
}

?>

<header class="" id="header-main">
   <nav class="navbar navbar-main navbar-expand-lg" id="navbar-main">
      <div class="container">
<a href="<?= $siteurl ?>">
    <img src="<?= ($darkSetting == 'on') ? $logourl_dark : $logourl_light ?>" id="navbar-logo" width="200">
    <?= $logotext ?>
</a>
         <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-main-collapse"
            aria-controls="navbar-main-collapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
         </button>
         <ul class="navbar-nav ml-lg-auto">
            <li class="nav-item nav-item-spaced d-none d-lg-block">
               <a class="nav-link" href="<?= $siteurl ?>">
                  Admin Bereich
               </a>
            </li>
         </ul>
         <div class="collapse navbar-collapse navbar-collapse-overlay" id="navbar-main-collapse">
            <div class="position-relative">
               <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-main-collapse"
                  aria-controls="navbar-main-collapse" aria-expanded="false" aria-label="Toggle navigation">
                  <i data-feather="x"></i>
               </button>
            </div>
            <ul class="navbar-nav align-items-lg-center d-lg-flex ml-lg-auto">
               <li class="nav-item dropdown dropdown-animate">
                  <a class="nav-link nav-link-icon px-2" href="#" role="button" data-toggle="dropdown"
                     aria-haspopup="true" aria-expanded="false">
						<i class="fa-duotone fa-gear"></i>
                  </a>

                  <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right dropdown-menu-arrow p-3">
                     <h6 class="dropdown-header px-0 mb-2 text-primary">Adminverwaltung</h6>
                     <a href="<?= ($phpenable === 'true' ? $siteurl . $dash_url . '.php' : $siteurl . $dash_url) ?>"
                        class="dropdown-item">
						<i class="fa-duotone fa-right-to-bracket"></i>
                        <span>Zum Dashboard</span>
                     </a>

                     <a href="<?= ($phpenable === 'true' ? $siteurl . $settings_url . '.php' : $siteurl . $settings_url) ?>"
                        class="dropdown-item">
						<i class="fa-duotone fa-shield-halved"></i>	
                        <span>Mein Account</span>
                     </a>
                  </div>
                 
               </li>
                  <div class="order-lg-4 ml-lg-3"><a class="" href="#modal-profile" role="button" data-toggle="modal"><span
                           class="avatar rounded-circle"><img alt="Image placeholder" src="../assets/img/user.png"></span></a>
                  </div>
              

         </div>
      </div>
   </nav>
                         <div class="modal fade fixed-right" id=modal-profile tabindex=-1 role=dialog aria-hidden=true>
         <div class="modal-dialog modal-vertical" role=document>
            <div class=modal-content>
               <div class=modal-body>
                  <div>
                     <button type=button class=close data-dismiss=modal aria-label=Close>
                        <span aria-hidden=true>&times;</span>
                     </button>
                  </div>
                  <div class=px-4>
                     <div class="d-flex my-4">
                        <div class="avatar-parent-child mx-auto">
                           <img alt="Image placeholder" src=../assets/img/user.png class="avatar avatar-xl rounded-circle">
                          
                        </div>
                       
                       
                       
                     </div>
                     <div class="text-center mb-4">
                     <h6 class="h5 mb-0">
   <?php 
      echo $user['name']; 
      if ($user['verified'] == 1) {
         echo ' <i class="fa-solid fa-badge-check" style="color: #5CC9A7;" title="Verifiziert"></i>';

      }
   ?>
</h6>
                        <span class="d-block text-muted">
                           <strong>
                              <?php echo $user['kontonummer']; ?>
                           </strong>
                        </span>
                        <hr>
                        <span class="d-block text-muted">
                           <strong>
<?php
if ($user['role'] == 'Owner') {
    echo '<span class="badge badge-danger">Owner</span>';
} elseif ($user['role'] == 'Admin') {
    echo '<span class="badge badge-danger">Administrator</span>';
} elseif ($user['role'] == 'Supporter') {
    echo '<span class="badge badge-primary">Supporter</span>';
} else {
    echo '<span class="badge badge-info">Mitglied</span>';
}
?>
                           </strong>
                        </span>

                     </div>
                     <span class="d-block text-muted">
                        <center><strong>
                              <?php
                              if ($user['ticket_sperre'] == 1) {
                                 echo '<td><span class="badge badge-dot"><i class="bg-danger"></i>Ticket Sperre</span></td>';
                              }
                              ?>
                           </strong></center>
                     </span>
                  </div>
               </div>
               <div class="modal-footer py-3 mt-auto">
                  <a href="<?= ($phpenable === 'true' ? $siteurl . $logout_url . '.php' : $siteurl . $logout_url) ?>"
                     class="btn btn-block btn-sm btn-neutral btn-icon rounded-pill">
                     <span class=btn-inner--icon>
                        <i data-feather=log-out></i>
                     </span>
                     <span class=btn-inner--text><?= str_replace('{websiteName}', $name, $translations['header']['already_login']['logout']) ?></span>
                  </a>
               </div>
            </div>
         </div>
      </div>
</header>