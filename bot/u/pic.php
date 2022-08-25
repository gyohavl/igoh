<?php
echo file_get_contents('https://suply.herokuapp.com/stav/pic.php?' . $_SERVER['QUERY_STRING']);
