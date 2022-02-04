<?php
require_once("assets/loginfunctions.php");

if(!empty($_POST["username"])) {
	if(isset($_POST["remembername"])) {
		setcookie("username", $_POST["username"], time() + (86400 * 30), "/");
	}
	if(isset($_POST["autolog"])) {
		setcookie("autolog", 1, time() + (86400 * 30), "/");
	} else {
		setcookie("autolog", 0, 10, "/");
	}
	if(empty($_POST["password"])) {
		$password = $_POST["username"];
	} else {
		$password = $_POST["password"];
	}
	$p = csrfReq();
	if(isset($_POST["rememberme"])) {
		$q = loginReq($p[0], $p[1], $_POST["username"], $password);
	} else {
		$q = loginReq($p[0], $p[1], $_POST["username"], $password, false);
	}
	setcookie("susenky", implode(";", $q), time() + (86400 * 30), "/");
	header("Location: .");
} else if(isset($_GET["autolog"]) && !empty($_COOKIE["username"])) {
	$p = csrfReq();
	$q = loginReq($p[0], $p[1], $_COOKIE["username"], $_COOKIE["username"], false);
	setcookie("susenky", implode(";", $q), time() + (86400 * 30), "/");
	header("Location: assets/get.php");
} else {
	if(isset($_POST["remembername"])) {
		setcookie("username", "", 10, "/");
	} elseif(isset($_GET["logout"])) {
		setcookie("username", "", 10, "/");
		setcookie("susenky", "", 10, "/");
		setcookie("autolog", 0, 10, "/");
	}
	header("Location: .");
}

function csrfReq() {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $GLOBALS["jidelnaURL"]."faces/login.jsp");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	$result = curl_exec($ch);
	curl_close($ch);
	list($header, $body) = explode("\r\n\r\n", $result, 2);
	$cookies = getCookies($header);
	preg_match('/<input +type="hidden" +name="_csrf" +value="([^\"]+) *"\/>/', $body, $m);
	return array($cookies, $m[1]);
}

function loginReq($cookies = null, $csrf = "", $username = "", $password = "", $rememberMe = true) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $GLOBALS["jidelnaURL"]."j_spring_security_check");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if ($cookies) {
		curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookies));
	}
	curl_setopt($ch, CURLOPT_POST, 1);
	if($rememberMe) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, "j_username=".$username."&j_password=".$password."&_spring_security_remember_me=on&terminal=false&targetUrl%3D%2Ffaces%2Fsecured%2Fmonth.jsp%3Fterminal%3Dfalse%26menuStatus%3Dtrue%26printer%3Dfalse%26keyboard%3Dfalse&_csrf=".urlencode($csrf));
	} else {
		curl_setopt($ch, CURLOPT_POSTFIELDS, "j_username=".$username."&j_password=".$password."&_spring_security_remember_me=false&terminal=false&targetUrl%3D%2Ffaces%2Fsecured%2Fmonth.jsp%3Fterminal%3Dfalse%26menuStatus%3Dtrue%26printer%3Dfalse%26keyboard%3Dfalse&_csrf=".urlencode($csrf));
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/x-www-form-urlencoded'
	));
	curl_setopt($ch, CURLOPT_HEADER, 1);
	$result = curl_exec($ch);
	curl_close($ch);
	list($header, $body) = explode("\r\n\r\n", $result, 2);
	$cookies = getCookies($header);
	return $cookies;
}