<?php
ignore_user_abort(true);
include('src/admin.php');

function messageTo($case) {
    switch ($case) {
        case 'all':
            return 'všem uživatelům';
        case 'suplovani':
            return 'odběratelům suplování';
        case 'canteen':
            return 'odběratelům jídelníčku';
        case 'classes':
            return 'studentům tříd…';
        case 'ids':
            return 'uživatelům s identifikátory…';
    }
}

if (isset($_POST['admin']) && $_POST['admin'] === $secrets['admin']) {
    if (empty($_POST['filter'])) {
        echo '<!doctype html><html lang="cs"><head><meta charset="utf-8"><title>Poslat zprávu uživatelům</title>'
            . '<style>body{font-family:sans-serif;}</style>'
            . '</head><body>';
        echo '<form method="post"><input type="hidden" name="admin" value="' . $_POST['admin'] . '" /><ul>'
            . '<li><button type="submit" name="filter" value="all">všichni uživatelé</button>'
            . '<li><button type="submit" name="filter" value="suplovani">odběratelé suplování</button>'
            . '<li><button type="submit" name="filter" value="canteen">odběratelé jídelníčku</button>'
            . '</form><form method="post"><input type="hidden" name="admin" value="' . $_POST['admin'] . '" />'
            . '<li><input type="text" name="classes" placeholder="pouze tyto třídy (oddělené čárkou)" style="width:200px" /><button type="submit" name="filter" value="classes">pokračovat</button>'
            . '</form><form method="post"><input type="hidden" name="admin" value="' . $_POST['admin'] . '" />'
            . '<li><br><textarea name="ids" placeholder="Messenger IDs (oddělené čárkou nebo zalomením řádku)" style="width:300px;height:100px;"></textarea><br><button type="submit" name="filter" value="ids">pokračovat</button>'
            . '</ul></form>';
        echo '</body></html>';
    } else if (empty($_POST['message'])) {
        echo '<!doctype html><html lang="cs"><head><meta charset="utf-8"><title>Poslat zprávu uživatelům</title>'
            . '<style>body{font-family:sans-serif;}</style>'
            . '</head><body>';
        echo '<h1>Zadejte zprávu určenou ' . messageTo($_POST['filter']) . '</h1>';
        $classes = '';
        $ids = '';

        if ($_POST['filter'] == 'classes') {
            $classes = join(',', processClasses($_POST['classes']));
            echo '<p>' . $classes . '</p>';
        } else if ($_POST['filter'] == 'ids') {
            $ids = join(',', processIds($_POST['ids']));
            echo '<p>' . $ids . '</p>';
        }

        echo '<form method="post">'
            . '<input type="hidden" name="admin" value="' . $_POST['admin'] . '" />'
            . '<input type="hidden" name="filter" value="' . $_POST['filter'] . '" />'
            . '<input type="hidden" name="classes" value="' . $classes . '" />'
            . '<input type="hidden" name="ids" value="' . $ids . '" />'
            . '<textarea name="message" placeholder="zpráva" style="width:300px;height:100px;"></textarea><br><input type="password" name="admin" placeholder="klíč administrátora" /><input type="submit" value="odeslat" /></form>';
        echo '</body></html>';
    } else {
        header('Content-Type: application/json');

        if ($_POST['filter'] == 'ids') {
            $sendTo = processIds($_POST['ids']);
        } else {
            $sendTo = array_keys(processFilters($_POST));
        }

        $url = $fbGraphApiPath . 'me/messages?access_token=' . $secrets['fb'];
        echo '[';

        foreach ($sendTo as $messenger_id) {
            $jsonData = '{
                "recipient":{
                "id":"' . $messenger_id . '"
                },
                "message":{
                "text":"' . $_POST['message'] . '"
                },
                "messaging_type": "MESSAGE_TAG",
                "tag": "ACCOUNT_UPDATE"
            }';

            echo customCurl($url, $jsonData) . ',';
        }

        echo '{}]';
    }
} else {
    echo $adminPasswordForm;
}
