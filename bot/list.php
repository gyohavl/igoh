<?php
include('src/admin.php');

if (isset($_POST['admin']) && $_POST['admin'] === $secrets['admin']) {
    $filter = isset($_POST['filter']) ? $_POST['filter'] : "all";
    $classes = isset($_POST['classes']) ? $_POST['classes'] : "";
    echo '<!doctype html><html lang="cs"><head><meta charset="utf-8"><title>Seznam uživatelů bota</title>'
        . '<style>body{font-family:sans-serif;}table{border-collapse:collapse}td,th{border:1px solid #ccc; padding: 0.5rem 1rem;}</style>'
        . '</head><body>';
    echo '<form method="post"><input type="hidden" name="admin" value="' . $_POST['admin'] . '" /><ul>'
        . '<li><button type="submit" name="filter" value="all">všichni uživatelé</button>'
        . '<li><button type="submit" name="filter" value="suplovani">odběratelé suplování</button>'
        . '<li><button type="submit" name="filter" value="canteen">odběratelé jídelníčku</button>'
        . '</form><form method="post"><input type="hidden" name="admin" value="' . $_POST['admin'] . '" />'
        . '<li><input type="text" name="classes" placeholder="pouze tyto třídy (oddělené čárkou)" value="' . $classes . '" style="width:200px" /><button type="submit" name="filter" value="classes">zobrazit</button>'
        . '</ul></form>';
    echo '<ul><li><form method="post" action="send-manual.php"><input type="hidden" name="admin" value="' . $_POST['admin'] . '" />'
        . '<input type="hidden" name="filter" value="' . $filter . '" />'
        . '<input type="hidden" name="classes" value="' . $classes . '" /><button type="submit">poslat zprávu aktuálnímu filtru</button></form></ul>';
    echo '<p>Poznámka: Pouze tlačítko všichni uživatelé vypisuje data z obou tabulek, jindy se na boolean hodnoty nedá spolehnout.</p>';
    echo '<table><tr><th>Messenger ID</th><th>jméno</th><th>příjmení</th><th>suplování?</th><th>třída</th><th>obědy?</th><th>alergeny?</th></tr>';
    echo listUsers($_POST);
    echo '</table>';
    echo '</body></html>';
} else {
    echo $adminPasswordForm;
}

function listUsers($params) {
    $users = processFilters($params);
    $returnHtml = '';

    foreach ($users as $messenger_id => $user) {
        $returnHtml .= '<tr><td>' . $messenger_id . '</td><td>' . $user['first_name'] . '</td><td>' . $user['last_name']
            . '</td><td>' . ($user['suplovani'] ? 'ano' : 'ne') . '</td><td>' . $user['class'] . '</td><td>'
            . ($user['canteen'] ? 'ano' : 'ne') . '</td><td>' . ($user['allergens'] ? 'ano' : 'ne') . '</td></tr>';
    }

    return $returnHtml;
}
