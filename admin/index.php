<!-- index.php -->
<html>
<meta name='og:title' content='Startseite • Admin'>
<meta name="description" content="Startseite | DLSystem">
<meta name="keywords" content="DLSystem">
<meta name="author" content="PvPMaster0001">
<meta name='copyright' content='DLSystem'>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $login_url . '.php' : $siteurl . $admin_directory . $login_url));
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

include '../settings/config.php';
include '../settings/head_admin.php';
include '../settings/header_admin.php';

if (!isset($userCount)) {
    $countUsersSql = "SELECT COUNT(*) as userCount FROM benutzer";
    $countResult = $conn->query($countUsersSql);
    $countData = $countResult->fetch_assoc();
    $userCount = $countData['userCount'];
}

if (!isset($userTicketCount)) {
    $countTicketSql = "SELECT COUNT(*) as userTicketCount FROM tickets WHERE status IN ('offen', 'bearbeitung')";
    $countTicketResult = $conn->query($countTicketSql);
    $countTicketData = $countTicketResult->fetch_assoc();
    $userTicketCount = $countTicketData['userTicketCount'];
}
	
if (!isset($emailCount)) {
    $countemailSql = "SELECT COUNT(*) as emailCount FROM email_delivered WHERE status IN ('delivered')";
    $countemailResult = $conn->query($countemailSql);
    $countemailData = $countemailResult->fetch_assoc();
    $emailCount = $countemailData['emailCount'];
}

if (!isset($deletedUserCount)) {
    $countDeletedUsersSql = "SELECT COUNT(*) as deletedUserCount FROM benutzer WHERE deleted = 1";
    $countDeletedUsersResult = $conn->query($countDeletedUsersSql);
    $countDeletedUserData = $countDeletedUsersResult->fetch_assoc();
    $deletedUserCount = $countDeletedUserData['deletedUserCount'];
}

?>
<title>Übersicht &mdash; Admin</title>

<body>
    <section class="pt-5 bg-section-secondary">
        <div class=container>
            <div class="row justify-content-center">
                <div class=col-lg-9>
                    <div class="row align-items-center">
                        <div class=col>
                            <span class=surtitle>Admin</span>
                            <h1 class="h2 mb-0">Übersicht</h1>
                        </div>
                    </div>
						<div class="row align-items-center mt-4">
                    <div class=col>
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
<?php 

                      
                      
                      include '../settings/navbar_admin.php';
                      ?>
                      
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="slice slice-sm bg-section-secondary">
        <div class="container">

          
<center><h2>Willkommen, <?php echo $user['name']; ?>!</h2>
  <?php echo generateBadge($user['role']); ?><br><br>
          </center>
          <div class="row justify-content-center">
    <div class="col-md-3">
        <div class="card shadow mb-5">
            <div class="card-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted font-weight-medium">Benutzer</p>
                        <h4 class="mb-0"><?php echo $userCount; ?></h4>
                    </div>

                    <div class="mini-stat-icon avatar-sm rounded-circle align-self-center">
                        <span class="avatar-title">
                            <i class="fa-duotone fa-users fa-3x text-primary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mini-stats-wid shadow mb-5">
            <div class="card-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted font-weight-medium">Offene Tickets</p>
                        <h4 class="mb-0"><?php echo $userTicketCount; ?></h4>
                    </div>

                    <div class="mini-stat-icon avatar-sm rounded-circle align-self-center">
                        <span class="avatar-title">
                            <i class="fa-duotone fa-ticket fa-3x text-primary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mini-stats-wid shadow mb-5">
            <div class="card-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted font-weight-medium">Löschaufträge</p>
                        <h4 class="mb-0"><?php echo $deletedUserCount; ?></h4>
                    </div>

                    <div class="avatar-sm rounded-circle align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle">
                            <i class="fa-duotone fa-trash-alt fa-3x text-primary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
			  
    <div class="col-md-3">
        <div class="card mini-stats-wid shadow mb-5">
            <div class="card-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted font-weight-medium">E-Mails gesendet</p>
                        <h4 class="mb-0"><?php echo $emailCount; ?></h4>
                    </div>

                    <div class="avatar-sm rounded-circle align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle">
                            <i class="fa-duotone fa-3x fa-envelope text-primary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
          
          
          
function generateBadge($role) {
    $badge_class = '';
    switch ($role) {
        case 'Owner':
        case 'Admin':
            $badge_class = 'danger';
            break;
        case 'Supporter':
            $badge_class = 'primary';
            break;
        default:
            $badge_class = 'default';
    }
    return '<span class="badge badge-sm badge-' . $badge_class . '">' . $role . '</span>';
}
?>
          






        </div>

    </div>

    <?php
    include '../settings/footer.php';
    ?>
</body>

</html>