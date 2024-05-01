<?php
session_start();
require '../db_connection.php';

if (isset($_POST['ticket_id'])) {
    $ticketId = $_POST['ticket_id'];

    if (!isset($_SESSION['user_id'])) {
        header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
        exit;
    }

    $checkUserSql = "SELECT user_id FROM tickets WHERE id = ?";
    $checkUserStmt = $conn->prepare($checkUserSql);
    $checkUserStmt->bind_param("i", $ticketId);
    $checkUserStmt->execute();
    $checkUserStmt->bind_result($ticketUserId);
    $checkUserStmt->fetch();
    $checkUserStmt->close();

    if ($ticketUserId != $_SESSION['user_id']) {
        // Hier kannst du eine Weiterleitung oder eine Fehlermeldung implementieren
        exit("Nicht berechtigt, das Ticket zu schließen");
    }


    $updateSql = "UPDATE tickets SET status = 'geschlossen' WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("i", $ticketId);

    if ($updateStmt->execute()) {
        $_SESSION['error_message'] = "Das Ticket wurde erfolgreich geschlossen.";
        header("Location: " . ($phpenable === 'true' ? $siteurl . $ticketview_url . '.php?ticket_id=' . $ticketId : $siteurl . $ticketview_url . '?ticket_id=' . $ticketId));
        exit;
    } else {
        echo "Fehler beim Schließen des Tickets";
    }

    $updateStmt->close();
} else {
    echo "Ungültige Anfrage";
}
?>