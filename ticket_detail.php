<!-- ticket_detail.php -->

<?php

session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
    exit;
}

if (!isset($_GET['ticket_id'])) {
    header("Location: " . ($phpenable === 'true' ? $ticket_url . '.php' : $ticket_url));
    exit;
}


$ticketId = $_GET['ticket_id'];
$userId = $_SESSION['user_id']; // Benutzer-ID aus der Sitzung holen

// Überprüfen, ob der Benutzer das Recht hat, das Ticket zu sehen
$sqlCheckUser = "SELECT id FROM tickets WHERE id = ? AND user_id = ?";
$stmtCheckUser = $conn->prepare($sqlCheckUser);
$stmtCheckUser->bind_param("ii", $ticketId, $userId);
$stmtCheckUser->execute();
$stmtCheckUser->store_result();
include 'settings/config.php';
include 'settings/head.php';
include 'settings/header.php';
if ($stmtCheckUser->num_rows <= 0) {
    echo '<h1 class="text-center mt-5">Sie haben keine Berechtigung, dieses Ticket anzuzeigen.</h1>';
    echo '<meta http-equiv="refresh" content="0;url=' . ($phpenable === 'true' ? $ticket_url . '.php' : $ticket_url) . '">';
    exit;
}

$stmtCheckUser->close();

$sql = "SELECT subject, status, created_at FROM tickets WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticketId);
$stmt->execute();
$stmt->bind_result($subject, $status, $createdAt);



if ($stmt->fetch()) {
    echo '<h1 class="text-center mt-5">' . $subject . '</h1>';
    if ($status == "offen") {
        $badgeClass = "bg-success xl";
        $statusText = ($translations['ticket_detail']['status']['open']);
    } elseif ($status == "geschlossen") {
        $badgeClass = "bg-danger";
        $statusText = ($translations['ticket_detail']['status']['closed']);
    } elseif ($status == "bearbeitung") {
        $badgeClass = "bg-warning";
        $statusText = ($translations['ticket_detail']['status']['in_progress']);
    } else {
        $badgeClass = "bg-secondary";
        $statusText = ($translations['ticket_detail']['status']['unknown']);
    }

    echo '<center><span class="badge badge-dot badge-lg"><i class="' . $badgeClass . '"></i>' . $statusText . '</span>';

    $datetime = new DateTime($createdAt, new DateTimeZone('Europe/Berlin'));
    $formattedDate = $datetime->format('d.m.Y H:i');

    echo '<p>'. ($translations['ticket_detail']['created']) .' ' . $formattedDate . '</p>';
    echo '<p>'. ($translations['ticket_detail']['ticket_id']) .' <strong>' . $ticketId . '</strong></p></center>';
    echo '</div>';
} else {
    echo '<h1 class="text-center mt-5">Ticket wurde nicht gefunden</h1>';
    echo '<meta http-equiv="refresh" content="0;url=' . ($phpenable === 'true' ? $ticket_url . '.php' : $ticket_url) . '">';
    exit;
}

$stmt->close();

?>

<title>
    <?= str_replace('{websiteName}', $name, $translations['ticket_detail']['title']) ?>
</title>

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

                    <h2 class="text-center mt-5"> <?= ($translations['ticket_detail']['messages']) ?></h2>
                    <?php
                    if ($status != "geschlossen") {

                        // Antwortformular nur anzeigen, wenn das Ticket nicht geschlossen ist
                        echo '<form action="actions/add_reply.php" method="post">';
                        echo '<input type="hidden" name="ticket_id" value="' . $ticketId . '">';
                        echo '<div class="form-group">';
                        echo '<label for="reply_message">'. ($translations['ticket_detail']['answer']['field']) .'</label>';
                        echo '<textarea class="form-control" id="reply_message" name="reply_message" rows="4" resize="none" required placeholder="Guten Tag,"></textarea>';
                        echo '</div>';
                        echo '<button type="submit" class="btn btn-primary">'. ($translations['ticket_detail']['answer']['send']) .'</button>';
                        echo '</form>';

                        echo '<form action="actions/close_ticket.php" method="post">';
                        echo '<input type="hidden" name="ticket_id" value="' . $ticketId . '">';
                        echo '<button type="submit" class="btn btn-danger">'. ($translations['ticket_detail']['answer']['close']) .'</button>';
                        echo '</form>';
                    } else {
                        echo '<center><button class="btn btn-secondary btn-block" disabled>'. ($translations['ticket_detail']['closed']) .'</button></center>';
                        echo '<br>';
                    }
                    ?>
                    <br>
                    <div class="table-responsive">
                        <tbody class="list">

                            <?php
                            $sql = "SELECT benutzer.name, message, created_at, team_reply FROM ticket_replies 
INNER JOIN benutzer ON ticket_replies.user_id = benutzer.id
WHERE ticket_id = ?
ORDER BY created_at DESC";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $ticketId);
                            $stmt->execute();
                            $stmt->bind_result($userName, $message, $replyCreatedAt, $teamReply);

                            $isNewMessage = true; // Annahme: Die erste Nachricht ist neu
                            
                            while ($stmt->fetch()) {
                                echo '<div class="media media-comment">';
                                echo '<img alt="Image placeholder" class="rounded-circle shadow mr-4" src="../assets/img/user.png" style="width:58px">';

                                echo '<div class="media-body">';
                                echo '<div class="media-comment-bubble left-top">';
                                echo '<h6 class="mt-0">' . htmlspecialchars($userName) . ')';
                                echo '<p class="text-sm lh-160">' . nl2br(htmlspecialchars($message)) . '</p>';
                                echo '<div class="icon-actions">';
                                echo '<a>';

                                $replyDateTime = new DateTime($replyCreatedAt, new DateTimeZone('Europe/Berlin'));
                                $currentDateTime = new DateTime();
                                $interval = $currentDateTime->diff($replyDateTime);

                                echo '<span class="text-muted">' . $replyDateTime->format('d.m.Y H:i') . '</span>';

                                if ($isNewMessage) {
                                    echo '<a>';
                                    echo '<span class="badge badge-primary">'. ($translations['ticket_detail']['answer']['new']) .'</span>';
                                    echo '</a>';
                                    $isNewMessage = false;
                                }

                                if ($teamReply == 1) {
                                    echo '<a>';
                                    echo '<span class="badge badge-dark">'. ($translations['ticket_detail']['answer']['team']) .'</span>';
                                    echo '</a>';
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
<br>
                </tbody>

            </div>

        </div>
    </div>
    </div>

</section>
<br><br><br><br>

<?php
include 'settings/footer.php';
?>