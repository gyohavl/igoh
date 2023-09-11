<?php
function plain($trida, $file, $nochanges = false) {
    if (!empty($trida)) {
        $content = $file;
        $date = $file;
        $vysledek = "";
        if ($file !== false && !str_contains($file, '[ERROR]')) {
            if (strpos($date, '<h1>') !== false) {
                $date = explode('<h1>', $date, 2);
                $date = explode('</h1>', $date[1], 2);
                $date = $date[0];
                // $date = str_replace(".", ". ", $date);
                $date = str_replace("Změny v rozvrhu ", "", $date);
                // $date = preg_replace("/(\d+\. \d+\.) \d*/", "$1", $date);
                $date = my_mb_ucfirst(trim($date));
                $vysledek = $vysledek . $date . ' – ';
            } else {
                $date = "Datum není dostupné.";
            }

            if (strpos($content, '<td style="width:22%">' . $trida) !== false) {
                $content = explode('<td style="width:22%">' . $trida, $content, 2);
                $content = explode('</table>', $content[1], 2);
                // $content = explode('<tr class="tr_dozortrid_3">', $content[0], 2);
                // $content = explode('<p class="textbaka_3" style="text-align: right">', $content[0], 2);
                $content = $trida . '<br>' . $content[0];
                $content = preg_replace("/>></", "&gt;&gt;<", $content);
                $content = preg_replace("/<<</", "&lt;&lt;<", $content);
                $content = strip_tags($content, '<tr><br>');
                // $content = preg_replace('/<\/p>[\s(?:&nbsp;)]*<p>/', ' ', $content);
                // $content = preg_replace('/<\/p>[\s(?:&nbsp;)]*<\/tr>[\s(?:&nbsp;)]*<tr>[\s(?:&nbsp;)]*<p>/', "<br>", $content);
                $content = preg_replace('/<\/tr>/', "<br>", $content);
                // $content = preg_replace('/<p>/', '', $content);
                // $content = preg_replace('/<\/p>[\s(?:&nbsp;)]*<\/tr>[\s(?:&nbsp;)]*/', '', $content);
                // $content = preg_replace('/<\/p>[\s(?:&nbsp;)]*/', '', $content);
                // $content = preg_replace('/\bhod\b/', '', $content);
                // $content = preg_replace('/' . $trida . '\s/', $trida . "<br>", $content);
                $content = preg_replace("/[\s\n\t]+/", " ", $content);
                // $content = preg_replace("/(\d\.)\- (\d\.)/", "$1–$2", $content);
                // $content = preg_replace("/– \(/", "(", $content);
                $content = strip_tags($content, '<br>');
                $content = preg_replace("/&gt;/", ">", $content);
                $content = preg_replace("/&lt;/", "<", $content);
                $content = preg_replace("/(\d?\d\.)(\d?\d\.)/", "$1 $2,", $content);
                $content = preg_replace("/\(N\)/", "(​N)", $content);
                $content = preg_replace("/\s*<br>\s*/", "<br>", $content);
                $content = preg_replace("/<br>$/", "", $content);
                $content = preg_replace("/(<br>\d\w?)\s/", "$1. ", $content);
                $vysledek = $vysledek . $content;
                return $vysledek;
            } else {
                $vysledek = array(
                    "Žádné změny. $date – $trida",
                    "S lítostí v srdci oznamuji, že se v rozvrhu $trida nekonají žádné změny.<br>$date",
                    "V rozvrhu $trida se nic nemění.<br>$date",
                    "A zase nic. $date – $trida",
                    "Dnes tady žádné změny nemám. 😔 $date – $trida"
                );
                // Původní hláška: "Žádné změny. $date – $trida"
                $dnuDoPrazdnin = skolnichDnuOdDo(date("Ymd") + 1);
                $hlaskaDnuDoPrazdnin = "<br><br>Nesmutni! Do příštích prázdnin zbývá už jenom " . $dnuDoPrazdnin . " školních dnů.";

                if ($nochanges) {
                    return "nochanges;$date;$trida";
                } else if ($trida == 'doprazdnin') {
                    $phpDate = DateTime::createFromFormat('* j. n.', $date);
                    $dnuDoPrazdnin = $phpDate ? skolnichDnuOdDo($phpDate->format('Ymd')) : 0;
                    return $dnuDoPrazdnin;
                }

                return $vysledek[array_rand($vysledek)] . $hlaskaDnuDoPrazdnin;
            }
        } else {
            return "Chyba dat. Napište na e-mail vit.kolos@gmail.com (uveďte kód chyby: SgohBd).";
        }
    } else {
        return "Chyba třídy. Napište na e-mail vit.kolos@gmail.com (uveďte kód chyby: SgohBt).";
    }
}

function convertNoChanges($string) {
    if (substr($string, 0, 10) === 'nochanges;') {
        $data = explode(';', $string);
        $date = $data[1];
        $trida = $data[2];
        $phpDate = DateTime::createFromFormat('* j. n.', $date);
        $dnuDoPrazdnin = $phpDate ? skolnichDnuOdDo($phpDate->format('Ymd')) : 0;

        $vysledek = array(
            "Žádné změny. $date – $trida",
            "S lítostí v srdci oznamuji, že se v rozvrhu $trida nekonají žádné změny.<br>$date",
            "V rozvrhu $trida se nic nemění.<br>$date",
            "A zase nic. $date – $trida",
            "Dnes tady žádné změny nemám. 😔 $date – $trida"
        );

        switch (true) {
            case ($dnuDoPrazdnin == 1):
                $hlaskaDnuDoPrazdnin = "<br><br>Nesmutni! Do příštích prázdnin zbývá už jenom jeden den školy!";
                break;
            case ($dnuDoPrazdnin >= 2 && $dnuDoPrazdnin <= 4):
                $hlaskaDnuDoPrazdnin = "<br><br>Nesmutni! Do příštích prázdnin zbývají už jenom " . $dnuDoPrazdnin . " školní dny.";
                break;
            default:
                $hlaskaDnuDoPrazdnin = "<br><br>Nesmutni! Do příštích prázdnin zbývá už jenom " . $dnuDoPrazdnin . " školních dnů.";
        }

        return $vysledek[array_rand($vysledek)] . $hlaskaDnuDoPrazdnin;
    }

    return $string;
}

function obedy($alergeny, $file) {
    if ($file !== false) {
        $result = array();
        $doc = new DomDocument();
        $internalErrors = libxml_use_internal_errors(true);
        $doc->loadHTML($file);
        libxml_use_internal_errors($internalErrors);
        $finder = new DomXPath($doc);
        $rows = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' jidelnicekWeb ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' jidelnicekDen ')]");
        foreach ($rows as $key => $row) {
            $result[$key] = "";
            $date = substr($row->getElementsByTagName("div")->item(0)->getAttribute("id"), 4);
            $weekdays = array("Dny v týdnu", "Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek", "Sobota", "Neděle");
            $den = date("N", strtotime($date));
            $datum = $weekdays[$den] . " " . date("j. n.", strtotime($date));
            $meals = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' jidelnicekWeb ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' jidelnicekDen ')][" . ($key + 1) . "]/article/div");
            $jidlo = $meals[0]->childNodes->item(3)->textContent;
            if (strpos($jidlo, ",") !== false && strpos($jidlo, "pro zaměstnance") === false) {
                $result[$key] .= $datum . "<br>";
                foreach ($meals as $mealNum => $meal) {
                    $jidlo = $meal->childNodes->item(3)->childNodes->item(0)->textContent;
                    if (!in_array(trim($meal->childNodes->item(1)->textContent), ["Oběd cizí", "Oběd BL", "Oběd BM"])) {
                        $jidlo = preg_replace('/\s\s+/', " ", $jidlo);
                        if ($mealNum == 0) {
                            $pokrm = trim(preg_replace("/^(Polévka )?(.*?),(.*)/", "$2", trim($jidlo)));
                            $pokrm = preg_replace('/\s"(\S*?)"/', ' „$1“', $pokrm);
                            $result[$key] .= "P) " . mb_strtolower($pokrm) . "<br>";
                        }
                        $pokrm = trim(preg_replace("/^(Polévka )?(.*?),(.*)/", "$3", trim($jidlo)));
                        $pokrm = preg_replace('/,$/', '', $pokrm);
                        $pokrm = preg_replace('/ , /', ', ', $pokrm);
                        $pokrm = preg_replace('/\s"(\S*?)"/', ' „$1“', $pokrm);
                        $a = "";
                        if ($alergeny && $meal->childNodes->item(3)->childNodes->item(1) !== null) {
                            $a = " " . $meal->childNodes->item(3)->childNodes->item(1)->textContent;
                        }
                        $result[$key] .= ($mealNum + 1) . ") " . $pokrm . $a . "<br>";
                        // $result[$key] .= ($mealNum + 1) . ") " . $jidlo . "<br>";
                    }
                }
            }
        }
        $result = array_values(array_filter($result));
        return $result;
    } else {
        return array("[Obědy] Nelze načíst data serveru.");
    }
}

function skolnichDnuOdDo($vych_datum, $kon_datum = 99999999) {
    // o letních prázdninách bude pravděpodobně potřeba deaktivovat cron job
    $prazdniny = array(
        20230928, 20231026, 20231027, 20231117, 20231223, 20231224, 20231225, 20231226, 20231227,
        20231228, 20231229, 20231230, 20231231, 20240101, 20240102, 20240202, 20240311, 
        20240312, 20240313, 20240314, 20240315, 20240316, 20240317, 20240329, 20240401, 20240501, 20240508, 20240629
    );
    $pocitadlo = 0;
    $datum = $vych_datum;
    while ($datum <= $kon_datum && $pocitadlo < 1000) {
        $datum = DateTime::createFromFormat("Ymd", $datum)->format("Ymd");
        $den_v_tydnu = DateTime::createFromFormat("Ymd", $datum)->format("N");
        if ($den_v_tydnu < 6 && !in_array($datum, $prazdniny)) {
            $pocitadlo++;
        } elseif ($kon_datum == 99999999 && in_array($datum, $prazdniny)) {
            break;
        }
        $datum++;
    }
    return $pocitadlo == 1000 ? "?" : $pocitadlo;
}

function my_mb_ucfirst($str) {
    $fc = mb_strtoupper(mb_substr($str, 0, 1));
    return $fc . mb_substr($str, 1);
}
