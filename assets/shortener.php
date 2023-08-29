<?php
$shortener = array(
	'example' => 'https://www.igoh.tk/bot/users.php', // https://www.igoh.tk/s/example redirects to https://www.igoh.tk/bot/users.php
    'burza' => 'https://docs.google.com/spreadsheets/d/17uzXVMzUiakpkmK6Gt65WigDqnwTEjci8lXkLeT-pLg/edit',
);

if (!empty($_GET['s']) && $shortener[$_GET['s']]) {
	header('Location: ' . $shortener[$_GET['s']]);
} else {
	header('Location: /');
}
