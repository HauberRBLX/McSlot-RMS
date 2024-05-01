<?php
session_start();
require '../../db_connection.php';

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'] . " AND (role = 'Supporter' OR role = 'Admin' OR role = 'Owner')";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "Du hast keine Berechtigung für diese Aktion.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

if (isset($_POST['ticket_id'])) {
    $ticketId = $_POST['ticket_id'];

    if (!isset($_SESSION['user_id'])) {
        header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
        exit;
    }

    $checkUserSql = "SELECT user_id FROM tickets WHERE id = ?";
    $checkUserStmt = $conn->prepare($checkUserSql);
    $checkUserStmt->bind_param("i", $ticketId);
    $checkUserStmt->execute();
    $checkUserStmt->bind_result($ticketUserId);
    $checkUserStmt->fetch();
    $checkUserStmt->close();



    $updateSql = "UPDATE tickets SET status = 'geschlossen' WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("i", $ticketId);

    if ($updateStmt->execute()) {
        header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $ticketview_url . '.php?ticket_id=' . $ticketId : $siteurl . $admin_directory . $ticketview_url . '?ticket_id=' . $ticketId));
        exit;
    } else {
        echo "Fehler beim Schließen des Tickets";
    }

    $updateStmt->close();
} else {
    echo "Ungültige Anfrage";
}
?>