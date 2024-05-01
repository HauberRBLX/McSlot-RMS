<!-- profile.php -->
<html>
<meta name='og:title' content='Codes • Admin'>
<meta name="description" content="Codes | DLSystem">
<meta name="keywords" content="DLSystem">
<meta name="author" content="PvPMaster0001">
<meta name='copyright' content='DLSystem'>

<?php
session_start();
require '../db_connection.php';
include '../database/db_cdn.php';

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
    // Benutzer ausloggen
    session_destroy();
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

include '../settings/config.php';
include '../settings/head_admin.php';
include '../settings/header_admin.php';

// Abfrage für alle Download-Logs
$sql = "SELECT * FROM files_history";
$result = $remote_conn->query($sql);

?>

<title>DL-Logs &mdash; Admin</title>

<body>
    <section class="pt-5 bg-section-secondary">
        <div class=container>
            <div class="row justify-content-center">
                <div class=col-lg-9>
                    <div class="row align-items-center">
                        <div class=col>
                            <span class=surtitle>Admin</span>
                            <h1 class="h2 mb-0">Download-Logs</h1>
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
                    <div class=row>
                        <div class=col-lg-12>
                            <center>
                                <h3>Download Logs</h3>
                            </center>
                          <div class="table-responsive">
                            <table class="table table-cards align-items-center">
                                <thead>
                                    <tr>
                                        <th scope="col">Benutzername</th>
                                        <th scope="col">Download Key</th>
                                        <th scope="col">CDN</th>
                                        <th scope="col">File</th>
                                        <th scope="col">Datum</th>
                                    </tr>
                                </thead>
                                <tbody>
<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $username = $row['user'];
        
        $sql_user = "SELECT name FROM benutzer WHERE id = '$username'";
        $result_user = $conn->query($sql_user);
        
        if ($result_user && $result_user->num_rows > 0) {
            $user_data = $result_user->fetch_assoc();
            echo "<tr>";
            echo "<th><a href='users?search=" . $user_data['name'] . " '>" . $user_data['name'] . "</a></th>";
            echo "<td>" . $row['download_key'] . "</td>";
            echo "<td>" . explode('.cdn.mcslot.net', $row['cdn'])[0] . "</td>";
            echo "<td>" . pathinfo($row['file'], PATHINFO_FILENAME) . "</td>";
            echo "<td>" . $row['date'] . "</td>";
            echo "</tr>";
        }
    }
} else {
    echo "<tr><td colspan='4'>Keine Daten gefunden</td></tr>";
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
