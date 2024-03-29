<?php
$shortener = array(
    'example' => 'https://www.igoh.cz/bot/users.php', // https://www.igoh.cz/s/example redirects to https://www.igoh.cz/bot/users.php
    'burza' => 'https://docs.google.com/spreadsheets/d/17uzXVMzUiakpkmK6Gt65WigDqnwTEjci8lXkLeT-pLg/edit',
);

if (!empty($_GET['s'])) {
    $s = trim($_GET['s'], '/'); // https://www.igoh.cz/s/example/ is treated like https://www.igoh.cz/s/example

    if (!empty($shortener[$s])) {
        @file_get_contents('https://www.vitkolos.cz/matomo/matomo.php?idsite=2&rec=1&action_name=Shortener&url=https://www.igoh.cz/s/' . urlencode($s)
            . '&ua=' . urlencode($_SERVER['HTTP_USER_AGENT']) . '&lang=' . urlencode($_SERVER['HTTP_ACCEPT_LANGUAGE']) . '&rand=' . rand() . '&apiv=1');
        header('Location: ' . $shortener[$s]);
        exit;
    }
}

header('Location: /');
exit;
