<?php
require_once('../assets/shared.php');

$htmlResult = main();

// redirect
if (isset($_GET["redirect"])) {
	$urlNoApi = substr($url, 0, -4);
	header("Location: $urlNoApi" . $_GET["redirect"]);
	exit;
}
// end redirect

// 8.B (get class)
if (isset($_GET["getclass"])) {
	header("Content-Type: text/plain");
	exit;
}
// end 8.B (get class)

function loadContent($bearer) {
	global $url;

	// redirect
	if (isset($_GET["redirect"])) {
		$webResponse = sendRequest(false, $bearer, '3/logintoken');

		if ($webResponse[0]) {
			$trimmedWebToken = trim($webResponse[1], '"');

			if (strpos($trimmedWebToken, '{') === false) {
				$redirLink = empty($_GET["redirect"]) ? "{$url}3/login/$trimmedWebToken" : "{$url}3/login/$trimmedWebToken?returnUrl=" . $_GET["redirect"];
				header("Location: $redirLink");
				exit;
			}
		}
	}
	// end redirect

	$userResponse = sendRequest(false, $bearer, '3/user');
	$name = '???';

	if ($userResponse[0]) {
		$decodedU = json_decode($userResponse[1]);

		if (isset($decodedU->FullName)) {
			$name = $decodedU->FullName;
		}
	}

	// 8.B (get class)
	if (isset($_GET["getclass"]) && $name != '???') {
		header("Content-Type: text/plain");
		echo substr($name, -3);
		exit;
	}
	// end 8.B (get class)

	$marksResponse = sendRequest(false, $bearer, '3/marks');

	if ($marksResponse[0]) {
		$decodedM = json_decode($marksResponse[1]);

		if (isset($decodedM->Subjects)) {
			return echoHeader($decodedM->Subjects, $name);
		} else {
			if (isset($_COOKIE['prumer_refresh_token'])) {
				return loginConditions(array($_COOKIE['prumer_refresh_token'], false));
			} else {
				$_SESSION['t'] = '';
				return msg('Přihlášení vypršelo.');
			}
		}
	} else {
		return msg('Problém se známkami.');
	}
}

function echoHeader($subjects, $name) {
	$result = '';
	$jsData = '';
	$result .= '<header><nav><div class="top-row"><h1><a href="..">iGOH</a><a href="#" onclick="fill(event, \'\', \'\');">Průměr známek</a></h1><div><a href="?odhlasit">' . $name . ' (odhlásit se)</a><a href="?redirect=next/prubzna.aspx">→ Bakaláři</a>';
	// $result .= substr($name, -3) == '8.B' ? '<a href="../fond">TF</a>' : '';
	$result .= '</div></div>';
    // $result .= '<div class="top-row"><a href="https://www.instagram.com/umzene_tusky/" onclick="gtag(\'event\', \'click\', {\'event_label\': \'https://www.instagram.com/umzene_tusky/?ref=prumer\',\'transport_type\': \'beacon\'});">Dražba oktávy B</a></div>';
	// $result .= '<div class="bottom-row">';

	foreach ($subjects as $key => $subject) {
		$marksArray = array();
		$weightsArray = array();

		foreach ($subject->Marks as $mark) {
			$marksArray[] = $mark->MarkText;
			$weightsArray[] = $mark->Weight;
		}

		$marksString = implode(' ', $marksArray);
		$weightsString = implode(' ', $weightsArray);

		// $result .= '<a href="#" onclick="fill(event, \'' . $marksString . ' \', \'' . $weightsString . ' \');">' . $subject->Subject->Abbrev . '</a>';
		$jsData .= "{predmet: '{$subject->Subject->Abbrev}', znamky: '$marksString ', vahy: '$weightsString '}, ";
	}

	// $result .= '<a href="#" onclick="getAll(event);">vše</a></div>';
	$result .= '<div id="menu"></div><script>let data = [' . $jsData .  '];</script></nav></header>';
	$result .= '
		<div class="obsah">
			<div class="rolovaci">
				<label for="znamky">Známky:</label>
				<input type="tel" id="znamky" onkeyup="keyUp(this, event);">
				<label for="vahy">Váhy:</label>
				<input type="tel" id="vahy" onkeyup="keyUp(this, event);">
			</div>
			<small id="napoveda"></small>
			<button type="button" onclick="calculate();">Vypočítat průměr</button>
			<div id="vysledek" class="vysledek"></div>
		</div>
		<!--<div class="obsah">' . getIframe() . '</div>-->
		';
	return $result;
}

function msg($text = '') {
	$header = '<header><nav class="uzky"><h1><a href="..">iGOH</a><a href=".">Průměr známek</a></h1></nav></header>';
	$form = '
		<form method="post">
			<label for="username">Uživatelské jméno:</label>
			<input type="text" id="username" name="username" autofocus>
			<label for="password">Heslo:</label>
			<input type="password" id="password" name="password">
			<button type="submit">Přihlásit se</button>
		</form>
		<small>Tento nástroj jsem vytvořil já, <a href="https://www.kolos.ga" class="link">Vít Kološ</a>. Studentům Gymnázia Olgy Havlové je k&nbsp;dispozici zcela zdarma. Pokud mi chceš pomoct s&nbsp;vývojem, <a href="mailto:vit.kolos@gmail.com" class="link">ozvi se mi</a>.</small>
		';
	return $header . wrap($text) . wrap($form) . wrap(getIframe());
}

function wrap($it) {
	return $it ? '<div class="obsah uzky">' . $it . '</div>' : '';
}

function getIframe() {
	// return '<div style="height:57px;overflow:hidden"><iframe src="https://www.igoh.tk/#content" style="border:0;height:67px;width:100%;opacity:.5"></iframe></div>';
    return '';
}
?>
<!DOCTYPE html>
<html lang="cs" dir="ltr">

<head>
	<meta charset="utf-8">
	<title>Průměr známek – iGOH</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="description" content="Výpočet průměru známek pro studenty Gymnázia Olgy Havlové.">

	<link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	<meta name="theme-color" content="#795548">
	<meta property="og:image" content="https://www.igoh.tk/assets/og.png">

	<link rel="stylesheet" href="style.css">

	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-57876300-5"></script>
	<script>
		window.dataLayer = window.dataLayer || [];

		function gtag() {
			dataLayer.push(arguments);
		}
		gtag('js', new Date());
		gtag('config', 'UA-57876300-5', {
			'anonymize_ip': true,
			'client_storage': 'none'
		});
	</script>
</head>

<body>
	<?php
	echo $htmlResult;
	?>

	<script src="script.js"></script>
</body>

</html>
