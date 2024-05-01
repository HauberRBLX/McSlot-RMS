<?php
session_start();
require '../../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

// Überprüfen Sie, ob der Benutzer ein Administrator ist
$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'] . " AND (role = 'Supporter' OR role = 'Admin' OR role = 'Owner')";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "Du hast keine Berechtigung für diese Aktion.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticketId = $_POST['ticket_id'];
    $replyMessage = $_POST['reply_message'];
    $teamReply = 1;

    if (empty($ticketId) || empty($replyMessage)) {
        // Handle error: Ticket-ID oder Antwort-Nachricht ist leer
        header("Location: " . ($phpenable === 'true' ? $admin_directory . $tickets_url_admin . '.php' : $admin_directory . $tickets_url_admin));
        exit;
    }

    $userId = $_SESSION['user_id']; // Annahme: Das aktuelle eingeloggte Benutzerkonto ist das Teammitglied

    // Fügen Sie die Antwort zur Datenbank hinzu
    $sql = "INSERT INTO ticket_replies (ticket_id, user_id, message, team_reply) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisi", $ticketId, $userId, $replyMessage, $teamReply);
    $stmt->execute();

    // Überprüfen Sie, ob die Antwort erfolgreich hinzugefügt wurde
    if ($stmt->affected_rows > 0) {
        // Erfolgreich hinzugefügt

        // Aktualisieren Sie den Ticketstatus auf "Bearbeitung"
        $updateStatusSql = "UPDATE tickets SET status = 'bearbeitung' WHERE id = ?";
        $updateStatusStmt = $conn->prepare($updateStatusSql);
        $updateStatusStmt->bind_param("i", $ticketId);
        $updateStatusStmt->execute();

        // Weiterleitung zur Ticketdetails-Seite

        $_SESSION['success_message'] = "Deine Antwort wurde nun gesendet.";
        header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $ticketview_url . '.php?ticket_id=' . $ticketId : $siteurl . $admin_directory . $ticketview_url . '?ticket_id=' . $ticketId));
    } else {
        // Fehler beim Hinzufügen der Antwort
        header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $ticketview_url . '.php' : $siteurl . $admin_directory . $ticketview_url));
    }

    $stmt->close();
    $updateStatusStmt->close();
    $conn->close();
} else {
    // Unerlaubter Zugriff zur Datei
    header("Location: ../ticket.php");
    exit;
}
?>