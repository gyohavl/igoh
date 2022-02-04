<?php
$jidelnaURL = "https://jidelna.gyohavl.cz/"; //global variable

function jidelnicekReq($cookies = null) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $GLOBALS["jidelnaURL"]."faces/secured/month.jsp?terminal=false&keyboard=false&printer=false");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if ($cookies) {
		curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookies));
	}
	curl_setopt($ch, CURLOPT_HEADER, 1);
	$result = curl_exec($ch);
	curl_close($ch);
	list($header, $body) = explode("\r\n\r\n", $result, 2);
	$cookies = getCookies($header);
	return array($body, $cookies, $header);
}

function getCookies($header) {
	$end = strpos($header, 'Content-Type');
	$start = strpos($header, 'Set-Cookie');
	if($start !== false) {
		$parts = explode('Set-Cookie:', substr($header, $start, $end - $start));
		$cookies = array();
		foreach ($parts as $co) {
			$cd = explode(';', $co);
			if (!empty($cd[0])) {
				$cookies[] = $cd[0];
			}
		}
	}
	else {
		$cookies = array();
	}
	return $cookies;
}