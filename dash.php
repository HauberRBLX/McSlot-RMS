<!-- index.php -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<?php
$random_id = uniqid();
session_start();
session_name("rms_session");
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
   header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
   exit;
}

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'];
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Überprüfen, ob der Benutzeraccount gesperrt ist
$userId = $_SESSION['user_id'];
$sql = "SELECT gesperrt, name FROM benutzer WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($isLocked, $userName);
$stmt->fetch();
$stmt->close();

if ($isLocked) {
   session_destroy();
   header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
   exit;
}

if (empty($userName)) {
   session_destroy();
   header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
   exit;
}


?>

<?php
include 'settings/config.php';
include 'settings/head.php';
include 'settings/header.php';




      
?>

<title>
   <?= str_replace('{websiteName}', $name, $translations['dash_page']['title']) ?>
</title>

<section class="pt-5 bg-section-secondary">

   <div class=container>

      <div class="row justify-content-center">

         <div class=col-lg-9>
           
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

			 
            <script>
               setTimeout(function () {
                  var errorMessage = document.getElementById('error-message');
                  if (errorMessage) {
                     errorMessage.style.display = 'none';
                     window.location.href = '?';
                  }
               }, 5000);
            </script>
			 
<?php
if ($user['email'] === NULL) {
    // Zeige eine Fehlermeldung direkt auf der Seite an
    echo '<div class="alert alert-group alert-danger alert-icon" role="alert">
    <div class="alert-group-prepend">
        <span class="alert-group-icon text-">
            <i class="fa-regular fa-circle-exclamation"></i>
        </span>
    </div>
    <div class="alert-content">
        Du hast keine E-Mail hinzugefügt. Bitte füge eine E-Mail hinzu.
    </div>
    <div class="alert-action">
		<a href="' . ($phpenable === 'true' ? $settings_url . '.php' : $settings_url) . '" class="btn btn-neutral" aria-label="Hinzufügen">E-Mail hinzufügen</a>
    </div>
</div>';
}
			 
			     echo '<div class="alert alert-group alert-danger alert-icon" role="alert">
    <div class="alert-group-prepend">
        <span class="alert-group-icon text-">
            <i class="fa-regular fa-circle-exclamation"></i>
        </span>
    </div>
    <div class="alert-content">
        McSlot Schließt am 10.05.2024
    </div>
    <div class="alert-action">
		<a href="closed" class="btn btn-neutral" aria-label="Hinzufügen">Weitere Informationen</a>
    </div>
</div>';
?>

            <div class="card mb-n7 position-relative zindex-100">
               <?php


				
               date_default_timezone_set('Europe/Berlin');
               $stunde = date("H");
               if ($stunde >= 6 && $stunde < 12) {
                  $begruessung = str_replace('{username}', $userName, $translations['dash_page']['welcome_text_morning']);
               } elseif ($stunde >= 12 && $stunde < 18) {
                  $begruessung = str_replace('{username}', $userName, $translations['dash_page']['welcome_text_afternoon']);
               } elseif ($stunde >= 18 && $stunde < 24) {
                  $begruessung = str_replace('{username}', $userName, $translations['dash_page']['welcome_text_evening']);
               } else {
                  $begruessung = str_replace('{username}', $userName, $translations['dash_page']['welcome_text_night']);
               }

               $productCount = $result->num_rows;

               echo '<h1 class="text-center mt-5">' . $begruessung . '</h1>';
               ?>

<div class="card-body px-5">

   <h5 class="text pt-4">
      <?= str_replace('{websiteName}', '<strong>' . $name . '</strong>', $translations['dash_page']['welcome_text']) ?>
   </h5>
   
   <p class="text opacity-8">
      <?= str_replace('{websiteName}', $name, $translations['dash_page']['text_info']) ?>
            </p>
   <p class="text opacity-8">
      <?= str_replace('{here}', '<a href="changelogs/">' . $translations['dash_page']['changelogs_here'] . '</a>', $translations['dash_page']['text_changelogs']) ?>

   </p>
</div>
               <center>
<?php

// Zeitzone setzen
date_default_timezone_set('Europe/Berlin');

$today = date('m-d');
$sql = "SELECT name FROM holydays WHERE DATE_FORMAT(date_from, '%m-%d') <= '$today' AND DATE_FORMAT(date_to, '%m-%d') >= '$today'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $holiday_name = $row["name"];
        switch($holiday_name) {
            case "christmas":
                echo '<p class="text-success opacity-8">Frohe Weihnachten! 🎅</p>';
                break;
            case "easter":
                echo '<p class="text-info opacity-8">Frohe Ostern! 🐰</p>';
                break;
            default:
                echo '<p class="text-info opacity-8">Heute ist ein Feiertag: ' . $holiday_name . '</p>';
        }
    }
} else {
}
$conn->close();

?>



                  




               </center>
              
            </div>



         </div>

      </div>
   </div><br>
</section>

<?php
require 'db_connection.php';

echo '<div class="slice slice-sm bg-section-secondary">';
echo '   <div class="container">';
echo '      <div class="row justify-content-center">';
echo '         <div class="col-lg-9">';
echo '            <div class="row">';
echo '               <div class="col-lg-12">';
echo '                  <h5>' . ($translations['dash_page']['table']['title']) . '</h5>';
echo '                  <div class="table-responsive">';
echo '                     <table class="table table-cards align-items-center" id="files-table">';
echo '                        <thead>';

// Im <thead>-Bereich immer "Kategorie", "Dateiname", "Dateigröße" und "Hochgeladen" anzeigen
if (!isset($_GET['cat'])) {
   // In der Kategorieliste nur "Kategorie" im <thead>-Bereich anzeigen
   echo '                           <tr>';

   echo ' <th scope="col">' . ($translations['dash_page']['table']['category']['text_1']) . '</th>';
   echo ' <th scope="col">' . ($translations['dash_page']['table']['category']['text_2']) . '</th>';
   echo '                           </tr>';
} else {
   // In der Dateiliste "Dateiname", "Dateigröße" und "Hochgeladen" im <thead>-Bereich anzeigen
   echo '                           <tr>';
   echo ' <th scope="col">' . ( $translations['dash_page']['table']['files']['text_1']) . '</th>';
   echo ' <th scope="col">' . ($translations['dash_page']['table']['files']['text_2']) . '</th>';
   echo ' <th scope="col">' . ($translations['dash_page']['table']['files']['text_3']) . '</th>';
   echo '                           </tr>';
}

echo '                        </thead>';
echo '                        <tbody class="list">';
echo '                           <tr id="loading-row"><td colspan="3"><div class="text-center"><div class="spinner-border" role="status"></div>';

echo '                        </tbody>';
echo '                     </table>';
echo '                  </div>';
echo '               </div>';
echo '            </div>';
echo '         </div>';
echo '      </div>';
echo '   </div>';
echo '</div>';
$conn->close();
?>

<script>
$(document).ready(function() {
	function generateBadge(released) {
		if (released === 'Yes') {
			return '<span class="badge badge-success badge-pill"><i class="fa-solid fa-check"></i> ' + '<?php echo $translations['dash_page']['table']['files']['released_yes']; ?>' + '</span>';
		} else if (released === 'No') {
			return '<span class="badge badge-secondary badge-pill"><i class="fa-solid fa-xmark"></i> ' + '<?php echo $translations['dash_page']['table']['files']['released_no']; ?>' + '</span>';
		} else if (released === 'disabled') {
			return '<span class="badge badge-warning badge-pill"><i class="fa-solid fa-ban"></i> ' + '<?php echo $translations['dash_page']['table']['files']['released_disabled']; ?>' + '</span>';
		} else {
			return '<span class="badge badge-primary badge-pill"><i class="fa-solid fa-certificate"></i> ' + '<?php echo $translations['dash_page']['table']['files']['released_only_verfied']; ?>' + '</span>';
		}
	}


    function loadCategories() {
        $.ajax({
            url: 'actions/ajax_get_category.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('.list').empty();
                
                $.each(response, function(index, categoryInfo) {
                    var link = '<a href="?cat=' + encodeURIComponent(categoryInfo.category) + '">' + categoryInfo.category + '</a>';
                    var fileCount = '<span class="file-count">' + categoryInfo.file_count + ' Dateien</span>';
                    var row = '<tr><th>' + link + '</th><td>' + fileCount + '</td></tr>';
                    $('.list').append(row);
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                setTimeout(loadCategories, 5000);
            }
        });
    }

    function loadFiles(category) {
        $.ajax({
            url: 'actions/ajax_get_files.php',
            type: 'GET',
            data: { cat: category },
            dataType: 'json',
            beforeSend: function() {
                $('#loading-row td').html('<div class="text-center"><div class="spinner-border" role="status"></div>');
            },
            success: function(response) {
                $('.list').empty();
                
                if (Array.isArray(response)) {
                    $.each(response, function(index, file) {
                        var row = '<tr>' +
                            '<th><a href="download?file=' + file.name + '">' + file.name + '</a></th>' +
                            '<td>' + file.size + '</td>' +
                            '<td>' + file.date + '</td>' +
                            '<td>' + generateBadge(file.released) + '</td>' +
                            '</tr>';
                        $('.list').append(row);
                    });
                } else {
                    loadCategories();
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                setTimeout(function() { loadFiles(category); }, 5000);
            }
        });
    }

    var categoryParam = '<?php echo isset($_GET['cat']) ? $_GET['cat'] : ''; ?>';
    if (categoryParam !== '') {
        loadFiles(categoryParam);
    } else {
        loadCategories();
    }
});
</script>







<?php
include 'settings/footer.php';
?>