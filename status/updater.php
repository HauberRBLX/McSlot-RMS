<?php


if(file_exists("assets/config/auth.json")){
    $auth = json_decode(file_get_contents("assets/config/auth.json"), true);
    $customer_id = $auth['customer_id'];
    $license_key = $auth['license_key'];
    $url = "https://api.zeneg.de/v1/projects/status/update/";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = '{"license_key":"' . $license_key . '","customer_id":"' . $customer_id . '"}';

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = json_decode(curl_exec($curl), true);
    curl_close($curl);

    var_dump($resp);

    if($resp['success']){
        $files = $resp['updated_files'];
        foreach ($files as $file) {
            unlink($file);
        }
        $folders = $resp['updated_folders'];
        foreach ($folders as $folder) {
            rmdir($folder);
        }

        header("Location:installer.php?customer_id=$customer_id&license_key=$license_key");
    }

}
