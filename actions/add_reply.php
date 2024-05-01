<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $login_url . '.php' : $login_url));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id']) && isset($_POST['reply_message'])) {
    $userId = $_SESSION['user_id'];
    $ticketId = $_POST['ticket_id'];
    $replyMessage = htmlspecialchars($_POST['reply_message']); // HTML-Steuerzeichen umwandeln

    // Überprüfen, ob genügend Zeit seit der letzten Nachricht vergangen ist
    if (!isset($_SESSION['last_message_time']) || (time() - $_SESSION['last_message_time']) > 30) {
        // SQL-Abfrage, um die Antwort in die Datenbank einzufügen
        $sql = "INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $ticketId, $userId, $replyMessage);
        $stmt->execute();
        $stmt->close();

        // Aktualisiere die Zeit der letzten Nachricht
        $_SESSION['last_message_time'] = time();

        $_SESSION['success_message'] = "Deine Antwort wurde erfolgreich gesendet.";
        header("Location: " . ($phpenable === 'true' ? $siteurl . $ticketview_url . '.php?ticket_id=' . $ticketId : $siteurl . $ticketview_url . '?ticket_id=' . $ticketId));
        exit;
    } else {
        $_SESSION['error_message'] = "Du kannst nur alle 30 Sekunden eine Nachricht senden.";
        header("Location: " . ($phpenable === 'true' ? $siteurl . $ticketview_url . '.php?ticket_id=' . $ticketId : $siteurl . $ticketview_url . '?ticket_id=' . $ticketId));
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>