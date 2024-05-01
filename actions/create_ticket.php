<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: $login_url");
    exit;
}

$userId = $_SESSION['user_id'];

// Überprüfen, ob der Nutzer eine Ticket-Sperre hat
$sqlCheckTicketSperre = "SELECT ticket_sperre FROM benutzer WHERE id = ? AND ticket_sperre = 0";
$stmtCheckTicketSperre = $conn->prepare($sqlCheckTicketSperre);
$stmtCheckTicketSperre->bind_param("i", $userId);
$stmtCheckTicketSperre->execute();
$stmtCheckTicketSperre->store_result();

if ($stmtCheckTicketSperre->num_rows > 0) {
    // Der Nutzer hat keine Ticket-Sperre und kann ein Ticket erstellen

    // Überprüfen, ob seit dem letzten Ticket mehr als 5 Minuten vergangen sind
    $sqlLastTicketTime = "SELECT MAX(created_at) as last_ticket_time FROM tickets WHERE user_id = ?";
    $stmtLastTicketTime = $conn->prepare($sqlLastTicketTime);
    $stmtLastTicketTime->bind_param("i", $userId);
    $stmtLastTicketTime->execute();
    $stmtLastTicketTime->bind_result($lastTicketTime);
    $stmtLastTicketTime->fetch();
    $stmtLastTicketTime->close();

    $currentTime = time();
    $timeDifference = $currentTime - strtotime($lastTicketTime);

    if ($timeDifference > 300) { // 300 Sekunden entsprechen 5 Minuten

        // Überprüfen, ob der Benutzer bereits fünf offene oder in Bearbeitung befindliche Tickets hat
        $sqlCheckOpenOrInProgressTickets = "SELECT COUNT(*) as open_or_in_progress_count FROM tickets WHERE user_id = ? AND status IN ('offen', 'bearbeitung')";
        $stmtCheckOpenOrInProgressTickets = $conn->prepare($sqlCheckOpenOrInProgressTickets);
        $stmtCheckOpenOrInProgressTickets->bind_param("i", $userId);
        $stmtCheckOpenOrInProgressTickets->execute();
        $stmtCheckOpenOrInProgressTickets->bind_result($openOrInProgressCount);
        $stmtCheckOpenOrInProgressTickets->fetch();
        $stmtCheckOpenOrInProgressTickets->close();

        if ($openOrInProgressCount < 5) {
            // Der Benutzer hat weniger als 5 offene oder in Bearbeitung befindliche Tickets und kann ein neues erstellen
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $subject = $_POST['subject'];
                $message = $_POST['message'];
                $ticketId = rand(10000000, 99999999);

                $sqlTicket = "INSERT INTO tickets (user_id, id, subject, message) VALUES (?, ?, ?, ?)";
                $stmtTicket = $conn->prepare($sqlTicket);
                $stmtTicket->bind_param("iiss", $userId, $ticketId, $subject, $message);
                $stmtTicket->execute();
                $stmtTicket->close();

                $sqlReply = "INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)";
                $stmtReply = $conn->prepare($sqlReply);
                $stmtReply->bind_param("iis", $ticketId, $userId, $message);
                $stmtReply->execute();
                $stmtReply->close();

                $_SESSION['success_message'] = "Dein Ticket wurde erfolgreich erstellt.";
                header("Location: " . ($phpenable === 'true' ? $siteurl . $ticket_url . '.php' : $siteurl . $ticket_url));
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Du kannst nur 5 offene oder in Bearbeitung befindliche Tickets haben.";
            header("Location: " . ($phpenable === 'true' ? $siteurl . $ticket_url . '.php' : $siteurl . $ticket_url));
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Du kannst nur alle 5 Minuten ein Ticket erstellen.";
        header("Location: " . ($phpenable === 'true' ? $siteurl . $ticket_url . '.php' : $siteurl . $ticket_url));
        exit;
    }
} else {
    $_SESSION['error_message'] = "Du hast keine Berechtigung, ein Ticket zu erstellen.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $ticket_url . '.php' : $siteurl . $ticket_url));
    exit;
}
?>