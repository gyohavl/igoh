<?php
$shortener = array(
	'example' => 'https://www.igoh.tk/bot/users.php', // https://www.igoh.tk/s/example redirects to https://www.igoh.tk/bot/users.php
);

if (!empty($_GET['s']) && $shortener[$_GET['s']]) {
	header('Location: ' . $shortener[$_GET['s']]);
} else {
	header('Location: /');
}
