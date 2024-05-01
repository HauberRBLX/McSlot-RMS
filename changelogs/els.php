<meta name='og:title' content='Einkaufsliste • ELSystem'>
<meta name="description" content="Changelog / System | ELSystem">
<meta name="keywords" content="Einkaufsliste, ELSystem">
<meta name="author" content="PvPMaster0001">
<meta name='copyright' content='ELSystem'>
<?php
date_default_timezone_set('Europe/Berlin');
$datum = date("d.m.Y H:i");
?>
<html lang="de">
<?php include '../settings/config.php' ?>
<head>
   <link rel="shortcut icon" type="image/x-icon" href="<?= $faviconurl ?>">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

   <script>
      window.addEventListener("load", function () {
         setTimeout(function () {
            document.querySelector('body').classList.add('loaded');
         }, 150);
      });
   </script>
   <link rel="manifest" href="manifest.json">
   <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
   <link rel="stylesheet" href="<?= $assetspath ?>style/libs/swiper/dist/css/swiper.min.css">
   <link rel="stylesheet" href="<?= $assetspath ?>style/libs/@fancyapps/fancybox/dist/jquery.fancybox.min.css">
   <link rel="stylesheet" href="<?= $assetspath ?>style/css/preloader-dark.css" id="stylesheet">
   <link rel="stylesheet" href="<?= $assetspath ?>style/css/style.css" id="stylesheet">
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
require '../db_connection.php';
$query = "SELECT setting_value FROM settings WHERE setting_name = 'website_name'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
   $row = $result->fetch_assoc();
   $name = $row["setting_value"];
   $selected_language = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : $default_language;
   // Übersetzungsdatei basierend auf der gewünschten Sprache laden
   $translationsFile = '../settings/lang/' . $selected_language . '.json';

   if (file_exists($translationsFile)) {
      $translations = json_decode(file_get_contents($translationsFile), true);
   }
}
?>
  
<title>ELS Changelog</title>
<section class="slice slice-sm bg-section-secondary">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-12"></div>
    </div>
    <br>
    <br>
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 text-center">
          <a href="<?php echo $siteurl ?>" class="btn btn-success btn-icon hover-translate-y-n3">
            <span class=btn-inner--icon>
              <i class="fa-solid fa-house"></i>
            </span>
            <span class=btn-inner--text>Zur Startseite</span>
          </a><br><br>
        </div>
      </div>
      <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
          <div class="card card-fluid">
            <div class="card-body text-center">
              <h3>ELSystem</h3>
              <p>EinkaufsListeSystem</p>
            </div>
          </div>
        </div>
      </div>
      <br>
      <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
          <h3 class="text-center">Changelog</h3>
          <hr>



          <?php
          $servername = "updates.mcslot.net";
          $username = "ELSystem-Updates";
          $password = "x6n7jB70?";
          $database = "ELSystem-Updates";

          // Verbindung zur Datenbank herstellen
          $conn = new mysqli($servername, $username, $password, $database);

          // Überprüfen, ob die Verbindung erfolgreich war
          if ($conn->connect_error) {
            die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
          }

          $sql = "SELECT * FROM changelogs ORDER BY Version DESC";
          $result = $conn->query($sql);

          if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              if ($row['badge'] == 2) {
                echo '<h6><span class="badge badge-secondary">PreRelease</span></h6>';
              } elseif ($row['badge'] == 1) {
                echo '<h6><span class="badge badge-danger">Beta</span></h6>';
              }
              echo '<h4> ' . $row['id'] . ' | v' . $row['version'] . '</h4>';

              $date = strtotime($row['datum']);
              $formattedDate = date('d.m.Y', $date);
              echo '<h6>' . $formattedDate . '</h6>';

              if (!empty($row['hinzugefuegt'])) {
                echo '<h5>Hinzugefügt</h5>';
                echo '<ul>';

                $hinzugefuegtItems = explode(', ', $row['hinzugefuegt']);

                foreach ($hinzugefuegtItems as $item) {
                  echo '<li><h6>' . $item . '</h6></li>';
                }

                echo '</ul>';
              }

              if (!empty($row['geaendert'])) {
                echo '<h5>Geändert</h5>';
                echo '<ul>';

                $geaendertItems = explode(', ', $row['geaendert']);

                foreach ($geaendertItems as $item) {
                  echo '<li><h6>' . $item . '</h6></li>';
                }

                echo '</ul>';
              }

              if (!empty($row['behoben'])) {
                echo '<h5>Behoben</h5>';
                echo '<ul>';

                $behobenItems = explode(', ', $row['behoben']);

                foreach ($behobenItems as $item) {
                  echo '<li><h6>' . $item . '</h6></li>';
                }

                echo '</ul>';
              }

              echo '<hr>';
            }
          } else {
            echo 'Keine Changelogs vorhanden.';
          }

          // Datenbankverbindung schließen
          $conn->close();
          ?>



        </div>
      </div>
    </div>
</section>
<script type="text/javascript">

  var message = "Sorry, right-click has been disabled";
  function clickIE() { if (document.all) { (message); return false; } }
  function clickNS(e) {
    if
      (document.layers || (document.getElementById && !document.all)) {
      if (e.which == 2 || e.which == 3) { (message); return false; }
    }
  }
  if (document.layers) { document.captureEvents(Event.MOUSEDOWN); document.onmousedown = clickNS; }
  else { document.onmouseup = clickNS; document.oncontextmenu = clickIE; }
  document.oncontextmenu = new Function("return false") 
</script>
<?php include '../settings/footer.php' ?>
</body>

</html>
<!-- System by PvPMaster0001 -->