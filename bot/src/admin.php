<?php
include(__DIR__ . '/main.php');
$adminPasswordForm = '<!doctype html><html><body><form method="post"><input type="password" name="admin" placeholder="klíč administrátora" /><input type="submit" value="odeslat" /></form></body></html>';

function addUserToArray($array, $user) {
    if (!isset($array[$user['messenger_id']])) {
        $array[$user['messenger_id']] = array();
    }

    if (isset($user['class'])) {
        $user['suplovani'] = true;
    } else {
        $user['canteen'] = true;
    }

    foreach (array('first_name', 'last_name', 'class', 'allergens', 'picture', 'canteen', 'suplovani') as $key) {
        if (!empty($user[$key])) {
            $array[$user['messenger_id']][$key] = $user[$key];
        } else {
            if (!isset($array[$user['messenger_id']][$key])) {
                $array[$user['messenger_id']][$key] = null;
            }
        }
    }

    return $array;
}

function getUserArray($queries) {
    $allUsers = array();

    foreach ($queries as $query) {
        $users = sql($query);

        foreach ($users as $user) {
            $allUsers = addUserToArray($allUsers, $user);
        }
    }

    return $allUsers;
}

function processClasses($classString) {
    global $availableClasses;

    $classesToGet = array();
    $classArray = explode(',', $classString);

    foreach ($classArray as $class) {
        $trimmedClass = trim($class);

        if (in_array($trimmedClass, $availableClasses)) {
            $classesToGet[] = $trimmedClass;
        }
    }

    return $classesToGet;
}

function processIds($idsString) {
    $idsString = str_replace(PHP_EOL, ',', $idsString);
    $idsString = preg_replace('/\s+/', '', $idsString);
    return explode(',', $idsString);
}

function processFilters($params) {
    $queries = ["SELECT * FROM bot_suplovani", "SELECT * FROM bot_canteen"];

    if (isset($params['filter'])) {
        switch ($params['filter']) {
            case 'all':
                break;

            case 'suplovani':
                $queries = [$queries[0]];
                break;

            case 'canteen':
                $queries = [$queries[1]];
                break;

            case 'classes':
                $classString = isset($params['classes']) ? $params['classes'] : '';
                $classesToGet = processClasses($classString);

                if (count($classesToGet) > 0) {
                    $queries = [$queries[0] . ' WHERE class IN (\'' . join('\',\'', $classesToGet) . '\')'];
                } else {
                    $queries = array();
                }
                break;

            default:
                $queries = array();
                break;
        }
    }

    return getUserArray($queries);
}
