<?php
include('src/admin.php');

if (isset($_POST['admin']) && $_POST['admin'] == $secrets['admin']) {
    header('Content-Type: text/plain');
    $file = getSuplovani();
    $file2 = file_get_contents('https://jidelna.gyohavl.cz/faces/login.jsp');
    echo plain('8.B', $file) . PHP_EOL;
    var_dump(obedy(true, $file2));
} else {
    echo $adminPasswordForm;
}
