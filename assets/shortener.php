<?php
$shortener = array(
	'example' => 'https://www.igoh.tk/bot/users.php', // https://www.igoh.tk/s/example redirects to https://www.igoh.tk/bot/users.php
	'burza' => 'https://docs.google.com/spreadsheets/d/17uzXVMzUiakpkmK6Gt65WigDqnwTEjci8lXkLeT-pLg/edit',
);

if (!empty($_GET['s'])) {
	$s = trim($_GET['s'], '/'); // https://www.igoh.tk/s/example/ is treated like https://www.igoh.tk/s/example

	if (!empty($shortener[$s])) {
		header('Location: ' . $shortener[$s]);
		exit;
	}
}

header('Location: /');
exit;
