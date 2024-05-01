<!-- admin_ticket_detail.php -->
<?php

session_start();
require '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'] . " AND (role = 'Supporter' OR role = 'Admin' OR role = 'Owner')";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "Du hast keine Berechtigung für diese Aktion.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

if (!isset($_GET['ticket_id'])) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $tickets_url_admin . '.php' : $siteurl . $admin_directory . $tickets_url_admin));
    exit;
}

$ticketId = $_GET['ticket_id'];
$userId = $_SESSION['user_id']; // Benutzer-ID aus der Sitzung holen

// Überprüfen, ob der Benutzer das Recht hat, das Ticket zu sehen (Annahme: Admins haben Zugriff auf alle Tickets)
$sqlCheckAdmin = "SELECT id FROM tickets WHERE id = ?";
$stmtCheckAdmin = $conn->prepare($sqlCheckAdmin);
$stmtCheckAdmin->bind_param("i", $ticketId);
$stmtCheckAdmin->execute();
$stmtCheckAdmin->store_result();

include '../settings/config.php';
include '../settings/head_admin.php';
include '../settings/header_admin.php';

if ($stmtCheckAdmin->num_rows <= 0) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

$stmtCheckAdmin->close();

$sql = "SELECT subject, status, created_at FROM tickets WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticketId);
$stmt->execute();
$stmt->bind_result($subject, $status, $createdAt);

if ($stmt->fetch()) {
    echo '<h1 class="text-center mt-5">' . $subject . '</h1>';
    if ($status == "offen") {
        $badgeClass = "bg-success xl";
        $statusText = "Offen";
    } elseif ($status == "geschlossen") {
        $badgeClass = "bg-danger";
        $statusText = "Geschlossen";
    } elseif ($status == "bearbeitung") {
        $badgeClass = "bg-warning";
        $statusText = "In Bearbeitung";
    } else {
        $badgeClass = "bg-secondary";
        $statusText = "Unbekannt";
    }

    echo '<center><span class="badge badge-dot badge-lg"><i class="' . $badgeClass . '"></i>' . $statusText . '</span>';

    $datetime = new DateTime($createdAt, new DateTimeZone('Europe/Berlin'));
    $formattedDate = $datetime->format('d.m.Y H:i');

    echo '<p>Erstellt am: ' . $formattedDate . '</p>';
    echo '<p>Ticket-ID: <strong>' . $ticketId . '</strong></p></center>';
    echo '</div>';
} else {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $tickets_url_admin . '.php' : $siteurl . $admin_directory . $tickets_url_admin));
    exit;
}

$stmt->close();

?>
<title>Ticket Nachrichten &mdash; Admin</title>
<section class="pt-6 bg-section-secondary">
    <div class=container>
        <div class="row justify-content-center">
            <div class=col-lg-9>
                <div class="row align-items-center">
                    <div class=col>
                        <span class=surtitle>Admin</span>
                        <h1 class="h2 mb-0">Ticket Nachrichten</h1>
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
<section class="pt-5 bg-section-secondary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9" style="max-width: 100%;">
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
                <div class="card mb-n7 position-relative zindex-100">

                    <h2 class="text-center mt-5">Nachrichten
                    </h2>
                    <?php
                    if ($status != "geschlossen") {

                        // Antwortformular nur anzeigen, wenn das Ticket nicht geschlossen ist
                        echo '<form action="actions/add_reply.php" method="post">';
                        echo '<input type="hidden" name="ticket_id" value="' . $ticketId . '">';
                        echo '<div class="form-group">';
                        echo '<label for="reply_message">Antworten:</label>';
                        echo '<textarea class="form-control" id="reply_message" name="reply_message" rows="4" data-toggle="autosize" required placeholder="Guten Tag,"></textarea>';
                        echo '</div>';
                        echo '<button type="submit" class="btn btn-primary">Antworten</button>';
                        echo '</form>';

                        echo '<form action="actions/close_ticket.php" method="post">';
                        echo '<input type="hidden" name="ticket_id" value="' . $ticketId . '">';
                        echo '<button type="submit" class="btn btn-danger">Ticket Schließen</button>';
                        echo '</form>';

                    } else {
                        echo '<center><button class="btn btn-secondary btn-block" disabled>Ticket Geschlossen</button></center>';
                        echo '<br>';
                    }
                    ?>
                    <br>
                    <div class="table-responsive">
                        <tbody class="list">

<?php
$sql = "SELECT benutzer.name, benutzer.kontonummer, message, created_at, team_reply FROM ticket_replies 
INNER JOIN benutzer ON ticket_replies.user_id = benutzer.id
WHERE ticket_id = ?
ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticketId);
$stmt->execute();
$stmt->bind_result($userName, $kontonummer, $message, $replyCreatedAt, $teamReply);

$isNewMessage = true; // Annahme: Die erste Nachricht ist neu

while ($stmt->fetch()) {
    echo '<div class="media media-comment">';
    echo '<img alt="Image placeholder" class="rounded-circle shadow mr-4" src="../assets/img/user.png" style="width:58px">';

    echo '<div class="media-body">';
    echo '<div class="media-comment-bubble left-top" style="max-width: 80%;">';


    // Badge für Teammitglied neben dem Namen einfügen
    echo '<h6 class="mt-0">' . htmlspecialchars($userName) . ' (' . htmlspecialchars($kontonummer) . ')';
    if ($teamReply == 1) {
        echo ' <span class="badge badge-dark">Teammitglied</span>';
    }
    echo '</h6>';
    echo '<p class="text-sm lh-160">' . nl2br(htmlspecialchars($message)) . '</p>';
    echo '<div class="icon-actions">';
    echo '<a>';

    $replyDateTime = new DateTime($replyCreatedAt, new DateTimeZone('Europe/Berlin'));
    $currentDateTime = new DateTime();
    $interval = $currentDateTime->diff($replyDateTime);

    echo '<span class="text-muted">' . $replyDateTime->format('d.m.Y H:i') . '</span>';

    if ($isNewMessage) {
        echo '<a>';
        echo '<span class="badge badge-primary">Neuste Antwort</span>';
        echo '</a>';
        $isNewMessage = false;
    }

    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div><hr>';
}

$stmt->close();
?>

                    </div>
                </div>
<br><br><br><br>
                </tbody>

            </div>
<style>
.media-comment-bubble {
    max-width: 80%; /* Ändere die Breite je nach Bedarf */
    padding: 15px; /* Optional: Füge mehr Platz um den Inhalt herum hinzu */
}
		</style>
        </div>
    </div>
    </div>
</section>

<?php
include '../settings/footer.php';
?>