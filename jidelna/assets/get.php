<?php
require_once("loginfunctions.php");
require_once("parser.php");

if(!empty($_COOKIE["susenky"])) {
	$q = explode(";", $_COOKIE["susenky"]);
	$r = jidelnicekReq($q);
	$q2 = array();
	foreach($q as $qval) {
		$qpair = explode("=", $qval);
		$q2[$qpair[0]] = $qval;
	}
	$r2 = array();
	foreach($r[1] as $rval) {
		$rpair = explode("=", $rval);
		$r2[$rpair[0]] = $rval;
	}
	setcookie("susenky", implode(";", array_merge($q2, $r2)), time() + (86400 * 30), "/");
	if($r[0] != "") {
		// echo '<div class="day">
		// <a href="/" style="color:#444">zpět na úvodní stránku iGOH</a>
		// </div>';

		if(!empty($_COOKIE["username"])) {
			echo "<div class=\"day\" id=\"upper\">
			Přihlášený uživatel: <b>{$_COOKIE["username"]}</b> (<a href=\"login.php?logout\" style=\"color:inherit\">odhlásit se</a>)<br>
            <small style=\"color:grey\">burzu obědů najdeš pouze v <a href=\"https://jidelna.gyohavl.cz/\" style=\"color:inherit\">iCanteen</a> (pokud na mobilu nevidíš tlačítko [do burzy], zkus otočit obrazovku na šířku)</small>
			</div>";
		}

		userView(parse($r[0]));
		//print_r(parse($r[0]));
	} else {
		loginForm();
	}
} else {
	loginForm();
}

function loginForm($text = "") {
	if(!empty($_COOKIE["autolog"])) {
		header("Location: ../login.php?autolog=1");
	} else {
	?>
		<p>
			<b class="nadpis">Přihlášení</b><br>
			<?php if($text != "") {echo $text."<br>";} ?>
			Použij údaje z iCanteen (např. novak1234).<br>
			Pokud máš heslo stejné jako přihlašovací jméno,<br> nemusíš ho vyplňovat.
		</p>
		<form action="login.php" method="POST">
			<table>
				<tbody>
					<tr><td colspan="2"><label for="username">Přihlašovací jméno:</label></td></tr>
					<tr><td colspan="2"><input type="text" id="username" name="username" value="<?php if(!empty($_COOKIE["username"])) {echo $_COOKIE["username"];} ?>"></td></tr>
					<tr><td colspan="2"><label for="password">Heslo (pokud se liší):</label></td></tr>
					<tr><td colspan="2"><input type="password" id="password" name="password"></td></tr>
					<tr><td><label for="remembername">Uložit přihlašovací jméno:</label></td><td><input type="checkbox" id="remembername" name="remembername" checked></td></tr>
					<tr><td><label for="autolog">Automaticky přihlašovat pomocí jména:<br>(doporučuji, pokud se heslo neliší od jména)</label></td><td><input type="checkbox" id="autolog" name="autolog"></td></tr>
					<tr><td><label for="rememberme">Zůstat trvale přihlášen:</label></td><td><input type="checkbox" id="rememberme" name="rememberme"></td></tr>
					<tr><td colspan="2"><input type="submit" value="Přihlásit se"></td></tr>
				</tbody>
			</table>
		</form>
	<?php
	}
}

function userView($data) {
	foreach ($data as $dayKey => $day) {
		$weekdays = array("Dny v týdnu", "Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek", "Sobota", "Neděle");
		$den = date("N", strtotime($day["date"]));
		$datum = $weekdays[$den]." ".date("j. n.", strtotime($day["date"]));
		$polevkaP = trim(preg_replace("/(.*?),(.*)/", "$1", $day["meals"][0]["name"]));
		$jidloP = trim(preg_replace("/(.*?),(.*)/", "$2", $day["meals"][0]["name"]));
		$polevka = preg_replace('/\n(.*)/', '', $polevkaP);
		$polevka = preg_replace('/\s\s/', ' ', $polevkaP);
		$polevka = preg_replace('/\( /', '(', $polevka);
		$polevka = preg_replace('/\s"(\S*?)"/', ' „$1“', $polevka);
		$polevka = preg_replace('/ - /', ' &ndash; ', $polevka);
		if($day["meals"][0]["name"] == "") {
			$polevka = '<span class="undefined">neznámá polévka</span>';
			$jidloP = $polevka;
		}
		// $polevkaChecked = in_array(true, array_column($day["meals"], 'ordered')) ? 'checked' : '';
		$polevkaChecked = 'checked';
		?>
		<div class="day<?php
				if($jidloP == $polevkaP) {
					echo " disabled";
				} elseif(strpos($jidloP, "pro zaměstnance")) {
					echo " disabled";
				}
				?>">
			<span class="date"><?= $datum ?></span>
			<div class="meal meal-p">
				<div class="meal-checkbox"><input type="checkbox" id="checkbox<?= $dayKey ?>p" class="transparent" <?=$polevkaChecked?> disabled></div><label for="checkbox<?= $dayKey ?>p"><?= $polevka ?></label>
			</div>
			<?php
			foreach ($day["meals"] as $mealKey => $meal) {
				$jidlo = trim(preg_replace("/(.*?),(.*)/", "$2", $meal["name"]));
				$jidlo = preg_replace('/\s\s+/', ' ', $jidlo);
				$jidlo = preg_replace('/ ,/', ',', $jidlo);
				$jidlo = preg_replace('/,(\S)/', ', $1', $jidlo);
				$jidlo = preg_replace('/\( /', '(', $jidlo);
				$jidlo = preg_replace('/\s"(\S*?)"/', ' „$1“', $jidlo);
				$jidlo = preg_replace('/ - /', ' &ndash; ', $jidlo);
				$jidlo = preg_replace('/, \(/', ' (', $jidlo);
				$jidlo = preg_replace('/,$/', '', $jidlo);
				if($meal["name"] == "") {
					$jidlo = '<span class="undefined">neznámé jídlo</span>';
				}

				$alergeny = $meal["allergens"];
				$alergeny = mb_strtolower($alergeny, 'UTF-8');
				$alergeny = preg_replace('/(^<span class="textgrey">\()|(\)<\/span>$)/', '', $alergeny);
				if(preg_replace('/<.+?>/', '', $alergeny) != "") {
					$alergeny = '<br><div class="alergeny">'.$alergeny.'</div>';
				}

				$atributy = "";
				if($meal["ordered"]) {
					$atributy .= " checked";
				}
				if($meal["disabled"]) {
					$atributy .= " disabled";
				}
				if($jidloP != $polevkaP) {
				?>
				<div class="meal meal-<?= $mealKey ?><?php if(strpos($jidlo, "nevaří") !== false) {echo " nevari";} ?>">
					<div class="meal-checkbox"><input class="mealcb" type="checkbox" id="checkbox<?= $dayKey.$mealKey ?>" onclick="checkboxClick(this, '<?= $meal["url"] ?>');"<?= $atributy ?>></div><label for="checkbox<?= $dayKey.$mealKey ?>"><?= $jidlo ?><?= $alergeny ?></label>
				</div>
				<?php
				}
			}
			?>
		</div>
		<?php
	}
}

function userView2($data) {
	foreach($data as $day) {
		foreach($day["meals"] as $meal) {
			echo $meal["url"]."<br>";
		}
	}
}
