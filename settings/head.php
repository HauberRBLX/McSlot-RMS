


<head>
   <link rel="shortcut icon" type="image/x-icon" href="<?= $faviconurl ?>">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <script>
        window.addEventListener("load", function () {
            setTimeout(function () {
                console.log('Seite geladen');
                document.querySelector('body').classList.add('loaded');
            }, 10);
        });
    </script>
<?php
  session_start();
         
$styleFileName = 'style.css';
$preloaderFileName = ''; // Leerer String für den Preloader-Stil

$autoModeStartTime = 7;  // 7 Uhr
$autoModeEndTime = 17;   // 17 Uhr

$currentHour = date('G');

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $darkSettingSql = "SELECT night_mode FROM settings_users WHERE user_id = $userId";
    $darkSettingResult = $conn->query($darkSettingSql);

    if ($darkSettingResult && $darkSettingResult->num_rows > 0) {
        $darkSetting = $darkSettingResult->fetch_assoc()['night_mode'];

        // Nachtmodus basierend auf den Einstellungen festlegen
        switch ($darkSetting) {
            case 'on':
                $styleFileName = 'style.css';
                $preloaderFileName = 'preloader-dark.css'; // Preloader für den Lichtmodus
                break;
            case 'off':
                $styleFileName = 'style-light.css';
                $preloaderFileName = 'preloader-light.css'; // Preloader für den Lichtmodus
                break;
            case 'auto':
                // Überprüfen Sie, ob es zwischen 7 Uhr und 17 Uhr liegt, um den Stil automatisch zu ändern
                if ($currentHour >= $autoModeStartTime && $currentHour < $autoModeEndTime) {
                    $styleFileName = 'style-light.css'; // Light-Modus zwischen 7 und 17 Uhr
                    $preloaderFileName = 'preloader-light.css'; // Preloader für den Lichtmodus
                } else {
                    $styleFileName = 'style.css'; // Dark-Modus außerhalb dieses Zeitrahmens
                    $preloaderFileName = 'preloader-dark.css'; // Preloader für den Lichtmodus
                }
                break;
            default:
                // Wenn der Wert von 'night_mode' ungültig ist, verwenden Sie den Standardstil 'style.css'
                $styleFileName = 'style.css';
        }
    }
} else {
    // Wenn der Benutzer nicht angemeldet ist, verwenden Sie standardmäßig den Dunkelmodus
    $styleFileName = 'style.css';
    $preloaderFileName = 'preloader-dark.css';
}
?>

<?php if ($preloaderFileName !== ''): ?>
    <link rel="stylesheet" href="<?= $assetspath ?>style/css/<?= $preloaderFileName ?>" id="preloader-stylesheet">
<?php endif; ?>
<link rel="stylesheet" href="<?= $assetspath ?>style/css/<?= $styleFileName ?>" id="stylesheet">

   <link rel="manifest" href="manifest.json">
   <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
   <link rel="stylesheet" href="<?= $assetspath ?>style/libs/swiper/dist/css/swiper.min.css">
   <link rel="stylesheet" href="<?= $assetspath ?>style/libs/@fancyapps/fancybox/dist/jquery.fancybox.min.css">
   <script src="https://code.jquery.com/jquery-3.5.1.min.js"
      integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/js/all.min.js"
      integrity="sha256-HkXXtFRaflZ7gjmpjGQBENGnq8NIno4SDNq/3DbkMgo=" crossorigin="anonymous"></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css"
      integrity="sha256-2XFplPlrFClt0bIdPgpz8H7ojnk10H69xRqd9+uTShA=" crossorigin="anonymous" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css"
      integrity="sha256-ENFZrbVzylNbgnXx0n3I1g//2WeO47XxoPe0vkp3NC8=" crossorigin="anonymous" />
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"
      integrity="sha256-3blsJd4Hli/7wCQ+bmgXfOdK7p/ZUMtPXY08jmxSSgk=" crossorigin="anonymous"></script>
   <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
   <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
   <script src="<?= $assetspath ?>style/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</head>
<div class="preloader">
   <div class="spinner-border text-primary" role="status">
      <span class="sr-only">Lade...</span>
   </div>
</div>


<?php
	
	

require 'db_connection.php';

// LOCKDOWN & MAINTENANCE

if (isset($_SESSION['user_id'])) {
   $userId = $_SESSION['user_id'];

   // Abrufen der Rolle des Benutzers aus der Datenbank
   $roleSql = "SELECT role FROM benutzer WHERE id = $userId";
   $roleResult = $conn->query($roleSql);

   if ($roleResult && $roleResult->num_rows > 0) {
      $userData = $roleResult->fetch_assoc();
      $userRole = $userData['role'];

      // Nur der Besitzer hat Zugriff bei Lockdown
      if ($userRole != 'Owner') {
         // Überprüfe, ob Lockdown aktiviert ist
         $lockdownSql = "SELECT setting_value FROM settings WHERE setting_name = 'lockdown'";
         $lockdownResult = $conn->query($lockdownSql);
         $lockdownStatus = $lockdownResult->fetch_assoc()['setting_value'];

         // Wenn Lockdown aktiviert ist, zeige eine Meldung und beende das Skript
         if ($lockdownStatus == 1) {
            include 'error/lockdown.php';
            die();
         }
      }
   }
} else {
   // Wenn kein Benutzer eingeloggt ist und Lockdown aktiviert ist, zeige eine Meldung und beende das Skript
   $lockdownSql = "SELECT setting_value FROM settings WHERE setting_name = 'lockdown'";
   $lockdownResult = $conn->query($lockdownSql);
   $lockdownStatus = $lockdownResult->fetch_assoc()['setting_value'];

   if ($lockdownStatus == 1) {
      include 'error/lockdown.php';
      die();
   }
}

// WEBSITE NAME

$query = "SELECT setting_value FROM settings WHERE setting_name = 'website_name'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
   $row = $result->fetch_assoc();
   $name = $row["setting_value"];
   $selected_language = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : $default_language;
   // Übersetzungsdatei basierend auf der gewünschten Sprache laden
   $translationsFile = 'settings/lang/' . $selected_language . '.json';

   if (file_exists($translationsFile)) {
      $translations = json_decode(file_get_contents($translationsFile), true);
   }
}
         

?>