<?php
ignore_user_abort(true);
header('Content-Type: application/json');

include('src/main.php');

// ##############
// # JÍDELNÍČEK #
// ##############

if (isset($_POST['canteen']) && $_POST['canteen'] == $secrets['canteen']) {
    $url = 'https://graph.facebook.com/?access_token=' . $secrets['fb'];
    $file = file_get_contents('https://jidelna.gyohavl.cz/faces/login.jsp');
    $data = sql("SELECT * FROM bot_canteen");
    $jsonData = array();
    $arrayLength = 0;
    $i = 0;

    foreach ($data as $student) {
        $messenger_id = $student["messenger_id"];
        $allergens = $student["allergens"];
        $messages = obedy($allergens, $file);

        foreach ($messages as $key => $message) {
            $message = preg_replace('/<br>/', '\n', $message);
            $message = preg_replace('/"/', '\"', $message);
            $depends_on = $key ? '"depends_on": "' . $messenger_id . '_' . ($key - 1) . '",' : '';
            $jsonData[] = '{
                "method":"POST",
                "relative_url":"me/messages",
                "name": "' . $messenger_id . '_' . $key . '",
                ' . $depends_on . '
                "body": "recipient={\"id\": \"' . $messenger_id . '\"}&message={\"text\": \"' . trim(json_encode($message), '"') . '\"}&messaging_type=MESSAGE_TAG&tag=CONFIRMED_EVENT_UPDATE"
            }';

            if ($i == 0) {
                $arrayLength++;
            }
        }

        $i++;
    }

    $chunks = array_chunk($jsonData, floor(50 / $arrayLength) * $arrayLength);
    echo '[';

    foreach ($chunks as $chunk) {
        echo customCurl($url, '{"batch":[' . implode(",", $chunk) . ']}');
        echo ',';
    }

    echo '{}]';
} else if (isset($_GET['check'])) {
    header('Content-Type: text/plain');
    echo preg_replace('/(\s)\s+/', '$1', html_entity_decode(strip_tags(getSuplovani()))) . "\n\n";
    $obedy = obedy(true, file_get_contents('https://jidelna.gyohavl.cz/faces/login.jsp'));
    echo str_replace('<br>', "\n", implode("\n", $obedy));
} else {

    // #############
    // # SUPLOVÁNÍ #
    // #############

    $url = $fbGraphApiPath . 'me/messages?access_token=' . $secrets['fb'];
    $file = getSuplovani();
    $newMessages = array();
    // get old messages from db
    $oldMessages = getConfigValue('messages');
    $oldMessages = unserialize($oldMessages);
    $errorNotificationOn = false;

    if ($file !== false && !str_contains($file, '[ERROR]')) {
        $send = plain('doprazdnin', $file);
        echo '{"status":"' . ($send ? "school_day" : "vacation") . '","messages":[';

        // generate new messages
        foreach ($availableClasses as $class) {
            $message = plain($class, $file, true);
            $message = preg_replace('/<br>/', '\n', $message);
            $newMessages[$class] = $message;
        }

        $output = sql("SELECT * FROM bot_suplovani");
        // go over students
        foreach ($output as $student) {
            $messenger_id = $student["messenger_id"];
            $class = $student["class"];

            // check if message has changed
            if ($newMessages[$class] != $oldMessages[$class]) {
                $message = convertNoChanges($newMessages[$class]);
                $message = preg_replace('/<br>/', '\n', $message);

                if ($send) {
                    $jsonData = '{
                        "recipient":{
                        "id":"' . $messenger_id . '"
                        },
                        "message":{
                        "text":"' . $message . '"
                        },
                        "messaging_type": "MESSAGE_TAG",
                        "tag": "CONFIRMED_EVENT_UPDATE"
                    }';

                    echo customCurl($url, $jsonData) . ',';
                }
            }
        }

        echo '{}]}';

        // update db
        $smessages = serialize($newMessages);
        setConfigValue('messages', $smessages);

        $errorStatusChanged = setErrorStatus(false);

        if ($errorStatusChanged && $errorNotificationOn) {
            // notify Vítek
            $jsonData = '{
                "recipient":{
                "id":"' . $secrets['admin_messenger_id'] . '"
                },
                "message":{
                "text":"[OK] funkčnost obnovena"
                },
                "messaging_type": "MESSAGE_TAG",
                "tag": "ACCOUNT_UPDATE"
            }';
            $result = customCurl($url, $jsonData);
        }
    } else {
        $errorStatusChanged = setErrorStatus(true);

        if ($errorStatusChanged && $errorNotificationOn) {
            // notify Vítek
            $jsonData = '{
                "recipient":{
                "id":"' . $secrets['admin_messenger_id'] . '"
                },
                "message":{
                "text":"' . $file . '"
                },
                "messaging_type": "MESSAGE_TAG",
                "tag": "ACCOUNT_UPDATE"
            }';
            echo customCurl($url, $jsonData);
        } else {
            echo '{"result":"' . $file . '"}';
        }
    }
}

function setErrorStatus($present) {
    $past = getConfigValue('error');
    $past = ($past === '1');

    if ($present == $past) {
        return false;
    } else {
        if ($present) {
            setConfigValue('error', '1');
        } else {
            setConfigValue('error', '0');
        }

        return true;
    }
}
