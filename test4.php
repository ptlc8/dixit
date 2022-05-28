<?php

$url = "https://api.waifu.pics/many/sfw/waifu";
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
    "Accept: application/json",
    "Content-Type: application/json"
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

curl_setopt($curl, CURLOPT_POSTFIELDS, "{}");

$resp = curl_exec($curl);
curl_close($curl);
foreach(json_decode($resp)->files as $files)
    echo '<img src="'.$files.'" />';

?>

