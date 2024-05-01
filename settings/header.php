<link href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css" rel="stylesheet">

<?php
session_start();
require 'db_connection.php';

if (isset($_SESSION['user_id'])) {
   $sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'];
   $result = $conn->query($sql);
   $user = $result->fetch_assoc();

   $userId = $_SESSION['user_id'];
   $sql = "SELECT deleted FROM benutzer WHERE id = ?";
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("i", $userId);
   $stmt->execute();
   $stmt->bind_result($isDeleted);
   $stmt->fetch();
   $stmt->close();

   if ($isDeleted == 1) {
      header("Location: " . ($phpenable === 'true' ? $siteurl . $reactive_url . '.php' : $siteurl . $reactive_url));
      exit;
   } elseif ($isDeleted == 0) {
   }
	
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
                  <a class="nav-link"
                     href="<?= ($phpenable === 'true' ? $siteurl . $dash_url . '.php' : $siteurl . $dash_url) ?>">
                     <?= str_replace('{websiteName}', $name, $translations['header']['already_login']['dashboard']) ?>
                  </a>
               </li>
            </ul>
            <ul class="navbar-nav ml-lg-auto">
               <li class="nav-item nav-item-spaced d-none d-lg-block">
                  <a class="nav-link" href="<?= ($phpenable === 'true' ? $ticket_url . '.php' : $ticket_url) ?>">
                     <?= str_replace('{websiteName}', $name, $translations['header']['already_login']['tickets']) ?>
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
                        <h6 class="dropdown-header px-0 mb-2 text-primary">
                           <?= str_replace('{websiteName}', $name, $translations['header']['already_login']['my_account']) ?>
                        </h6>

                        <a href="<?= ($phpenable === 'true' ? $settings_url . '.php' : $settings_url) ?>"
                           class="dropdown-item">
<i class="fa-duotone fa-screwdriver-wrench"></i>
                           <span>
                              <?= str_replace('{websiteName}', $name, $translations['header']['already_login']['settings']) ?>
                           </span>
                       </a>

                        <a href="<?= ($phpenable === 'true' ? $security_url . '.php' : $security_url) ?>"
                           class="dropdown-item">
<i class="fa-duotone fa-shield-halved"></i>
                           <span>
                              <?= str_replace('{websiteName}', $name, $translations['header']['already_login']['security']) ?>
                           </span>
                        </a>
                      <?php
                      if ($user['role'] == 'Supporter' || $user['role'] == 'Admin' || $user['role'] == 'Owner') {
                         echo '<a href="' . $siteurl . 'admin" class="dropdown-item">
							<i class="fa-duotone fa-user-tie"></i>
                               <span>' . str_replace('{websiteName}', $name, $translations['header']['already_login']['admin_button']) . '</span>
                            </a>';
                      }
                      ?>

                     </div>
                  </li>
                  <div class="order-lg-4 ml-lg-3"><a class="" href="#modal-profile" role="button" data-toggle="modal"><span
                           class="avatar rounded-circle"><img alt="Image placeholder" src="assets/img/user.png"></span></a>
                  </div>
            </div>
         </div>
      </nav>
   </header>

   <?php

   session_start();
   require 'db_connection.php';


   $sql = "SELECT benutzer.*, settings_users.effects
         FROM benutzer
         JOIN settings_users ON benutzer.id = settings_users.user_id
         WHERE benutzer.id = " . $_SESSION['user_id'];

   $result = $conn->query($sql);
   $userWithSettings = $result->fetch_assoc();

   date_default_timezone_set('Europe/Berlin');

   $currentDate = date('Y-m-d');

   if ($userWithSettings['effects'] == 1 && date('m', strtotime($currentDate)) == 12) {
      // Nur wenn der Schneeeffekt für den Benutzer aktiviert ist und das Datum im Dezember ist
      echo '<script src="' . $siteurl . '/assets/style/js/snow.js"></script>';
   }

   if ($userWithSettings['effects'] == 1 && (date('m-d', strtotime($currentDate)) >= '01-01' && date('m-d', strtotime($currentDate)) <= '01-08')) {
      echo '<script src="' . $siteurl . '/assets/style/js/new_year.js"></script>';
   }
   ?>

   <body>
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
                           <img alt="Image placeholder" src=assets/img/user.png class="avatar avatar-xl rounded-circle">
                          
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
      </head>
      <?php
} else {
   // Der Benutzer ist nicht angemeldet
   ?>

      <?php include 'config.php'; ?>

      <body>
         <div class=preloader>
            <div class="spinner-border text-primary" role=status>
               <span class=sr-only>Loading...</span>
            </div>
         </div>
         <header class="" id=header-main>
            <nav class="navbar navbar-main navbar-expand-lg navbar-dark" id=navbar-main>
               <div class=container>
                  <a href="<?= $siteurl ?>">
                     <img src="<?= $logourl_dark ?>" id="navbar-logo" width="200">
                     <?= $logotext ?>
                  </a>
                  <button class=navbar-toggler type=button data-toggle=collapse data-target=#navbar-main-collapse
                     aria-controls=navbar-main-collapse aria-expanded=false aria-label="Toggle navigation">
                     <span class=navbar-toggler-icon></span>
                  </button>
                  <div class="collapse navbar-collapse navbar-collapse-overlay" id=navbar-main-collapse>
                     <div class=position-relative>
                        <button class=navbar-toggler type=button data-toggle=collapse data-target=#navbar-main-collapse
                           aria-controls=navbar-main-collapse aria-expanded=false aria-label="Toggle navigation">
                           x
                        </button>
                     </div>
                     <ul class="navbar-nav ml-lg-auto">
                        <li class="nav-item nav-item-spaced d-none d-lg-block">
                           <a class=nav-link href="<?= $siteurl ?>">
                              <?= str_replace('{websiteName}', $name, $translations['header']['not_login']['homepage']) ?>
                           </a>
                        </li>
                        <li class="nav-item nav-item-spaced d-none d-lg-block">
                           <a class=nav-link href="<?= $status_url ?>" target="_blank">
                              <?= str_replace('{websiteName}', $name, $translations['header']['not_login']['status']) ?>
                           </a>
                        </li>
                     </ul>
                     <ul class="navbar-nav align-items-lg-center d-none d-lg-flex ml-lg-auto">
                        <li class=nav-item>
                           <a class=nav-link href="<?= ($phpenable === 'true' ? $login_url . '.php' : $login_url) ?>">
                              <i class="fa-solid fa-right-to-bracket"></i> <?= str_replace('{websiteName}', $name, $translations['header']['not_login']['login']) ?>
                           </a>
                        </li>
                        <li class=nav-item>
                           <a href="<?= ($phpenable === 'true' ? $register_url . '.php' : $register_url) ?>"
                              class="btn btn-sm btn-primary btn-icon ml-3" target=_blank>
                              <span class=btn-inner--icon>
                                 <i class="fa-solid fa-user-plus"></i>
                              </span>
                              <span class=btn-inner--text><?= str_replace('{websiteName}', $name, $translations['header']['not_login']['register']) ?></span>
                           </a>
                        </li>
                     </ul>
                  </div>
               </div>
            </nav>
         </header>

         <body>
            <?php
}
?>