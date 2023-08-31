<?php
include('src/main.php');

$numberOfStudents = 590;
$defaultPicture = '/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAoKCgoKCgsMDAsPEA4QDxYUExMUFiIYGhgaGCIzICUgICUgMy03LCksNy1RQDg4QFFeT0pPXnFlZXGPiI+7u/sBCgoKCgoKCwwMCw8QDhAPFhQTExQWIhgaGBoYIjMgJSAgJSAzLTcsKSw3LVFAODhAUV5PSk9ecWVlcY+Ij7u7+//CABEIAGQAZAMBIgACEQEDEQH/xAAaAAEAAgMBAAAAAAAAAAAAAAAABQYBAgME/9oACAEBAAAAALuAAAADtK9fFGAOln6EXCAS0yNangExLjFSwDvZ8kfAAJiXaVjkBNyg513zBLTIOVY0NrVuBEQ57rCA8lbJSbAaVN//xAAUAQEAAAAAAAAAAAAAAAAAAAAA/9oACAECEAAAAAAAAAAAAH//xAAUAQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDEAAAAAAAAAAAAH//xAAvEAACAQAGBwcFAAAAAAAAAAABAgMABAURIDESEyEwQVFxECIjUoGRsUBCYWKh/9oACAEBAAE/AProYJJ2uRep5USzIwPEdmP42UNnVUrk4PWk9mSRrpxnTHLI7mGNppFRcyaRRJCiog2DBadVUg1iNbvMNxZSAvK54KAPXCyq6MpyKke9CCCRjspgNep4gEYSQASaMb2J5k/3HVptRMr5jj0NFZXUMDeCMFoVgRxmMHvv8bmzxW1+zwT5vkds+uCeEFLcL6TrMsja4MHJ47ip1EIA8ovbgvLDLDFOmi6g0rVVerPcdqnJsVm1YOdc+QNy454kmjKMPXkaSI0TsjZg4EUu6qBeWIA6mkaLEiouQAG4tSLakwGfdbBZ6aVaU3XhQW3NdTTqsvMC/wBsFlEiaQjPQ3MovilH6N2//8QAFBEBAAAAAAAAAAAAAAAAAAAAUP/aAAgBAgEBPwBH/8QAFBEBAAAAAAAAAAAAAAAAAAAAUP/aAAgBAwEBPwBH/9k=';

function getPicture($data, $defaultPicture) {
    if (str_starts_with($data['picture'], 'http')) {
        return $data['picture'];
    } else {
        return 'data:image/jpeg;base64, ' . $defaultPicture;
    }
}

function regenerateImageUrls($token) {
    global $fbGraphApiPath;

    foreach (array('bot_suplovani', 'bot_canteen') as $table) {
        $data = sql("SELECT `id`, `messenger_id`, `first_name`, `picture` FROM `$table`;");

        foreach ($data as $row) {
            if (isset($row['picture']) && str_starts_with($row['picture'], 'http') && isImageUrlWorking($row['picture'])) {
                if (empty($row['first_name'])) {
                    $userResponse = customCurl($fbGraphApiPath . $row['messenger_id'] . "?fields=first_name,last_name&access_token=" . $token);
                    $user = json_decode($userResponse, true);

                    if (isset($user['first_name'])) {
                        sql("UPDATE `$table` SET `first_name`=?, `last_name`=? WHERE `id`=?;", false, array($user['first_name'], $user['last_name'], $row['id']));
                        echo "opraveno jméno\n";
                    } else {
                        echo "v pořádku\n";
                    }
                } else {
                    echo "v pořádku\n";
                }
            } else {
                $userResponse = customCurl($fbGraphApiPath . $row['messenger_id'] . "?fields=first_name,last_name,profile_pic&access_token=" . $token);
                $user = json_decode($userResponse, true);

                if (isset($user['profile_pic'])) {
                    sql("UPDATE `$table` SET `first_name`=?, `last_name`=?, `picture`=? WHERE `id`=?;", false, array($user['first_name'], $user['last_name'], $user['profile_pic'], $row['id']));
                    echo "opraveno\n";
                } else {
                    sql("UPDATE `$table` SET `picture`=? WHERE `id`=?;", false, array('', $row['id']));
                    echo "chyba\n";
                }
            }
        }
    }
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

if (isset($_GET['default'])) {
    header('Content-Type: image/jpg');
    echo base64_decode($defaultPicture);
    exit;
} else if (isset($_GET['regenerate']) || isset($_POST['regenerate'])) {
    if (
        (isset($_GET['admin']) && $_GET['admin'] == $secrets['admin'])
        || (isset($_POST['admin']) && $_POST['admin'] == $secrets['admin'])
    ) {
        header('Content-Type: text/plain');
        echo regenerateImageUrls($secrets['fb']);
    } else {
        echo '<!doctype html><html><body><form method="post"><input type="password" name="admin" placeholder="klíč administrátora" /><input type="hidden" name="regenerate" value="1" /><input type="submit" value="odeslat" /></form></body></html>';
    }
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
                // echo '<li><b><a href="?pictures=1">zobrazit profilové fotografie uživatelů</a></b></li>';
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
                    echo '<span class="user"><img src="' . getPicture($data['suplovani'][$id], $defaultPicture) . '" onerror="this.src=\'?default=1\';" alt="uživatel" /></span>';
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

            $suplovaniTotal = count($ids['suplovani']);
            $stText = czechUsers($suplovaniTotal);
            echo "<li class=\"total\">celkem … $suplovaniTotal $stText</li>";
            ?>
        </ul>
    </section>
    <section>
        <h2>Obědy</h2>
        <div>
            <?php
            if (isset($_GET['pictures'])) {
                foreach ($ids['canteen'] as $id) {
                    echo '<span class="user"><img src="' . getPicture($data['canteen'][$id], $defaultPicture) . '" onerror="this.src=\'?default=1\';" alt="uživatel" /></span>';
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
            $canteenTotal = count($ids['canteen']);
            $ctText = czechUsers($canteenTotal);
            echo "<li class=\"total\">celkem … $canteenTotal $ctText</li>";
            ?>
        </ul>
    </section>
</body>

</html>
