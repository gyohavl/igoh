<?php
include('src/main.php');

$numberOfStudents = 590;

function getPicture($data, $table, $token) {
    $defaultPicture = 'data:image/jpeg;base64, /9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAoKCgoKCgsMDAsPEA4QDxYUExMUFiIYGhgaGCIzICUgICUgMy03LCksNy1RQDg4QFFeT0pPXnFlZXGPiI+7u/sBCgoKCgoKCwwMCw8QDhAPFhQTExQWIhgaGBoYIjMgJSAgJSAzLTcsKSw3LVFAODhAUV5PSk9ecWVlcY+Ij7u7+//CABEIAGQAZAMBIgACEQEDEQH/xAAaAAEAAgMBAAAAAAAAAAAAAAAABQYBAgME/9oACAEBAAAAALuAAAADtK9fFGAOln6EXCAS0yNangExLjFSwDvZ8kfAAJiXaVjkBNyg513zBLTIOVY0NrVuBEQ57rCA8lbJSbAaVN//xAAUAQEAAAAAAAAAAAAAAAAAAAAA/9oACAECEAAAAAAAAAAAAH//xAAUAQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDEAAAAAAAAAAAAH//xAAvEAACAQAGBwcFAAAAAAAAAAABAgMABAURIDESEyEwQVFxECIjUoGRsUBCYWKh/9oACAEBAAE/AProYJJ2uRep5USzIwPEdmP42UNnVUrk4PWk9mSRrpxnTHLI7mGNppFRcyaRRJCiog2DBadVUg1iNbvMNxZSAvK54KAPXCyq6MpyKke9CCCRjspgNep4gEYSQASaMb2J5k/3HVptRMr5jj0NFZXUMDeCMFoVgRxmMHvv8bmzxW1+zwT5vkds+uCeEFLcL6TrMsja4MHJ47ip1EIA8ovbgvLDLDFOmi6g0rVVerPcdqnJsVm1YOdc+QNy454kmjKMPXkaSI0TsjZg4EUu6qBeWIA6mkaLEiouQAG4tSLakwGfdbBZ6aVaU3XhQW3NdTTqsvMC/wBsFlEiaQjPQ3MovilH6N2//8QAFBEBAAAAAAAAAAAAAAAAAAAAUP/aAAgBAgEBPwBH/8QAFBEBAAAAAAAAAAAAAAAAAAAAUP/aAAgBAwEBPwBH/9k=';
    $id = $data['id'];
    $picture = $data['picture'];

    if (is_numeric($picture)) {
        if ($picture != 0) {
            sql("UPDATE `$table` SET `picture`=? WHERE `id`=?;", false, array($picture - 1, $id));
            return $defaultPicture;
        }
    } else {
        if (isImageUrlWorking($picture)) {
            return $picture;
        }
    }

    $userResponse = customCurl("https://graph.facebook.com/v6.0/" . $data['messenger_id'] . "?fields=profile_pic&access_token=" . $token);
    $user = json_decode($userResponse, true);

    if (isset($user['profile_pic'])) {
        $url = $user['profile_pic'];

        if (isImageUrlWorking($url)) {
            sql("UPDATE `$table` SET `picture`=? WHERE `id`=?;", false, array($url, $id));
            return $url;
        }
    }

    sql("UPDATE `$table` SET `picture`=? WHERE `id`=?;", false, array(rand(8, 15), $id));
    return $defaultPicture;
}

function isImageUrlWorking($url) {
    $c = curl_init($url);
    curl_setopt($c, CURLOPT_NOBODY, true);
    curl_setopt($c,  CURLOPT_RETURNTRANSFER, true);
    curl_exec($c);
    $httpCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
    curl_close($c);

    return ($httpCode == 200);
}

function czechUsers($userNumber) {
    if ($userNumber == 1) {
        return "uživatel";
    } else if ($userNumber > 1 && $userNumber < 5) {
        return "uživatelé";
    } else {
        return "uživatelů";
    }
}

if (!empty($_GET['picture'])) {
    header('Content-type: image/jpg');
    $table = isset($_GET['canteen']) ? 'bot_canteen' : 'bot_suplovani';
    getPicture(intval($_GET['picture']), $table, $secrets['fb']);
    exit;
}

$suplovani = sql("SELECT `id`, `messenger_id`, `class`, `picture` FROM `bot_suplovani`;");
$canteen = sql("SELECT `id`, `messenger_id`, `allergens`, `picture` FROM `bot_canteen`;");
$ids = array('suplovani' => array(), 'canteen' => array());
$data = array('suplovani' => array(), 'canteen' => array());
$uids = array();
$counter = array('class' => array(), 'allergens' => array(0, 0));

foreach ($suplovani as $row) {
    $ids['suplovani'][] = $row['messenger_id'];
    $data['suplovani'][$row['messenger_id']] = $row;
    $uids[] = $row['messenger_id'];

    if (!isset($counter['class'][$row['class']])) {
        $counter['class'][$row['class']] = 0;
    }

    $counter['class'][$row['class']]++;
}

foreach ($canteen as $row) {
    $ids['canteen'][] = $row['messenger_id'];
    $data['canteen'][$row['messenger_id']] = $row;
    $uids[] = $row['messenger_id'];
    $counter['allergens'][$row['allergens']]++;
}

$uniqueUsers = count(array_unique($uids));
shuffle($ids['suplovani']);
shuffle($ids['canteen']);
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../favicon.ico">
    <title>Přehled uživatelů bota Suplování GOH</title>
    <link rel="stylesheet" href="src/style.css">
</head>

<body>
    <header>
        <h1>Přehled uživatelů bota</h1>
    </header>
    <section>
        <ul>
            <li><a href="..">zpět na iGOH</a></li>
            <li><a href="http://m.me/suplovanigoh">otevřít bota</a></li>
            <li><a href=".">základní informace o botovi</a></li>
            <?php
            if (isset($_GET['pictures'])) {
                echo '<li><b><a href="?">skrýt profilové fotografie uživatelů</a></b></li>';
            } else {
                echo '<li><b><a href="?pictures=1">zobrazit profilové fotografie uživatelů</a></b></li>';
            }
            ?>
        </ul>
    </section>
    <section>
        <ul>
            <li>Celkový počet uživatelů: <b><?= $uniqueUsers ?></b>
                <br>(to je přibližně <b><?= str_replace(".", ",", round(($uniqueUsers / $numberOfStudents * 100))) ?></b> % studentů školy)
            </li>
        </ul>
    </section>
    <section>
        <h2>Suplování</h2>
        <div>
            <?php
            if (isset($_GET['pictures'])) {
                foreach ($ids['suplovani'] as $id) {
                    echo '<span class="user"><img src="' . getPicture($data['suplovani'][$id], 'bot_suplovani', $secrets['fb']) . '" alt="uživatel" /></span>';
                }
            }
            ?>
        </div>
        <ul>
            <?php
            foreach ($availableClasses as $class) {
                if (isset($counter['class'][$class])) {
                    $userNumber = $counter['class'][$class];
                    $userText = czechUsers($userNumber);
                    echo "<li>$class … $userNumber $userText</li>";
                }
            }
            ?>
        </ul>
    </section>
    <section>
        <h2>Obědy</h2>
        <div>
            <?php
            if (isset($_GET['pictures'])) {
                foreach ($ids['canteen'] as $id) {
                    echo '<span class="user"><img src="' . getPicture($data['canteen'][$id], 'bot_canteen', $secrets['fb']) . '" alt="uživatel" /></span>';
                }
            }
            ?>
        </div>
        <ul>
            <?php
            $userNumber = $counter['allergens'][0];
            $userText = czechUsers($userNumber);
            echo "<li>bez alergenů … $userNumber $userText</li>";
            $userNumber = $counter['allergens'][1];
            $userText = czechUsers($userNumber);
            echo "<li>s alergeny … $userNumber $userText</li>";
            ?>
        </ul>
    </section>
</body>

</html>
