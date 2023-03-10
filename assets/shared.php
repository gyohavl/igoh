<?php
session_start();
$url = isset($url) ? $url : 'https://gyohavl.bakalari.cz/api/';

function main() {
	if (!empty($_POST['username']) && !empty($_POST['password'])) {
		$result = loginConditions(array($_POST['username'], $_POST['password']/*, isset($_POST['refresh']) ? true : false*/));
	} elseif (isset($_GET['odhlasit'])) {
		$_SESSION['t'] = '';
		setcookie('prumer_refresh_token', '', 10, '/');
		$result = msg('Uživatel odhlášen.');
	} elseif (!empty($_SESSION['t'])) {
		$bearer = urldecode($_SESSION['t']);
		$result = loadContent($bearer);
	} elseif (isset($_COOKIE['prumer_refresh_token'])) {
		$result = loginConditions(array($_COOKIE['prumer_refresh_token'], false));
	} else {
		$result = msg();
	}

	return $result;
}

function loginConditions($creds) {
	$loginResponse = sendRequest($creds);

	if ($loginResponse[0]) {
		$decoded = json_decode($loginResponse[1]);
		if (isset($decoded->access_token)) {
			$bearer = $decoded->access_token;
			$_SESSION['t'] = urlencode($bearer);

			// if ($creds[2] || !$creds[1]) {
			setcookie('prumer_refresh_token', $decoded->refresh_token, time() + (86400 * 30), '/');
			// }

			$result = loadContent($bearer);
		} else {
			$result = msg($creds[1] ? 'Špatné jméno nebo heslo.' : 'Přihlášení vypršelo. (c)');
			$result = $loginResponse[2] ? msg('Nastala chyba na serveru Bakalářů. ' . $loginResponse[2]) : $result;

            if (!empty($_POST['debug'])) {
                $result .= '<code>' . $loginResponse[1] . '</code>';
            }

			$_SESSION['t'] = '';
			setcookie('prumer_refresh_token', '', 10, '/');
		}
	} else {
		$result = msg('Problém s přihlášením.');
	}

	return $result;
}

function sendRequest($creds = false, $token = '', $page = '') {
	global $url;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url . ($creds ? 'login' : $page));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$headers = array();

	if ($creds) {
		curl_setopt($ch, CURLOPT_POST, 1);
        $cd0 = rawurlencode($creds[0]);
        $cd1 = rawurlencode($creds[1]);

		if ($creds[1]) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=ANDR&grant_type=password&username=$cd0&password=$cd1");
		} else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=ANDR&grant_type=refresh_token&refresh_token=$cd0");
		}

		$headers[] = 'Accept: application/json';
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
	} elseif ($token) {
		$headers[] = 'Authorization: Bearer ' . $token;
	}

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = array(true, curl_exec($ch), '');

	if (curl_errno($ch)) {
		$result[2] = 'Error: ' . curl_error($ch);
	}

	curl_close($ch);
	return $result;
}
