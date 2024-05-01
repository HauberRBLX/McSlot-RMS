<?php
session_start();
require '../../db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

$sql = "SELECT * FROM benutzer WHERE id = " . $_SESSION['user_id'] . " AND (role = 'Admin' OR role = 'Owner')";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "Du hast keine Berechtigung für diese Aktion.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
    exit;
}

// Assuming you have a database connection established (e.g., $conn)

// Prepare a SQL statement to delete all codes
$sql = "DELETE FROM registration_codes";
$stmt = $conn->prepare($sql);

// Execute the statement
if ($stmt->execute()) {
    // Codes deleted successfully
    $_SESSION['success_message'] = "Alle Registrierungs-Codes wurden erfolgreich gelöscht";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $codes_url_admin . '.php' : $siteurl . $admin_directory . $codes_url_admin));
    exit;
} else {
    // Error occurred while deleting the codes
    echo "Error deleting the codes: " . $stmt->error;
    // You can redirect to an error page or take other actions as needed
}

// Close the statement and the database connection if necessary
$stmt->close();
$conn->close();
?>
