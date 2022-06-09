<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Prague');
require_once('data/token.php');
$url = 'https://gyohavl.bakalari.cz:8081';

function customCurl($url) {
	global $curl_timeout, $debug;
	$curl_timeout = isset($curl_timeout) ? $curl_timeout : 5;
	$c = curl_init();
	curl_setopt($c, CURLOPT_TIMEOUT, $curl_timeout);
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_COOKIEJAR, 'data/cookies');
	curl_setopt($c, CURLOPT_COOKIEFILE, 'data/cookies');
	$result = curl_exec($c);
	$http_code = curl_getinfo($c, CURLINFO_HTTP_CODE);
	$ct = curl_getinfo($c, CURLINFO_CONTENT_TYPE);
	curl_close($c);
	return ($http_code == 200 && substr($ct, 0, 9) == 'text/html') ? $result : "";
}

function loginConditions($creds) {
	$loginResponse = sendRequest($creds);
	if ($loginResponse[0]) {
		$decoded = json_decode($loginResponse[1]);
		if (isset($decoded->access_token)) {
			$bearer = $decoded->access_token;
			if (isset($decoded->refresh_token)) {
				file_put_contents('data/token.php', "<?php\n\$refreshToken = '$decoded->refresh_token';\n");
			}
			return [true, $bearer];
		}
	}

	return [false, 'chyba'];
}

function sendRequest($creds = false, $token = '', $page = '') {
	global $url;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "$url/api/" . ($creds ? 'login' : $page));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$headers = array();
	if ($creds) {
		curl_setopt($ch, CURLOPT_POST, 1);
		if ($creds[1]) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=ANDR&grant_type=password&username=$creds[0]&password=$creds[1]");
		} else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=ANDR&grant_type=refresh_token&refresh_token=$creds[0]");
		}
		$headers[] = 'Accept: application/json';
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
	} elseif ($token) {
		$headers[] = 'Authorization: Bearer ' . $token;
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = [true, curl_exec($ch), ''];
	if (curl_errno($ch)) {
		$result[2] = 'Error: ' . curl_error($ch);
	}
	curl_close($ch);
	return $result;
}

function getTimestamp() {
	if (date("Gi") < 1350) {
		if (date("N", strtotime("today")) < 6) {
			return strtotime("today") * 1000;
		} else {
			return strtotime("next Monday") * 1000;
		}
	} else {
		if (date("N", strtotime("today")) < 5) {
			return strtotime("tomorrow") * 1000;
		} else {
			return strtotime("next Monday") * 1000;
		}
	}
}

$timestamp = getTimestamp() + 3600000 * 3; // adding span because of timezones
$getParams = "__VIEWSTATE=M8SZ%2BTf07IXyPB6m4ymrOkBbjoRJ3pQN38QCfuTa18vZhYrfei%2BDMXzK6Li8YhgUQO86jwWpyWs5ubBJv1mJrwYvvu2EZEMiXEPF2QKWy5VmS8A%2FYfQKCeqSSyySjiFRvG0jQaKxO4RkluJVzuqe7VNFZq0AfQJcye5XzIZ5%2BekQXPIRdfaRGH8RXdfOBROM&DateEdit%24State={%26quot%3BrawValue%26quot%3B%3A%26quot%3B$timestamp%26quot%3B%2C%26quot%3BuseMinDateInsteadOfNull%26quot%3B%3Afalse}&DateEdit=&FilterDropDown_VI=1&FilterDropDown=";
$page = customCurl("$url/next/zmeny.aspx?$getParams");

if (strpos($page, 'Změny') === false) {
	$login = loginConditions([$refreshToken, false]);

	if ($login[0]) {
		$webResponse = sendRequest(false, $login[1], '3/logintoken');

		if ($webResponse[0]) {
			$trimmedWebToken = trim($webResponse[1], '"');

			if (strpos($trimmedWebToken, '{') === false) {
				$redirLink = "$url/api/3/login/$trimmedWebToken?returnUrl=next/zmeny.aspx";
				$page = customCurl($redirLink);

				if (strpos($page, 'Změny') !== false) {
					$page = customCurl("$url/next/zmeny.aspx?$getParams");

					if (strpos($page, 'Změny') !== false) {
						echo $page;
					} else {
						echo "[ERROR] chybí změny";
					}
				} else {
					echo "[ERROR] chybí změny 2";
				}
			} else {
				echo "[ERROR] problém token";
			}
		}
	} else {
		if ($page == "") {
            echo "[ERROR] chyba serveru";
        } else {
            echo "[ERROR] chybí login";
        }
	}
} else {
	echo $page;
}
