<?php
// Verbindung zur Datenbank herstellen
require '../../db_connection.php';

$sql_hoster = "SELECT * FROM hoster_list";
$result_hoster = $conn->query($sql_hoster);

$updated_services = [];

if ($result_hoster->num_rows > 0) {
    while ($row_hoster = $result_hoster->fetch_assoc()) {
        $hoster_url = $row_hoster['hoster_url'];
        $current_status = $row_hoster['status'];

        // Wenn der aktuelle Status "maintenance" ist, überspringen Sie die Überprüfung
        if ($current_status === "maintenance") {
            continue;
        }

        // HTTP-Status überprüfen
        $headers = @get_headers($hoster_url);
        if ($headers && (strpos($headers[0], '200') !== false || strpos($headers[0], '301') !== false || strpos($headers[0], '302') !== false)) {
            $status = "Online";
        } elseif ($headers) {
            $status = "error";
        } else {
            $status = "Offline";
        }

        // Nur aktualisieren, wenn der Status sich geändert hat
        if ($current_status !== $status) {
            // Status in die Datenbank aktualisieren
            $update_sql = "UPDATE hoster_list SET status = '$status' WHERE hoster_url = '$hoster_url'";
            $conn->query($update_sql);

            // Protokollieren, welche Dienste aktualisiert wurden
            $updated_services[] = "Dienst: $hoster_url - Status: $current_status -> $status";

            $cleaned_url = str_replace(array('http://', 'https://'), '', $hoster_url);

			$ip_info_json = file_get_contents("http://ip-api.com/json/" . $cleaned_url . "?fields=status,countryCode,city,query");
            $ip_info = json_decode($ip_info_json, true);
            $country_code = $ip_info['countryCode'];
            $city = $ip_info['city'];
            $location = $country_code . ", " . $city;

            // Standort in die Datenbank aktualisieren
            $update_location_sql = "UPDATE hoster_list SET location = '$location' WHERE hoster_url = '$hoster_url'";
            $conn->query($update_location_sql);
        }
    }

    // Aktuelles Datum und Zeit einfügen oder aktualisieren
    $current_datetime = date('Y-m-d H:i:s');
    $update_datetime_sql = "UPDATE queue_last_execution SET last_execution_datetime = '$current_datetime' WHERE id = 2";
    $conn->query($update_datetime_sql);
} else {
    echo "Keine Daten gefunden";
}

// Verbindung schließen
$conn->close();

if (!empty($updated_services)) {
    // Erfolgsmeldung generieren
    $success_message = "Folgende Dienste wurden aktualisiert:\n" . implode("\n", $updated_services);
    echo $success_message;
} else {
    echo "Keine Dienste wurden aktualisiert.";
}
?>
