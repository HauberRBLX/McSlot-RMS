<?php
function fetchContents($url){
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $resp = curl_exec($curl);
    curl_close($curl);

    return $resp;
}
// $_POST = json_decode(file_get_contents('php://input'), true);
if (isset($_POST['license_key']) || isset($_GET['license_key'])) {
    if(isset($_POST['license_key'])) {
        $customer_id = $_POST['customer_id'];
        $license_key = $_POST['license_key'];
    } else {
        $customer_id = $_GET['customer_id'];
        $license_key = $_GET['license_key'];
    }


    $url = "https://api.zeneg.de/v1/projects/status/installer/";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = <<<DATA
{
  "customer_id":"$customer_id",
  "license_key":"$license_key"
}
DATA;
    // echo $data;

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    // var_dump($curl);

    $resp =json_decode(curl_exec($curl), true);
    curl_close($curl);

    if($resp == null || !isset($resp)){
        echo "Error while installing. No response from server.";
        exit;
    }

    if($resp['success']){
        $tempcode = $resp['tempcode'];
        $success = array();
        $success[] = mkdir("assets");
        $success[] = mkdir("assets/fetch");
        $success[] = mkdir("assets/config");
        $success[] = file_put_contents("index.php", fetchContents($resp['files']['index']));
        $success[] = file_put_contents("updater.php", fetchContents($resp['files']['updater']));
        $success[] = file_put_contents("assets/fetch/fetch.php", fetchContents($resp['files']['fetch']));
        $success[] = file_put_contents("assets/config/auth.json", fetchContents($resp['files']['config']));
        foreach ($success as $itmsuccess) {
            if(!$itmsuccess){
                echo "Error while installing. Point: " . json_encode($itmsuccess);
                rmdir("assets");
                unlink("index.php");
                unlink("updater.php");
                exit;
            }
        }

        $curl2 = curl_init($url);
        curl_setopt($curl2, CURLOPT_URL, $url);
        curl_setopt($curl2, CURLOPT_POST, true);
        curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/json",
        );
        curl_setopt($curl2, CURLOPT_HTTPHEADER, $headers);

        $data = <<<DATA
{
  "tempcode":"$tempcode"
}
DATA;
        // echo $data;

        curl_setopt($curl2, CURLOPT_POSTFIELDS, $data);

//for debug only!
        curl_setopt($curl2, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, false);

        // var_dump($curl);

        $resp =curl_exec($curl2);
        curl_close($curl2);

        echo "<script>console.log('" . $tempcode . "')</script>";

        echo "Installation successful. Redirecting to <a href='index.php'>index.php</a>";
        header("Refresh: 2; url=index.php?installed");
    }




}


?>

<form method="post">
    <input name="customer_id" type="text" placeholder="Customer ID">
    <input name="license_key" type="text" placeholder="License Key">
    <button type="submit">Login</button>
</form>