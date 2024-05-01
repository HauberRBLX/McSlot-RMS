
<?php
include '../settings/config.php';

session_start();
require '../db_connection.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . '.php' : $siteurl . $admin_directory));
    exit;
}

// LOGIN SYSTEM

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM benutzer WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Überprüfen, ob der Benutzer ein Administrator ist
        if ($user['role'] == 'Supporter' || $user['role'] == 'Admin' || $user['role'] == 'Owner') {
            if (password_verify($password, $user['password'])) {
                if ($user['gesperrt'] == 1) {
                    if (!empty($user['sperrgrund'])) {
                        $error_message = "Dieses Konto wurde von einem Administrator gesperrt. <br>Grund: " . $user['sperrgrund'];
                    } else {
                        $error_message = "Dieses Konto wurde von einem Administrator gesperrt.";
                    }
                } else {
                    // Erfolgreich angemeldet
                    $error_message = "Erfolgreich angemeldet!";
                    echo '<center><div class="alert alert-success" role="alert"><h2><i class="fa-solid fa-circle-check"></i> Erfolgreich angemeldet!</h2></center>';
                    $_SESSION['user_id'] = $user['id'];
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                    $login_status = 'erfolgreich';

                    $sql = "INSERT INTO login_history (user_id, login_time, ip_address, login_status) VALUES (?, NOW(), ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iss", $user['id'], $ip_address, $login_status);
                    $stmt->execute();

                    sleep(2);
                    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory : $siteurl . $admin_directory));
                    exit;
                }
            } else {
                // Fehlgeschlagener Login
                $error_message = "Ungültige Anmeldeinformationen";

                // Fehlgeschlagenen Login in der Login-History speichern
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $login_status = 'abgelehnt';

                $sql = "INSERT INTO login_history (user_id, login_time, ip_address, login_status) VALUES (?, NOW(), ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iss", $user['id'], $ip_address, $login_status);
                $stmt->execute();
            }
        } else {
            // Benutzer ist kein Administrator, zeige eine Fehlermeldung
            $error_message = "Nur Administratoren können sich anmelden.";
        }
    } else {
        $error_message = "Dieses Konto existiert nicht.";
    }
}
?>


<head>
    <link rel="shortcut icon" type="image/x-icon" href="<?= $faviconurl ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <script>
        window.addEventListener("load", function () {
            setTimeout(function () {
                document.querySelector('body').classList.add('loaded');
            }, 150);
        });
    </script>
    <link rel="manifest" href="../manifest.json">
    <link rel="stylesheet" href="<?= $assetspath ?>style/libs/swiper/dist/css/swiper.min.css">
    <link rel="stylesheet" href="<?= $assetspath ?>style/libs/@fancyapps/fancybox/dist/jquery.fancybox.min.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
    <link rel="stylesheet" href="<?= $assetspath ?>style/css/preloader.css" id="stylesheet">
    <link rel="stylesheet" href="<?= $assetspath ?>style/css/style.css" id="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/js/all.min.js"
        integrity="sha256-HkXXtFRaflZ7gjmpjGQBENGnq8NIno4SDNq/3DbkMgo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css"
        integrity="sha256-2XFplPlrFClt0bIdPgpz8H7ojnk10H69xRqd9+uTShA=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css"
        integrity="sha256-ENFZrbVzylNbgnXx0n3I1g//2WeO47XxoPe0vkp3NC8=" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"
        integrity="sha256-3blsJd4Hli/7wCQ+bmgXfOdK7p/ZUMtPXY08jmxSSgk=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
    <script src="<?= $assetspath ?>style/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</head>
<div class="preloader">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Lade...</span>
    </div>
</div>

<?php
$query = "SELECT setting_value FROM settings WHERE setting_name = 'website_name'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row["setting_value"];

    // Übersetzungsdatei basierend auf der gewünschten Sprache laden
    $translationsFile = '../settings/lang/' . $default_language . '.json';

    if (file_exists($translationsFile)) {
        $translations = json_decode(file_get_contents($translationsFile), true);

        // Ersetze {websiteName} mit dem tatsächlichen Namen

        // Ersetze {status_url} mit dem Wert von $status_url, falls vorhanden
        if (isset($translations['status_url'])) {
            $name = str_replace('{status_url}', $status_url, $name);
        }
    } else {
        // Fallback, falls die Übersetzungsdatei nicht gefunden wird
        $name = str_replace('{status_url}', $status_url, $name);
        echo '<title>Startseite — ' . $name . '</title>';
    }
}
?>
<title>Anmelden &mdash; Admin</title>

<body>
    <div class="container" style="max-width: 400px;">
        <br><br><br>

        <h1 class="text-center mt-5">Adminpanel</h1>
      <p class="text-center mt-5">Notfall Login</p>

        <?php

        if ($error_message) {
            echo '<div class="alert alert-danger"> ' . $error_message . '</div>';
        }

        if (isset($_GET['reg'])) {
            $reg = "Dein Account wurde erfolgreich erstellt.";
        }
        if ($reg) {
            echo '<div class="alert alert-success"> ' . $reg . '</div>';
        }

        ?>

        <form action="login.php" method="post" class="mt-3">
            <div class="mb-3">
                <label for="login" class="form-label">Benutzername</label>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-user"></i> </span>
                    <input type="text" name="login" id="login" class="form-control" aria-describedby="basic-addon1"
                        required>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Passwort</label>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-key"></i> </span>
                    <input type="password" name="password" id="password" class="form-control"
                        aria-describedby="basic-addon1" required>
                </div>
            </div>
            <br>

            <center>
                <button id="loadBtn" type="submit" class="btn btn-primary">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <span class="sr-only">Loading...</span>
                </button>
            </center>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js?"></script>
</body>

</html>

<style>
    .btn-animated {
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
    }
</style>

<script>
    document.getElementById('loadBtn').disabled = true;

    setTimeout(() => {
        document.getElementById('loadBtn').disabled = false;
        document.getElementById('loadBtn').className = 'btn btn-animated btn-animated-x';
        setTimeout(() => {
            document.getElementById('loadBtn').className = 'btn btn-animated btn-primary btn-animated-x';
            document.getElementById('loadBtn').innerHTML = '<span class="btn-inner--visible">Anmelden</span><span class="btn-inner--hidden"><i class="fa-solid fa-right-to-bracket"></i></span>';
        }, 0); // 1000 Millisekunden = 1 Sekunde
    }, 1000); // 1000 Millisekunden = 1 Sekunde


</script>

<?php
include 'settings/footer.php';
?>