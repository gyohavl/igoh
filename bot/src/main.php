<?php
date_default_timezone_set('Europe/Prague');
$secrets = include(__DIR__ . '/../secrets.php');
include(__DIR__ . '/parser.php');
include(__DIR__ . '/bakalari.php');

$availableClasses = array("1.A", "1.B", "1.C", "2.A", "2.B", "2.C", "3.A", "3.B", "3.C", "4.A", "4.B", "4.C", "5.A", "5.B", "6.A", "6.B", "7.A", "7.B", "8.A", "8.B");
$fbGraphApiPath = 'https://graph.facebook.com/v17.0/';

function customCurl($url, $jsonData = null) {
    global $secrets;
    $c = curl_init();

    if ($jsonData) {
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    }

    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($c);
    curl_close($c);

    $result = !empty($result) ? $result : '{}';
    $displayUrl = str_replace($secrets['fb'], '<key>', $url);

    if (
        (isset($_GET['admin']) && $_GET['admin'] == $secrets['admin'])
        || (isset($_POST['admin']) && $_POST['admin'] == $secrets['admin'])
    ) {
        // set or send
        return "{\"url\": \"$displayUrl\", \"data\": $jsonData, \"result\": $result}";
    } else {
        if ($jsonData == null) {
            // getting user info
            return $result;
        } else {
            // send without auth
            return '{}';
        }
    }
}

function getConfigValue($name) {
    $result = sql('SELECT `value` FROM `bot_config` WHERE `name`=?;', true, array($name));
    return (isset($result[0]) && isset($result[0][0])) ? $result[0][0] : null;
}

function setConfigValue($name, $value) {
    $query = 'UPDATE `bot_config` SET `value`=? WHERE `name`=?;';
    sql($query, false, array($value, $name));
}

function sql($sql, $fetch = true, $params = array()) {
    global $secrets;
    $db = new PDO('mysql:dbname=' . $secrets['dbname'] . ';charset=utf8mb4;host=' . $secrets['dbhost'], $secrets['dbuser'], $secrets['dbpass']);
    $db->exec('set names utf8mb4');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $query = $db->prepare($sql);
    $query->execute($params);
    return $fetch ? $query->fetchAll() : true;
}
