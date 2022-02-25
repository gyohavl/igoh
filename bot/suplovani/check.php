<?php
ini_set('display_errors', 1);
error_reporting(E_ERROR | E_WARNING);
ignore_user_abort(true);
header('Content-Type: text/plain');

$file = customCurl('https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));
$file = explode('<h1>Změny v rozvrhu', $file, 2);
$file = isset($file[1]) ? $file[1] : false;
$lastFile = file_get_contents('data/last.html');

if ($file !== false) {
    if ($lastFile != $file) {
        file_put_contents('data/last.html', $file);
        file_get_contents('https://suply.herokuapp.com/suplovani-send-new.php');
        echo 'Odesláno!';
    } else {
        echo 'Beze změn.';
    }
} else {
    echo 'Nelze načíst obsah suplování.';
}

function customCurl($url) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_TIMEOUT, 5);
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($c);
    $http_code = curl_getinfo($c, CURLINFO_HTTP_CODE);
    $ct = curl_getinfo($c, CURLINFO_CONTENT_TYPE);
    curl_close($c);
    return ($http_code == 200 && substr($ct, 0, 9) == 'text/html') ? $result : false;
}
