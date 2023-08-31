<?php
require_once('../assets/shared.php');

$themeToggle = '
    <button class="theme-toggle" id="theme-toggle" title="Přepnout světlý/tmavý režim" aria-label="light" aria-live="polite">
        <svg class="sun-and-moon" aria-hidden="true" width="24" height="24" viewBox="0 0 24 24"><mask class="moon" id="moon-mask"><rect x="0" y="0" width="100%" height="100%" fill="white"></rect><circle cx="24" cy="10" r="6" fill="black"></circle></mask><circle class="sun" cx="12" cy="12" r="6" mask="url(#moon-mask)" fill="currentColor"></circle><g class="sun-beams" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></g></svg>
    </button>
';

$htmlResult = main();

// redirect
if (isset($_GET["redirect"])) {
    $urlNoApi = substr($url, 0, -4);
    header("Location: $urlNoApi/login?ReturnUrl=" . $_GET["redirect"]);
    exit;
}
// end redirect

// getclass
if (isset($_GET["getclass"])) {
    header("Content-Type: text/plain");
    exit;
}
// end getclass

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
    $classId = '';

    if ($userResponse[0]) {
        $decodedU = json_decode($userResponse[1]);

        if (isset($decodedU->FullName)) {
            $name = $decodedU->FullName;
        }

        if (isset($decodedU->Class->Id)) {
            $classId = $decodedU->Class->Id;
        }
    }

    // getclass
    if (isset($_GET['getclass']) && $name != '???') {
        header("Content-Type: text/plain");

        switch ($_GET['getclass']) {
            case 2:
                echo $classId;
                break;

            default:
                echo substr($name, -3);
                break;
        }

        exit;
    }
    // end getclass

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
    global $themeToggle;

    $result = '';
    $jsData = '';
    $result .= '<header><nav><div class="top-row"><h1><a href="..">iGOH</a><a href="#" onclick="fill(event, \'\', \'\');">Průměr známek</a></h1>';
    $result .= $themeToggle;
    $result .= '<div><a href="?odhlasit">' . $name . ' (odhlásit se)</a><a href="?redirect=next/prubzna.aspx">→ Bakaláři</a>';
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
    global $themeToggle;

    $header = '<header><nav class="uzky"><h1><a href="..">iGOH</a><a href=".">Průměr známek</a></h1>' . $themeToggle . '</nav></header>';
    $form = '
		<form method="post">
			<label for="username">Uživatelské jméno:</label>
			<input type="text" id="username" name="username" autofocus>
			<label for="password">Heslo:</label>
			<input type="password" id="password" name="password">
            ' . (!empty($_GET['debug']) ? '<input type="hidden" name="debug" value="1">' : '') . '
			<button type="submit">Přihlásit se</button>
		</form>
		<small>Tento nástroj jsem vytvořil já, <a href="https://www.vitkolos.cz" class="link">Vít Kološ</a>. Studentům Gymnázia Olgy Havlové je k&nbsp;dispozici zcela zdarma. Pokud mi chceš pomoct s&nbsp;vývojem, <a href="mailto:vit.kolos@gmail.com" class="link">ozvi se mi</a>.</small>
		<small>Nejsi studentem GOH? Přejdi do <a href="anonymni.html" class="link">anonymní verze aplikace</a>.</small>
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
    <link rel="manifest" href="/assets/icons/site.webmanifest">
    <meta name="theme-color" content="#795548">
    <meta property="og:image" content="https://www.igoh.tk/assets/og.png">

    <link rel="stylesheet" href="style.css?v=1">

    <script src="../assets/theme.js"></script>

    <!-- Matomo -->
    <script>
        var _paq = window._paq = window._paq || [];
        _paq.push(['disableCookies']);
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u = "//www.vitkolos.cz/matomo/";
            _paq.push(['setTrackerUrl', u + 'matomo.php']);
            _paq.push(['setSiteId', '2']);
            var d = document,
                g = d.createElement('script'),
                s = d.getElementsByTagName('script')[0];
            g.async = true;
            g.src = u + 'matomo.js';
            s.parentNode.insertBefore(g, s);
        })();
    </script>
    <!-- End Matomo Code -->
</head>

<body>
    <?php
    echo $htmlResult;
    ?>

    <script src="script.js?v=1"></script>
</body>

</html>
