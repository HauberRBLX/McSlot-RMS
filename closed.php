<!-- reactive.php -->

<?php
session_start();
require 'db_connection.php';

?>

<?php
include 'settings/config.php';
include 'settings/head.php';
?>

<title>Closed &mdash;
    <?= $name ?>
</title>
<br><br>
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

        <div class="card mb-n7 position-relative zindex-100">
            <h1 class="text-center mt-5">MCSLOT SCHLIEßT</h1>
            <div class="card-body px-5">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-6 text-center">
                            <p class="text opacity-8">
                            Liebe McSlot Community,<br><br>
wir wollen euch darüber informieren, dass McSlot seine Tore schließen wird. Seit Monaten haben wir euch die Möglichkeit geboten, unsere selbst entwickelten Systeme herunterzuladen und zu nutzen, ohne dabei auf unser Hosting angewiesen zu sein. Diese Entscheidung fiel uns nicht leicht, aber aufgrund verschiedener Umstände sehen wir uns gezwungen, diesen Schritt zu gehen.<br><br>
Alle Accounts auf unserer Plattform werden permanent gelöscht. Wir möchten euch daher bitten, sicherzustellen, dass ihr alle benötigten Daten oder Systeme vor dem 10. Mai 2024 herunterladet und sichert. Nach diesem Datum wird die Website offline genommen und nicht mehr erreichbar sein.<br><br>
Wir möchten uns bei euch für eure Treue und Unterstützung während der Zeit bedanken, in der McSlot aktiv war. Es war uns eine Freude, Teil dieser Community zu sein und euch mit unseren Services zu unterstützen.<br><br>
Mit freundlichen Grüßen,<br>
Das McSlot Team


                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div><br>