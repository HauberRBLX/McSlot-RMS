<?php
session_start();
require '../db_connection.php';

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
        exit;
    }

    $sql = "SELECT category, COUNT(*) AS file_count FROM files GROUP BY category";
    $result = $conn->query($sql);

    $categories = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = array(
                'category' => $row['category'],
                'file_count' => $row['file_count']
            );
        }
    }

    echo json_encode($categories);

    $conn->close();
} else {
    $_SESSION['error_message'] = "Ungültige Anfrage!";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $dash_url . '.php' : $siteurl . $dash_url));
    exit;
}
?>
