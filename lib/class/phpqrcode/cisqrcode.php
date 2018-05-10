<?php 
	include "./phpqrcode.php";
	QRcode::png("http://localhost:7777/phpqrcode/cisqrcode.php","./cisqrcode.png",0,7,2);

	echo "<img src='cisqrcode.png'>";

 ?>
