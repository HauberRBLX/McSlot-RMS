<!-- profile.php -->
<html>
<meta name='og:title' content='DLSystem • Admin'>
<meta name="description" content="Tickets | DLSystem">
<meta name="keywords" content="DLSystem">
<meta name="author" content="PvPMaster0001">
<meta name='copyright' content='DLSystem'>

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

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'];
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if ($user['gesperrt'] == 1) {
    session_destroy();
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

$datetime = new DateTime($createdAt, new DateTimeZone('Europe/Berlin'));

include '../settings/config.php';
include '../settings/head_admin.php';
include '../settings/header_admin.php';


?>
<title>Tickets &mdash; Admin</title>

<body>
    <section class="pt-5 bg-section-secondary">
        <div class=container>
            <div class="row justify-content-center">
                <div class=col-lg-9>
                    <div class="row align-items-center">
                        <div class=col>
                            <span class=surtitle>Admin</span>
                            <h1 class="h2 mb-0">Tickets</h1>
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

    <div class="slice slice-sm bg-section-secondary">
        <div class=container>

            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="row">
                        <div class="col-lg-12">
                            <center>
                                <h3>Tickets</h3>
                            </center>
                            <div class="row mt-4">
                                <div class="col-lg-12">
                                    <form action="ticket_detail.php" method="GET">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Ticket-ID eingeben"
                                                name="ticket_id">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="submit">Suchen</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-cards align-items-center">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Status</th>
                                            <th>Betreff</th>
                                            <th>Erstellt am</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sqlTickets = "SELECT id, user_id, subject, message, status, DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') as formatted_created_at FROM tickets ORDER BY FIELD(status, 'offen', 'bearbeitung') DESC, created_at DESC";
                                        $resultTickets = $conn->query($sqlTickets);

                                        if ($resultTickets->num_rows > 0) {
                                            while ($row = $resultTickets->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<th>" . $row['id'] . "</th>";

                                                // Überprüfen des Status und Zuweisen der entsprechenden Klassen und Texte
                                                $badgeClass = "";
                                                $statusText = "";
                                                if ($row['status'] == "offen") {
                                                    $badgeClass = "bg-success xl";
                                                    $statusText = "Offen";
                                                } elseif ($row['status'] == "geschlossen") {
                                                    $badgeClass = "bg-danger";
                                                    $statusText = "Geschlossen";
                                                } elseif ($row['status'] == "bearbeitung") {
                                                    $badgeClass = "bg-warning";
                                                    $statusText = "In Bearbeitung";
                                                } else {
                                                    $badgeClass = "bg-secondary";
                                                    $statusText = "Unbekannt";
                                                }

                                                echo '<td><span class="badge badge-dot"><i class="' . $badgeClass . '"></i>' . $statusText . '</span></td>';
                                                echo "<td>" . $row['subject'] . "</td>";
                                                echo "<td>" . $row['formatted_created_at'] . "</td>"; // Verwende das formatierte Datum
                                                echo "<td class='text-right'>";
                                                echo '<div class="dropdown float-right">
                    <a href="' . ($phpenable === 'true' ? $ticketview_url . '.php?ticket_id=' . $row['id'] : $ticketview_url . '?ticket_id=' . $row['id']) . '" role="button">
                        <i class="fas fa-eye"></i>
                    </a>';
                                                echo "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4'>Keine Tickets gefunden.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php
    include '../settings/footer.php';
    ?>
</body>

</html>