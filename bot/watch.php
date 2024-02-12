<?php
include('src/main.php');
include('reaction-functions.php');

ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php-reaction-error.log');

// docker exec -it php-apache bash
// cat /tmp/php-reaction-error.log
// tail -f /tmp/php-reaction-error.log

header('Content-Type: text/plain');

$pageId = 1971180546491940;
$token = $secrets['fb'];

$url = $fbGraphApiPath . $pageId . '/conversations?fields=participants%2Cmessages.limit(1)%7Bmessage%2Cfrom%7D&limit=5&access_token=' . $token;
$result = customCurl($url);
$decoded = json_decode($result, true);

$url = $fbGraphApiPath . 'me/messages?access_token=' . $token;

foreach ($decoded['data'] as $conversation) {
    echo '> ';

    if (
        $conversation['messages']['data'][0]['from']['id'] != $pageId
        && !empty($conversation['participants']['data'][0]['id'])
        && !empty($conversation['messages']['data'][0]['message'])
    ) {
        echo 'message sent';
        $sender = $conversation['participants']['data'][0]['id'];
        $message = $conversation['messages']['data'][0]['message'];
        $payload = null;
        $message = getMessage($sender, $payload, $message, $availableClasses, $token, $url, $secrets['admin_messenger_id']);
        sendMessage($message, $sender, $url);
    }

    echo PHP_EOL;
}
