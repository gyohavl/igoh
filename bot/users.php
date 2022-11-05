<?php
include('src/main.php');

function getPicture($id, $table, $token) {
    $result = sql("SELECT * FROM `$table` WHERE `id`=?;", true, array($id));
    $success = false;

    if (!empty($result[0])) {
        if (!empty($result[0]['picture'])) {
            $url = $result[0]['picture'];
            $success = downloadImage($url);

            if ($success) {
                return;
            }
        }

        $userResponse = customCurl("https://graph.facebook.com/v6.0/" . $result[0]['messenger_id'] . "?fields=profile_pic&access_token=" . $token);
        // echo "https://graph.facebook.com/v6.0/" . $result[0]['messenger_id'] . "?fields=profile_pic&access_token=" . $token;
        $user = json_decode($userResponse, true);
        $url = $user['profile_pic'];
        $success = downloadImage($url);
    }

    if ($success) {
        sql("UPDATE `$table` SET `picture`=? WHERE `id`=?;", false, array($url, $id));
        return;
    } else {
        echo base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAoKCgoKCgsMDAsPEA4QDxYUExMUFiIYGhgaGCIzICUgICUgMy03LCksNy1RQDg4QFFeT0pPXnFlZXGPiI+7u/sBCgoKCgoKCwwMCw8QDhAPFhQTExQWIhgaGBoYIjMgJSAgJSAzLTcsKSw3LVFAODhAUV5PSk9ecWVlcY+Ij7u7+//CABEIAGQAZAMBIgACEQEDEQH/xAAaAAEAAgMBAAAAAAAAAAAAAAAABQYBAgME/9oACAEBAAAAALuAAAADtK9fFGAOln6EXCAS0yNangExLjFSwDvZ8kfAAJiXaVjkBNyg513zBLTIOVY0NrVuBEQ57rCA8lbJSbAaVN//xAAUAQEAAAAAAAAAAAAAAAAAAAAA/9oACAECEAAAAAAAAAAAAH//xAAUAQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDEAAAAAAAAAAAAH//xAAvEAACAQAGBwcFAAAAAAAAAAABAgMABAURIDESEyEwQVFxECIjUoGRsUBCYWKh/9oACAEBAAE/AProYJJ2uRep5USzIwPEdmP42UNnVUrk4PWk9mSRrpxnTHLI7mGNppFRcyaRRJCiog2DBadVUg1iNbvMNxZSAvK54KAPXCyq6MpyKke9CCCRjspgNep4gEYSQASaMb2J5k/3HVptRMr5jj0NFZXUMDeCMFoVgRxmMHvv8bmzxW1+zwT5vkds+uCeEFLcL6TrMsja4MHJ47ip1EIA8ovbgvLDLDFOmi6g0rVVerPcdqnJsVm1YOdc+QNy454kmjKMPXkaSI0TsjZg4EUu6qBeWIA6mkaLEiouQAG4tSLakwGfdbBZ6aVaU3XhQW3NdTTqsvMC/wBsFlEiaQjPQ3MovilH6N2//8QAFBEBAAAAAAAAAAAAAAAAAAAAUP/aAAgBAgEBPwBH/8QAFBEBAAAAAAAAAAAAAAAAAAAAUP/aAAgBAwEBPwBH/9k=');
    }
}

function downloadImage($url) {
    $c = curl_init($url);
    curl_setopt($c, CURLOPT_NOBODY, true);
    curl_setopt($c,  CURLOPT_RETURNTRANSFER, true);
    curl_exec($c);
    $httpCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
    curl_close($c);

    if ($httpCode == 200) {
        $im = imagecreatefromjpeg($url);
        imagejpeg(imagescale($im, 150));
        imagedestroy($im);
        return true;
    }
}

if (!empty($_GET['picture'])) {
    header('Content-type: image/jpg');
    $table = isset($_GET['canteen']) ? 'bot_canteen' : 'bot_suplovani';
    getPicture(intval($_GET['picture']), $table, $secrets['fb']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seznam uživatelů bota Suplování GOH</title>
</head>
<body>
    
</body>
</html>
