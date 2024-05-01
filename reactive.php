<!-- reactive.php -->

<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
    exit;
}
$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'];
$result = $conn->query($sql);
$user = $result->fetch_assoc();

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

$userId = $_SESSION['user_id'];
$sql = "SELECT deleted FROM benutzer WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($isDeleted);
$stmt->fetch();
$stmt->close();

if ($isDeleted == 1) {
} elseif ($isDeleted == 0) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $dash_url . '.php' : $siteurl . $dash_url));
    exit;
}

if (empty($userName)) {
    session_destroy();
    header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
    exit;
}

$creationDateQuery = "SELECT creation_date FROM queue WHERE user_id = ?";
$stmt = $conn->prepare($creationDateQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($creationDate);
$stmt->fetch();
$stmt->close();

// Berechne die verbleibenden Tage bis zur Löschung
$deleteDate = strtotime($creationDate) + (7 * 24 * 60 * 60); // Hinzufügen von 7 Tagen in Sekunden
$remainingDays = ceil(($deleteDate - time()) / (24 * 60 * 60)); // Berechne die verbleibenden Tage

?>

<?php
include 'settings/config.php';
include 'settings/head.php';
?>

<title>Reaktivieren &mdash;
    <?= $name ?>
</title><br><br>
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
            <h1 class="text-center mt-5"><?= ( $translations['reactive']['title']) ?></h1>
            <div class="card-body px-5">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-6 text-center">
                            <p class="text opacity-8">
                            <?= str_replace('{days}', '<strong>' . $remainingDays . '</strong>', str_replace('{username}', $userName, $translations['reactive']['text'])) ?>


                            </p>
                            <form method="post" action="actions/reactive_account.php" id="reactivateAccountForm">
                                <button type="submit" class="btn btn-success btn-icon hover-translate-y-n3">
                                    <span class="btn-inner--icon">
                                        <i class="fa-solid fa-recycle"></i>
                                    </span>
                                    <span class="btn-inner--text"><?= ( $translations['reactive']['button_reactive']) ?></span>
                                </button>
                            </form><br><br>
                            <p class="text opacity-8">
                            <?= ( $translations['reactive']['text_2']) ?>
                            </p>
                            <a href="<?= $logout_url ?>" class="btn btn-danger btn-icon hover-translate-y-n3">
                                <span class="btn-inner--icon">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                </span>
                                <span class="btn-inner--text"><?= ( $translations['reactive']['button_logout']) ?></span>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div><br><br><br><br>
<?php
include 'settings/footer.php';
?>