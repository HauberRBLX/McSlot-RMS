<!-- users.php -->
<meta name='og:title' content='DLSystem • Admin'>
<meta name="description" content="Users | DLSystem">
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
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . '' : $siteurl . $admin_directory));
    exit;
}

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'];
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if ($user['gesperrt'] == 1) {
    // Benutzer ausloggen
    session_destroy();
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}
?>

<?php
include '../settings/config.php';
include '../settings/head_admin.php';
include '../settings/header_admin.php';

?>
<title>Benutzer &mdash; Admin</title>
<section class="pt-5 bg-section-secondary">
    <div class=container>
        <div class="row justify-content-center">
            <div class=col-lg-9>
                <div class="row align-items-center">
                    <div class=col>
                        <span class=surtitle>Admin</span>
                        <h1 class="h2 mb-0">Benutzerverwaltung</h1>
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
                <div class=row>

                    <div class=col-lg-12>
                        <center>
                            <h3>Benutzerverwaltung</h3>
                        </center>

<?php if ($user['role'] === 'Admin' || $user['role'] === 'Owner') { ?>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCDNHostModal">
        Benutzer hinzufügen
    </button>
<?php } ?>
                        <hr>
                        <form method="get" action="" autocomplete="off">
                            <div class="form-row align-items-center">
                                <div class="col-auto">
                                    <label class="sr-only" for="search">Suchen</label>
                                    <input type="text" class="form-control mb-2" id="search" name="search"
                                        placeholder="Benutzer suchen">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary mb-2">Suchen</button>
                                </div>
                            </div>
                        </form>

<?php if ($user['role'] === 'Admin' || $user['role'] === 'Owner') { ?>
                            <div class="modal fade" id="addCDNHostModal" tabindex="-1" role="dialog"
                            aria-labelledby="addCDNHostModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addUserModalLabel">Benutzer hinzufügen</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Formular für das Hinzufügen eines Benutzers -->
                                        <form action="actions/add_user.php" method="post">
                                            <div class="form-group">
                                                <label for="newUserName">Benutzername</label>
                                                <input type="text" class="form-control" name="newUserName" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="newUserPassword">Passwort</label>
                                                <input type="password" class="form-control" name="newUserPassword"
                                                    required>
                                            </div>
                                            <div class="form-group">
                                                <label for="newUserAdminRights">Rolle</label>
                                                <select class="form-control" name="newUserAdminRights">
                                                    <option value="0">Mitglied</option>
                                                    <option value="1">Admin</option>
                                                </select>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Schließen</button>
                                                <button type="submit" class="btn btn-primary">Benutzer
                                                    hinzufügen</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
<?php } ?>


                        <?php
                        $itemsPerPage = 10;
                        $page = isset($_GET['page']) ? $_GET['page'] : 1;
                        $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

                        $sql = "SELECT COUNT(*) FROM benutzer WHERE name LIKE ? OR kontonummer LIKE ?";
                        $stmt = $conn->prepare($sql);
                        $searchTerm = "%$searchTerm%";
                        $stmt->bind_param("ss", $searchTerm, $searchTerm);
                        $stmt->execute();
                        $totalRecords = $stmt->get_result()->fetch_row()[0];

                        $totalPages = ceil($totalRecords / $itemsPerPage);

                        if ($page < 1) {
                            $page = 1;
                        } elseif ($page > $totalPages) {
                            $page = $totalPages;
                        }

                        $offset = ($page - 1) * $itemsPerPage;
                        $sql = "SELECT * FROM benutzer WHERE name LIKE ? OR kontonummer LIKE ? ORDER BY created DESC LIMIT ?, ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ssii", $searchTerm, $searchTerm, $offset, $itemsPerPage);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        echo '<div class="table-responsive">';
                        echo '<table class="table table-cards align-items-center">';
                        echo '<thead><tr><th scope="col">#</th><th scope="col">Benutzername</th><th scope="col">Rolle</th><th scope="col">Status</th></tr></thead>';
                        echo '<tbody class="list">';

                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<th>" . $row['kontonummer'] . "</th>"; // Display kontonummer
                            echo '<td>' . $row['name'];
                            if ($row['verified'] == 1) {
                                echo ' <i class="fa-solid fa-badge-check" style="color: #5CC9A7;"></i>';
                            }
                            echo '</td>';
							echo '<td class="' . ($row['role'] == 'Owner' ? 'text-danger' : ($row['role'] == 'Admin' ? 'text-danger' : ($row['role'] == 'Supporter' ? 'text-primary' : ''))) . '">' . $row['role'] . '</td>';
                            echo "<td><span class='badge badge-dot'><i class='" . ($row['gesperrt'] ? 'bg-danger' : ($row['deleted'] ? 'bg-warning' : 'bg-success')) . "'></i>" . ($row['gesperrt'] ? 'Gesperrt' : ($row['deleted'] ? 'Löschauftrag' : 'Aktiv')) . "</span></td>";
                            echo "<td class='text-right'>";
                            echo '<div class="float-right">';
                            echo '<a class="mr-3" data-toggle="modal" href="#infoUser' . $row['id'] . '"><i class="fas fa-info-circle"></i></a>';
                          if ($user['role'] === 'Admin' || $user['role'] === 'Owner') {
                              echo '<a class="mr-3" href="edit_user?kn=' . $row['kontonummer'] . '"><i class="fas fa-edit"></i></a>';
                          }
                          if ($user['role'] === 'Admin' || $user['role'] === 'Owner') {
                              echo '<a class="text-danger" href="javascript:void(0);" onclick="confirmDelete(' . $row['id'] . ')"><i class="fas fa-trash-alt"></i></a>';
                          }

                            echo '</div>';
                            echo '</td>';

                            echo "</tr>";
                            
                            ////////////////////////
                            //  INFO USER MODAL	//
                            ////////////////////////
                            echo '<div class="modal fade" id="infoUser' . $row['id'] . '" tabindex="-1" role="dialog" aria-labelledby="infoUserLabel' . $row['id'] . '" aria-hidden="true">';
                            echo '<div class="modal-dialog" role="document">';
                            echo '<div class="modal-content">';
                            echo '<div class="modal-header">';
                            echo '<h5 class="modal-title" id="editUserLabel' . $row['id'] . '">Benutzer ' . $row['name'];
                            if ($row['verified'] == 1) {
                                echo ' <i class="fa-solid fa-badge-check" style="color: #5CC9A7;"></i>';
                            } 

                            echo ' Informationen</h5>';
                            echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
                            echo '<span aria-hidden="true">&times;</span>';
                            echo '</button>';
                            echo '</div>';
                            echo '<div class="modal-body">';
// Überprüfen, ob der Benutzer die Rolle "Supporter" hat
if ($user['role'] === 'Supporter') {
    // Überprüfen, ob eine IP-Adresse vorhanden ist
    if ($row['letzte_ip'] !== null) {
        // Überprüfen, ob es sich um eine IPv4-Adresse handelt
        if (filter_var($row['letzte_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // Maskieren der letzten Teil der IPv4-Adresse
            $ipv4_parts = explode('.', $row['letzte_ip']);
            $masked_ip = $ipv4_parts[0] . '.' . $ipv4_parts[1] . '.' . $ipv4_parts[2] . '.*';
        }
        // Überprüfen, ob es sich um eine IPv6-Adresse handelt
        elseif (filter_var($row['letzte_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Maskieren der letzten beiden Teile der IPv6-Adresse
            $ipv6_parts = explode(':', $row['letzte_ip']);
            $count = count($ipv6_parts);
            $masked_ip = '';
            // Wenn die Anzahl der Teile größer als 2 ist, maskieren wir die letzten beiden Teile
            if ($count > 2) {
                for ($i = 0; $i < $count - 2; $i++) {
                    $masked_ip .= $ipv6_parts[$i] . ':';
                }
                $masked_ip .= '****:****';
            } else {
                // Wenn weniger als 2 Teile vorhanden sind, zeigen wir die vollständige Adresse an
                $masked_ip = $row['letzte_ip'];
            }
        } else {
            // Wenn weder IPv4 noch IPv6 vorliegt, markieren Sie sie als nicht aufgezeichnet
            $masked_ip = 'Nicht aufgezeichnet';
        }
        // Anzeigen der modifizierten IP-Adresse
        echo '<center><label for="userip">Letzte IP-Adresse: <strong>' . $masked_ip . '</strong></label></center>';
    } else {
        // Wenn keine IP-Adresse vorhanden ist, markieren Sie sie als nicht aufgezeichnet
        echo '<center><label for="userip">Letzte IP-Adresse: <strong class="text-danger">Nicht aufgezeichnet</strong></label></center>';
    }
} else {
    // Wenn der Benutzer keine Supporter-Rolle hat, die normale Anzeige durchführen
    echo '<center><label for="userip">Letzte IP-Adresse: <strong class="' . ($row['letzte_ip'] !== null ? '' : 'text-danger') . '">' . ($row['letzte_ip'] !== null ? $row['letzte_ip'] : 'Nicht aufgezeichnet') . '</strong></label></center>';
}
                            echo '<center><label for="userName">Kontonummer: <strong>' . $row['kontonummer'] . '</strong></label></center>';
                          	echo '<center><label for="userRole">Rolle: <strong>' . ($row['role'] === 'Owner' ? '<span class="text-danger">Owner</span>' : ($row['role'] === 'Admin' ? '<span class="text-danger">Administrator</span>' : ($row['role'] === 'Mitglied' ? '<span class="text-primary">Mitglied</span>' : $row['role']))) . '</strong></label></center>';

                            $createdDate = date_create($row['created']);
                            $formattedCreatedDate = date_format($createdDate, 'd.m.Y H:i:s');
                            echo '<center><label for="userName">Erstellt am: <strong class="' . ($row['created'] !== null ? '' : 'text-danger') . '">' . ($row['created'] !== null ? $formattedCreatedDate : 'Nicht aufgezeichnet') . '</strong>';
                            if ($row['verified'] == 1) {
                                echo '<br><br><center><span class="badge badge-success"><i class="fa-solid fa-badge-check"></i> Verifizierter Account</span></center>';
                            } else {
                            	echo '<br><br><center><span class="badge badge-danger"><i class="fa-solid fa-brake-warning"></i> Nicht Verifizierter Account</span></center>';
                             }
                            if ($row['deleted'] == 1) {
                                echo '<br><center><span class="badge badge-danger"><i class="fa-solid fa-trash"></i> Löschauftrag gesendet</span></center>';
                            } else {
                                echo '<br><center><span class="badge badge-success"><i class="fa-regular fa-circle-check"></i> Kein Löschauftrag</span></center>';
                            }
                          if ($row['gesperrt'] == 1) {
    echo '<br><center><span class="badge badge-danger"><i class="fa-solid fa-lock"></i> Gesperrt</span></center>';
    if (!empty($row['sperrgrund'])) {
        echo '<br><center><label for="sperrgrund">Sperrgrund: <strong>' . $row['sperrgrund'] . '</strong></label></center>';
    }
}
                            echo '</div>';
                            echo '<div class="modal-footer">';
                            echo '<button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa-solid fa-circle-xmark"></i> Schließen</button>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';

                        }
                        echo '</tbody></table>';
                        echo '</div>';

                        echo '</div>';
                        echo '</div>';

                        // Pagination-Links anzeigen
                        echo '<br>';
                        echo '<nav aria-label="Page navigation example"><ul class="pagination">';
                        if ($page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
                        }
                        for ($i = 1; $i <= $totalPages; $i++) {
                            if ($i == $page) {
                                echo '<li class="page-item active"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                            } else {
                                echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                            }
                        }
                        if ($page < $totalPages) {
                            echo '<li class="page-item"><a class="page-link" href="?page=' . ($page + 1) . '" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
                        }
                        echo '</ul></nav>';


                        ?>


                    </div>
                </div>
            </div>
        </div>
    </div>

  <script>
    function confirmDelete(userId) {
        if (confirm('Bist du dir sicher, dass du diesen Benutzer löschen möchtest?')) {
            window.location.href = 'actions/delete_user.php?user_id=' + userId;
        }
    }
</script>


    <?php
    include '../settings/footer.php';
    ?>