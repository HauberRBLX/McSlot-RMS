<?php
session_start();
require '../db_connection.php';

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . ($phpenable === 'true' ? $siteurl . $login_url . '.php' : $siteurl . $login_url));
        exit;
    }

    function getFileInfoByCategory($conn, $selectedCategory) {
        $fileInfo = array();

        $sql = "SELECT id, name, size, date, released FROM files WHERE category = ? ORDER BY date DESC"; // Sortieren nach Datum absteigend
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $selectedCategory);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $fileInfo[] = array(
                    'name' => $row['name'],
                    'size' => $row['size'],
                    'date' => date("d.m.Y H:i", strtotime($row['date'])),
                    'released' => $row['released']
                );
            }
        }

        return $fileInfo;
    }

    function getAllCategories($conn) {
        $categories = array();
        $sql = "SELECT DISTINCT category FROM files";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row['category'];
            }
        }

        return $categories;
    }

    if (isset($_GET['cat'])) {
        $selectedCategory = urldecode($_GET['cat']);

        $fileInfo = getFileInfoByCategory($conn, $selectedCategory);

        echo json_encode($fileInfo);
    } else {
        $categories = getAllCategories($conn);

        echo json_encode($categories);
    }
} else {
    $_SESSION['error_message'] = "Ungültige Anfrage!";
    header("Location: " . ($phpenable === 'true' ? $siteurl . $dash_url . '.php' : $siteurl . $dash_url));
    exit;
}

$conn->close();
?>
