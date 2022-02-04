<?php
$jidelnaURL = "https://jidelna.gyohavl.cz/"; //global variable

if(!empty($_COOKIE["susenky"]) && !empty($_POST["time"]) && !empty($_POST["token"]) && !empty($_POST["ID"]) && !empty($_POST["day"]) && !empty($_POST["type"])) {
	$q = $_COOKIE["susenky"];
	$u = "time=".$_POST["time"]."&token=".preg_replace("/\+/", "%2B", $_POST["token"])."&ID=".$_POST["ID"]."&day=".$_POST["day"]."&type=".$_POST["type"];
	$r = req($q, $u);
	echo $r[0];
	echo "\n".$u;
}

function req($cookies = null, $u) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $GLOBALS["jidelnaURL"]."faces/secured/db/dbProcessOrder.jsp?".$u."&week=&terminal=false&keyboard=false&printer=false&_=1552727912460");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if ($cookies) {
		curl_setopt($ch, CURLOPT_COOKIE, $cookies);
	}
	curl_setopt($ch, CURLOPT_HEADER, 1);
	$result = curl_exec($ch);
	curl_close($ch);
	list($header, $body) = explode("\r\n\r\n", $result, 2);
	return array($body, $header);
}