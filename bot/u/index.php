<?php
header('Content-Type: text/plain');
$response = file_get_contents('https://suply.herokuapp.com/stav/simple.php');
$data = array();

foreach (explode("\n", $response) as $row) {
    $r = explode(" ", $row);
    if (isset($r[1]))
        $data[$r[0]] = $r[1];
}

echo $data['u'] . ' lidí odebírá suplování' . PHP_EOL . 'to je asi ' . str_replace('.', ',', $data['p']) . ' % studentů školy' . PHP_EOL;
echo PHP_EOL . 'zde je rozdělení podle tříd:' . PHP_EOL;

foreach (array_slice($data, 0, 20) as $key => $value) {
    echo $key . "\t" . $value . PHP_EOL;
}

echo PHP_EOL . $data['o'] . ' studentů odebírá jídelníček';
