<?php

require_once 'settings/config.php';

if (!isset($product_id) || !isset($license_code) || !isset($client_name)) {
    echo "Lizenz: Fehlende Konfigurationsvariablen.";
    exit;
}

$url = 'https://license.mcslot.net/api/activate_license';
$api_key = 'A02046645E068E97D668';
$lb_url = $domain;
$lb_ip = $_SERVER['SERVER_ADDR'];   // Automatisch die IP-Adresse des Servers abrufen
$lb_lang = 'german';

$data = array(
    'verify_type' => 'non_envato',
    'product_id' => $product_id,
    'license_code' => $license_code, // Den Lizenzcode aus der Konfigurationsdatei verwenden
    'client_name' => $client_name
);

$options = array(
    'http' => array(
        'header' => "LB-API-KEY: $api_key\r\n" .
            "LB-URL: $lb_url\r\n" .
            "LB-IP: $lb_ip\r\n" .
            "LB-LANG: $lb_lang\r\n" .
            "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data),
    ),
);

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    // Fehlerbehandlung
} else {
    // Verarbeite die Antwort
    $response = json_decode($result, true);

    if ($response && isset($response['status']) && $response['status'] === false) {
        echo "Fehler: " . $response['message'];
        exit;
    }
}

?>