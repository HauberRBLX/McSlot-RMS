<?php
require '../../db_connection.php';
session_start();

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

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Du kannst deinen eigenen Account nicht löschen.";
        header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $users_url_admin . '.php' : $siteurl . $admin_directory . $users_url_admin));
        exit;
    }

    $check_super_admin_sql = "SELECT role FROM benutzer WHERE id = ?";
    $check_super_admin_stmt = $conn->prepare($check_super_admin_sql);
    $check_super_admin_stmt->bind_param("i", $user_id);
    $check_super_admin_stmt->execute();
    $result = $check_super_admin_stmt->get_result();

if ($result->num_rows === 1) {
    $user_data = $result->fetch_assoc();
    $allowed_roles = ['Supporter', 'Admin', 'Owner'];
    if (in_array($user_data['role'], $allowed_roles)) {
        $_SESSION['error_message'] = "Du darfst einen Benutzer mit der Rolle '{$user_data['role']}' nicht löschen.";
        header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $users_url_admin . '.php' : $siteurl . $admin_directory . $users_url_admin));
        exit;
    }
} else {
    // Benutzer mit der angegebenen ID nicht gefunden
    $_SESSION['error_message'] = "Der Benutzer konnte nicht gefunden werden.";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $users_url_admin . '.php' : $siteurl . $admin_directory . $users_url_admin));
    exit;
}
    // Wenn der Benutzer nicht 'super_admin' ist, führen Sie die Löschung durch
    $delete_benutzer_sql = "DELETE FROM benutzer WHERE id = ?";
    $delete_benutzer_stmt = $conn->prepare($delete_benutzer_sql);
    $delete_benutzer_stmt->bind_param("i", $user_id);
    $delete_benutzer_stmt->execute();

    // Vorbereiten und Ausführen der DELETE-Anweisungen für die anderen Tabellen
    $delete_tables_sql = [
        "DELETE FROM login_history WHERE user_id = ?",
        "DELETE FROM tickets WHERE user_id = ?",
        "DELETE FROM ticket_replies WHERE user_id = ?",
        "DELETE FROM remember_me_tokens WHERE user_id = ?",
        "DELETE FROM benutzer WHERE id = ?"
    ];

    foreach ($delete_tables_sql as $sql) {
        $delete_stmt = $conn->prepare($sql);
        $delete_stmt->bind_param("i", $user_id);
        $delete_stmt->execute();
    }
    sleep(1);
    $_SESSION['success_message'] = "Der Benutzer wurde erfolgreich gelöscht";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $users_url_admin . '.php' : $siteurl . $admin_directory . $users_url_admin));
} else {
    header("Location: " . ($phpenable === 'true' ? $siteurl . $admin_directory . $users_url_admin . '.php' : $siteurl . $admin_directory . $users_url_admin));
}
?>