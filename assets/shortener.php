<?php
$shortener = array(
	'cetba' => 'https://docs.google.com/spreadsheets/d/1drkg0qkQZ3AyMmxbKJq9_tKPwGLyrteiqnTcIj7urkw/edit?usp=sharing',
	'cetba-kopirovat' => 'https://docs.google.com/spreadsheets/u/1/d/1drkg0qkQZ3AyMmxbKJq9_tKPwGLyrteiqnTcIj7urkw/copy'
);

if (!empty($_GET['s']) && $shortener[$_GET['s']]) {
	header('Location: ' . $shortener[$_GET['s']]);
} else {
	header('Location: /');
}
