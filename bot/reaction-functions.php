<?php
function getMessage($sender, $payload, $message, $availableClasses, $token, $url, $adminId) {
    global $fbGraphApiPath;

    $senderId = $sender['id'];

    if (!empty($payload)) {
        if ($payload == "ZACIT") {
            return 'Zadej prosím název třídy, pro kterou budeš chtít dostávat upozornění na změny v suplování (např. 4.B nebo 6.A). '
                . '\n\nBot také umí posílat jídelníček. Pro více informací rozklikni nápovědu v menu ☰ nebo napiš otazník. '
                . '\n\nPokud se během používání bota vyskytnou problémy, napiš mi na m.me/vit.kolos nebo na vit.kolos@gmail.com."';
        } elseif ($payload == "ZADAT") {
            return 'Zadej prosím název třídy, pro kterou budeš chtít dostávat upozornění na změny v suplování (např. 4.B nebo 6.A).';
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
            // subscribe suplovani
            $class = str_replace(' ', '', strtoupper($matches[0]));

            if (in_array($class, $availableClasses)) {
                sql("DELETE FROM bot_suplovani WHERE messenger_id = " . $senderId . ";", false);

                $userResponse = customCurl($fbGraphApiPath . $senderId . "?fields=first_name,last_name,profile_pic&access_token=" . $token);
                $user = json_decode($userResponse, true);
                sql("INSERT INTO bot_suplovani (messenger_id, first_name, last_name, picture, class) VALUES (" . $senderId . ", '" . $user["first_name"] . "', '" . $user["last_name"] . "', '" . $user["profile_pic"] . "', '" . $class . "')", false);

                $file = getSuplovani();
                $messageToSend = convertNoChanges(plain($class, $file, true));
                $messageToSend = '\n\nAktuální suplování:\n' . $messageToSend;
                $messageToSend = str_replace('<br>', '\n', $messageToSend);

                return 'Budeš dostávat upozornění pro třídu ' . $class . ' a to vždy, když se suplování změní na webu školy. Pro zrušení notifikací napiš x.' . $messageToSend;
            } else {
                return 'Zadej prosím platný název třídy ve tvaru X.Y (např. 4.B nebo 1.C, podporovány jsou třídy 1.–8.A, 1.–8.B, 1.–4.C).';
            }
        } elseif (preg_match("/\bobědy\b/i", $message, $matches) || preg_match("/\bobedy\b/i", $message, $matches)) {
            // subscribe/cancel canteen
            if (preg_match("/\bobědy[-–]x\b/i", $message) || preg_match("/\bobedy[-–]x\b/i", $message)) {
                sql("DELETE FROM bot_canteen WHERE messenger_id = " . $senderId . ";", false);
                return 'Pondělní zasílání jídelníčku bylo zrušeno.';
            } else {
                $allergens = (preg_match("/\bobědy[-–]a\b/i", $message, $matches) || preg_match("/\bobedy[-–]a\b/i", $message, $matches));
                sql("DELETE FROM bot_canteen WHERE messenger_id = " . $senderId . ";", false);

                $userResponse = customCurl($fbGraphApiPath . $senderId . "?fields=first_name,last_name,profile_pic&access_token=" . $token);
                $user = json_decode($userResponse, true);
                sql("INSERT INTO bot_canteen (messenger_id, first_name, last_name, picture, allergens) VALUES (" . $senderId . ", '" . $user["first_name"] . "', '" . $user["last_name"] . "', '" . $user["profile_pic"] . "', " . intval($allergens) . ")", false);

                $file = file_get_contents('https://jidelna.gyohavl.cz/faces/login.jsp');
                $messagesToSend = obedy($allergens, $file);
                $jsonData = array();

                if (count($messagesToSend) == 0) {
                    $messagesToSend = array('Jídelníček momentálně není dostupný.');
                }

                foreach ($messagesToSend as $key => $messageToSend) {
                    if ($key == 0) {
                        $allergensText = $allergens ? ' (se seznamem alergenů)' : '';
                        $messageToSend = 'Každé pondělí v 7:45 budeš dostávat aktuální jídelníček' . $allergensText . '. Pro zrušení notifikací napiš obědy-x.\n\n' . $messageToSend;
                    }

                    $messageToSend = str_replace('<br>', '\n', $messageToSend);
                    $messageToSend = str_replace('"', '\"', $messageToSend);
                    $jsonData[] = '{
                        "recipient":{
                        "id":"' . $senderId . '"
                        },
                        "message":{
                        "text":"' . $messageToSend . '"
                        }
                    }';
                }

                return $jsonData;
            }
        } elseif ($message == "x" || $message == "X" || $message == "×") {
            // cancel suplovani
            sql("DELETE FROM bot_suplovani WHERE messenger_id = " . $senderId . ";", false);
            return 'Pravidelné zasílání suplování bylo zrušeno.';
        } elseif (preg_match("/\b(help)|(otazník)\b/i", $message) || $message == "?" || $message == "Nápověda") {
            // help
            $currentState = array(
                empty(sql("SELECT * FROM bot_suplovani WHERE messenger_id = $senderId;")),
                empty(sql("SELECT * FROM bot_canteen WHERE messenger_id = $senderId;"))
            );
            $text = 'Zadej název třídy, pro kterou budeš chtít dostávat upozornění na změny v suplování (např. 4.B nebo 6.A). Pokud jsi tak již učinil(a) dříve, můžeš zasílání upozornění zrušit pomocí „x“. \n\nJestli bys ocenil(a), kdyby ti bot každé pondělí zasílal jídelníček, napiš „obědy“ nebo „obědy-a“ (pro jídelníček s alergeny). Pro zrušení zasílání jídelníčku napiš „obědy-x“.';
            return $text;
            // disabled buttons as payload isn't working
            $jsonData = '{
                "recipient":{
                    "id":"' . $senderId . '"
                },
                "message":{
                    "attachment":{
                        "type":"template",
                        "payload":{
                            "template_type":"button",
                            "text":"'.$text.'",
                            "buttons":[' .
                ($currentState[0] ? '{
                                    "title": "Zasílat suplování",
                                    "type": "postback",
                                    "payload": "ZADAT"
                                },' : '{
                                    "title": "Zrušit suplování",
                                    "type": "postback",
                                    "payload": "ZRUSIT"
                                },') .
                ($currentState[1] ? '{
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
            return array($jsonData);
        } elseif (preg_match("/\bid\b/i", $message)) {
            return $senderId;
        } else {
            // default
            $userResponse = customCurl($fbGraphApiPath . $senderId . "?fields=first_name,last_name,profile_pic&access_token=" . $token);
            $user = json_decode($userResponse, true);
            $message2 = str_replace('"', '\"', $message);

            $name = $sender['name'] ?? ($user["first_name"] . ' ' . $user["last_name"]);

            $jsonData = '{
                "recipient":{
                "id":"' . $adminId . '"
                },
                "message":{
                "text":"<' . $name . '>\n' . $message2 . '"
                },
                "messaging_type": "MESSAGE_TAG",
                "tag": "ACCOUNT_UPDATE"
            }';
            customCurl($url, $jsonData);

            return 'Bohužel ti nerozumím, jsem přece jenom bot. Občas se tady však objeví Vítek, tak budeš mít třeba štěstí. Když napíšeš otazník, zobrazí se základní nápověda.';
        }
    }
}

function sendMessage($message, $sender, $url) {
    if (is_array($message)) {
        foreach ($message as $jsonData) {
            customCurl($url, $jsonData);
        }
    } else {
        $jsonData = '{
            "recipient":{
                "id":"' . $sender['id'] . '"
            },
            "message":{
                "text":"' . $message . '"
            }
        }';
        customCurl($url, $jsonData);
    }
}
