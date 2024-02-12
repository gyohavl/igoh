<?php
include('src/main.php');
include('reaction-functions.php');

ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php-reaction-error.log');

// docker exec -it php-apache bash
// cat /tmp/php-reaction-error.log
// tail -f /tmp/php-reaction-error.log

if (isset($_REQUEST['hub_challenge'])) {
    $challenge = $_REQUEST['hub_challenge'];
    $verify_token = $_REQUEST['hub_verify_token'];

    if ($verify_token === $secrets['fb_verify_token']) {
        echo $challenge;
        exit;
    }
}

$input = json_decode(file_get_contents('php://input'), true);
@$sender = $input['entry'][0]['messaging'][0]['sender']['id'];
@$message = $input['entry'][0]['messaging'][0]['message']['text'];
@$payload = $input['entry'][0]['messaging'][0]['postback']['payload'];
$token = $secrets['fb'];
$url = $fbGraphApiPath . 'me/messages?access_token=' . $token;

$message = getMessage($sender, $payload, $message, $availableClasses, $token, $url, $secrets['admin_messenger_id']);
sendMessage($message, $sender, $url);
