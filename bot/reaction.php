<?php
include('src/main.php');
// todo: refactor, put verify token in secrets

$endSubmit = true;
$token = $secrets['fb'];

$challenge = $_REQUEST['hub_challenge'];
$verify_token = $_REQUEST['hub_verify_token'];

if ($verify_token === 'supltoken') {
    echo $challenge;
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$sender = $input['entry'][0]['messaging'][0]['sender']['id'];
$message = $input['entry'][0]['messaging'][0]['message']['text'];
$payload = $input['entry'][0]['messaging'][0]['postback']['payload'];
$url = 'https://graph.facebook.com/v6.0/me/messages?access_token=' . $token;
$ch = curl_init($url);
$pv = curl_init($url);

if (!empty($payload)) {
    if ($payload == "ZACIT") {
        $jsonData = '{
			"recipient":{
			"id":"' . $sender . '"
			},
			"message":{
			"text":"Zadej prosím název třídy, pro kterou budeš chtít dostávat upozornění na změny v suplování (např. 4.B nebo 6.A). '
            . preg_replace('/<br>/', '\n', "<br><br>") . 'Bot také umí posílat jídelníček. Pro více informací rozklikni nápovědu v menu ☰ nebo napiš otazník. '
            . preg_replace('/<br>/', '\n', "<br><br>") . 'Pokud se během používání bota vyskytnou problémy, napiš mi na m.me/vit.kolos nebo na vit.kolos@gmail.com."
			}
        }';
    } elseif ($payload == "ZADAT") {
        $jsonData = '{
			"recipient":{
			"id":"' . $sender . '"
			},
			"message":{
			"text":"Zadej prosím název třídy, pro kterou budeš chtít dostávat upozornění na změny v suplování (např. 4.B nebo 6.A)."
			}
        }';
    } elseif ($payload == "NAPOVEDA") {
        $message = "?";
    } elseif ($payload == "ZRUSIT") {
        $message = "x";
    } elseif ($payload == "OBEDY") {
        $message = "obědy";
    } elseif ($payload == "OBEDYAL") {
        $message = "obědy-a";
    } elseif ($payload == "ZRUSITOBEDY") {
        $message = "obědy-x";
    }
}

if ($message != "") {
    if (preg_match("/\b\d\. ?\w\b/i", $message, $matches)) {
        $trida = str_replace(' ', '', strtoupper($matches[0]));

        if (in_array($trida, $availableClasses)) {
            zapis("DELETE FROM bot_suplovani WHERE messenger_id = " . $sender . ";");

            $user = curl_init("https://graph.facebook.com/v6.0/" . $sender . "?fields=first_name,last_name,profile_pic&access_token=" . $token);
            curl_setopt($user, CURLOPT_RETURNTRANSFER, 1);
            $uzivatel = curl_exec($user);
            $uzivatel = json_decode($uzivatel, true);

            zapis("INSERT INTO bot_suplovani (messenger_id, first_name, last_name, picture, class) VALUES (" . $sender . ", '" . $uzivatel["first_name"] . "', '" . $uzivatel["last_name"] . "', '" . $uzivatel["profile_pic"] . "', '" . $trida . "')");

            $file = getSuplovani();
            $zprava = convertNoChanges(plain($trida, $file, true));
            $zprava = "<br><br>Aktuální suplování:<br>" . $zprava;
            $zprava = preg_replace('/<br>/', '\n', $zprava);

            $jsonData = '{
				"recipient":{
				"id":"' . $sender . '"
				},
				"message":{
				"text":"Budeš dostávat upozornění pro třídu ' . $trida . ' a to vždy, když se suplování změní na webu školy. Pro zrušení notifikací napiš x.' . $zprava . '"
				}
            }';
        } else {
            $jsonData = '{
				"recipient":{
				"id":"' . $sender . '"
				},
				"message":{
				"text":"Zadej prosím platný název třídy ve tvaru X.Y (podporovány jsou třídy 1.–8.A, 1.–8.B, 1.–4.C)."
				}
            }';
        }
    } elseif (preg_match("/\bobědy\b/i", $message, $matches) || preg_match("/\bobedy\b/i", $message, $matches)) {
        if (preg_match("/\bobědy[-–]x\b/i", $message) || preg_match("/\bobedy[-–]x\b/i", $message)) {
            zapis("DELETE FROM bot_canteen WHERE messenger_id = " . $sender . ";");
            $jsonData = '{
				"recipient":{
				"id":"' . $sender . '"
				},
				"message":{
				"text":"Pondělní zasílání jídelníčku bylo zrušeno."
				}
            }';
        } else {
            if (preg_match("/\bobědy[-–]a\b/i", $message, $matches) || preg_match("/\bobedy[-–]a\b/i", $message, $matches)) {
                $alergenyVar = true;
            } else {
                $alergenyVar = false;
            }

            zapis("DELETE FROM bot_canteen WHERE messenger_id = " . $sender . ";");

            $user = curl_init("https://graph.facebook.com/v6.0/" . $sender . "?fields=first_name,last_name,profile_pic&access_token=" . $token);
            curl_setopt($user, CURLOPT_RETURNTRANSFER, 1);
            $uzivatel = curl_exec($user);
            $uzivatel = json_decode($uzivatel, true);

            if ($alergenyVar) {
                $alergenyDB = 1;
            } else {
                $alergenyDB = 0;
            }

            zapis("INSERT INTO bot_canteen (messenger_id, first_name, last_name, picture, allergens) VALUES (" . $sender . ", '" . $uzivatel["first_name"] . "', '" . $uzivatel["last_name"] . "', '" . $uzivatel["profile_pic"] . "', " . $alergenyDB . ")");

            $file = file_get_contents('https://jidelna.gyohavl.cz/faces/login.jsp');
            $zpravy = obedy($alergenyVar, $file);
            $endSubmit = false;

            foreach ($zpravy as $key => $zprava) {
                if ($key == 0) {
                    $alergenyText = "";
                    if ($alergenyVar) {
                        $alergenyText = " (se seznamem alergenů)";
                    }
                    $zprava = "Každé pondělí v 7:45 budeš dostávat aktuální jídelníček" . $alergenyText . ". Pro zrušení notifikací napiš obědy-x.<br><br>" . $zprava;
                }

                $zprava = preg_replace('/<br>/', '\n', $zprava);
                $zprava = preg_replace('/"/', '\"', $zprava);
                $jsonData = '{
					"recipient":{
					"id":"' . $sender . '"
					},
					"message":{
					"text":"' . $zprava . '"
					}
                }';
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($ch);
                //error_log($result);
            }
        }
    } elseif ($message == "x" || $message == "X" || $message == "×") {
        zapis("DELETE FROM bot_suplovani WHERE messenger_id = " . $sender . ";");
        $jsonData = '{
			"recipient":{
			"id":"' . $sender . '"
			},
			"message":{
			"text":"Upozornění byla zrušena."
			}
        }';
    } elseif (preg_match("/\b(help)|(otazník)\b/i", $message) || $message == "?") {
        $aktualniStav = array(
            empty(vypis("SELECT * FROM bot_suplovani WHERE messenger_id = $sender;")),
            empty(vypis("SELECT * FROM bot_canteen WHERE messenger_id = $sender;"))
        );
        $jsonData = '{
                "recipient":{
                    "id":"' . $sender . '"
                },
                "message":{
                    "attachment":{
                        "type":"template",
                        "payload":{
                            "template_type":"button",
                            "text":"Zadej název třídy, pro kterou budeš chtít dostávat upozornění na změny v suplování (např. 4.B nebo 6.A). Pokud jsi tak již učinil(a) dříve, můžeš zasílání upozornění zrušit pomocí „x“. \n\nJestli bys ocenil(a), kdyby ti bot každé pondělí zasílal jídelníček, napiš „obědy“ nebo „obědy-a“ (pro jídelníček s alergeny). Pro zrušení zasílání jídelníčku napiš „obědy-x“.",
                            "buttons":[' .
            ($aktualniStav[0] ? '{
                                    "title": "Zasílat suplování",
                                    "type": "postback",
                                    "payload": "ZADAT"
                                },' : '{
                                    "title": "Zrušit suplování",
                                    "type": "postback",
                                    "payload": "ZRUSIT"
                                },') .
            ($aktualniStav[1] ? '{
                                    "title": "Zasílat jídelníček",
                                    "type": "postback",
                                    "payload": "OBEDY"
                                },
                                {
                                    "title": "Jídelníček s alergeny",
                                    "type": "postback",
                                    "payload": "OBEDYAL"
                                }'
                : '{
                                    "title": "Zrušit jídelníček",
                                    "type": "postback",
                                    "payload": "ZRUSITOBEDY"
                                }') .
            ']
                        }
                    }
                }
			}';
    } elseif (preg_match("/\bid\b/i", $message)) {
        $jsonData = '{
			"recipient":{
			"id":"' . $sender . '"
			},
			"message":{
			"text":"' . $sender . '"
			}
        }';
    } else {
        $jsonData = '{
			"recipient":{
			"id":"' . $sender . '"
			},
			"message":{
			"text":"Bohužel ti nerozumím, jsem přece jenom bot. Občas se tady však objeví Vítek, tak budeš mít třeba štěstí. Když napíšeš otazník, zobrazí se základní nápověda."
			}
        }';

        $user = curl_init("https://graph.facebook.com/v6.0/" . $sender . "?fields=first_name,last_name,profile_pic&access_token=" . $token);
        curl_setopt($user, CURLOPT_RETURNTRANSFER, 1);
        $uzivatel = curl_exec($user);
        $uzivatel = json_decode($uzivatel, true);
        $message2 = preg_replace('/"/', '\"', $message);

        $proVitka = '{
			"recipient":{
			"id":"' . $secrets['admin_messenger_id'] . '"
			},
			"message":{
			"text":"<' . $uzivatel["first_name"] . ' ' . $uzivatel["last_name"] . '>' . preg_replace('/<br>/', '\n', "<br>") . '' . $message2 . '"
			},
            "messaging_type": "MESSAGE_TAG",
            "tag": "ACCOUNT_UPDATE"
        }';
        curl_setopt($pv, CURLOPT_POST, 1);
        curl_setopt($pv, CURLOPT_POSTFIELDS, $proVitka);
        curl_setopt($pv, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($pv, CURLOPT_RETURNTRANSFER, 1);
        $resultpv = curl_exec($pv);
    }
}

if ($endSubmit) {
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
}
