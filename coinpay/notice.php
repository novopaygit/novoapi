<?php
$token = $_GET['token'];
file_get_contents('http://coin.jpiece.net/ws/call.php?target='. $token);
?>