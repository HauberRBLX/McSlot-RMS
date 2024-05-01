<!-- actions/reactive_account.php -->

<?php
session_start();
require '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $reactive_url . '.php' : $siteurl . $reactive_url));
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

$userId = $_SESSION['user_id'];
$sql = "UPDATE benutzer SET deleted = 0 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

// Lösche die User-ID aus der Warteschlange (angenommen, du hast eine Tabelle namens "queue")
$sqlDeleteQueue = "DELETE FROM queue WHERE user_id = ?";
$stmtDeleteQueue = $conn->prepare($sqlDeleteQueue);
$stmtDeleteQueue->bind_param("i", $userId);
$stmtDeleteQueue->execute();
$stmtDeleteQueue->close();

// Optional: Füge hier weitere Aktionen hinzu, die nach der Reaktivierung ausgeführt werden sollen.

$_SESSION['success_message'] = "Dein Account wurde erfolgreich reaktiviert!";
header("Location: " . ($phpenable === 'true' ? $siteurl . $dash_url . '.php' : $siteurl . $dash_url));
exit;
?>