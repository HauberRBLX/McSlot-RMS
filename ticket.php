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

if ($user['gesperrt'] == 1) {
    session_destroy();
    header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
    exit;
}


$userId = $_SESSION['user_id'];

$ticketCreated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticketId = rand(10000000, 99999999);

    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $sql = "INSERT INTO tickets (id, user_id, subject, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $ticketId, $userId, $subject, $message);
    $stmt->execute();
    $stmt->close();

    $ticketCreated = true;
}

include 'settings/config.php';
include 'settings/head.php';
include 'settings/header.php';
?>

<title>Meine Tickets &mdash;
    <?= $name ?>
</title>


<br>
<section class="pt-5 bg-section-secondary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
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
                    <h1 class="text-center mt-5"><?= ($translations['ticket']['title']) ?></h1>

                    <?php
                    $userId = $_SESSION['user_id'];

                    $sql = "SELECT id, subject, status, created_at FROM tickets WHERE user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $stmt->bind_result($ticketId, $subject, $status, $createdAt);

                    if ($stmt->fetch()) {
                        ?>
                        <div class="table-responsive">
                            <table class="table table-cards align-items-center">
                                <thead>
                                    <tr>
                                        <th scope="col"><?= ($translations['ticket']['table']['text_1']) ?></th>
                                        <th scope="col"><?= ($translations['ticket']['table']['text_2']) ?></th>

                                        <th scope="col"><?= ($translations['ticket']['table']['text_3']) ?></th>
                                        <th scope="col"><?= ($translations['ticket']['table']['text_4']) ?></th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    <?php
                                    do {
                                        echo '<tr>';
                                        echo '<th>' . $ticketId . '</th>';
                                        if ($status == "offen") {
                                            $badgeClass = "bg-success xl";
                                            $statusText = ($translations['ticket']['status']['open']);
                                        } elseif ($status == "geschlossen") {
                                            $badgeClass = "bg-danger";
                                            $statusText = ($translations['ticket']['status']['closed']);
                                        } elseif ($status == "bearbeitung") {
                                            $badgeClass = "bg-warning";
                                            $statusText = ($translations['ticket']['status']['in_progress']);
                                        } else {
                                            $badgeClass = "bg-secondary";
                                            $statusText = ($translations['ticket']['status']['unknown']);
                                        }
                                        echo '<td><span class="badge badge-dot"><i class="' . $badgeClass . '"></i>' . $statusText . '</span></td>';

                                        echo '<td>' . $subject . '</td>';





                                        // Verwende DateTime für das gewünschte Zeitformat und die Zeitzone
                                        $datetime = new DateTime($createdAt, new DateTimeZone('Europe/Berlin'));
                                        echo '<td>' . $datetime->format('d.m.Y H:i') . '</td>';
                                        echo "<td class='text-right'>"; // Rechtsbündige Ausrichtung
                                        echo '<div class="dropdown float-right">
                                        <a href="' . ($phpenable === 'true' ? $ticketview_url . '.php?ticket_id=' . $ticketId : $ticketview_url . '?ticket_id=' . $ticketId) . '" role="button">
                        <i class="fas fa-eye"></i>
                    </a>';
                                        echo '</tr>';
                                    } while ($stmt->fetch());
                                    ?>
                                </tbody>
                            </table>
                        </div><br>
                        <?php
                    } else {
                        echo '<p class="text-center">' . ($translations['ticket']['ticket_not_create']) . '</p>';
                    }

                    $stmt->close();
                    ?>


                <center><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#ticketModal">
                <?= ($translations['ticket']['ticket_create_button']) ?>
                    </button></center><br>
                </div>

            </div>

        </div>

    </div>
</section>

<!-- Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="ticketModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= ( $translations['ticket']['ticket_create']['title']) ?></h5>

            </div>
            <div class="modal-body">
                <form action="actions/create_ticket.php" method="post">
                    <div class="form-group">
                        <label for="subject"><?= ( $translations['ticket']['ticket_create']['text_1']) ?>:</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message"><?= ( $translations['ticket']['ticket_create']['text_2']) ?>:</label>
                        <textarea class="form-control" id="message" name="message" rows="3" resize="none"
                            placeholder="<?= ( $translations['ticket']['ticket_create']['text_3']) ?>" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><?= ( $translations['ticket']['ticket_create']['text_4']) ?></button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><?= ( $translations['ticket']['ticket_create']['text_5']) ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include 'settings/footer.php';
?>