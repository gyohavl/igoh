<?php
ignore_user_abort(true);
header('Content-Type: text/plain');
$included = true;
require_once('index.php');

$file = getSuplovani();
$file = explode('<h1>Změny v rozvrhu', $file, 2);
$file = isset($file[1]) ? $file[1] : false;
$lastFile = file_get_contents('data/last.html');

if ($file !== false) {
    logError(false);

    if ($lastFile != $file) {
        file_put_contents('data/last.html', $file);
        file_get_contents('https://suply.herokuapp.com/suplovani-send-new.php');
        echo 'Odesláno!';
    } else {
        echo 'Beze změn.';
    }
} else {
    echo 'Nelze načíst obsah suplování.';
    reportError();
    logError(true);
}

function reportError() {
    $errorFile = file_get_contents('data/error.txt');

    if (!$errorFile) {
        file_get_contents('https://suply.herokuapp.com/suplovani-send-new.php');
    }
}

function logError($isError) {
    file_put_contents('data/error.txt', $isError ? 'error' : '');
}
