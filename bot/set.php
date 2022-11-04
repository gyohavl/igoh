<?php
include('src/main.php');

$url = 'https://graph.facebook.com/v6.0/me/messenger_profile?access_token=' . $secrets['fb'];
$jsonData = '{
	"persistent_menu": [
		{
			"locale": "default",
			"composer_input_disabled": false,
			"call_to_actions": [
				{
					"title": "Nápověda",
					"type": "postback",
					"payload": "NAPOVEDA"
				},
				{
					"type": "web_url",
					"title": "iGOH",
					"url": "https://www.igoh.tk/",
					"webview_height_ratio": "full"
				}
			]
		}
	],
	"get_started": {
		"payload": "ZACIT"
	},
	"greeting": [
		{
			"locale": "default",
			"text": "Hlídám suplování za vás. Pro nastavení upozornění zadejte třídu (např. 4.B). Pro zrušení notifikací napište x."
		}
	]
}';

if (isset($_POST['admin']) && $_POST['admin'] == $secrets['admin']) {
    header('Content-Type: application/json');
    echo customCurl($url, $jsonData);
} else {
    echo '<!doctype html><html><body><form method="post"><input name="admin" placeholder="klíč administrátora" /><input type="submit" value="odeslat" /></form></body></html>';
}
