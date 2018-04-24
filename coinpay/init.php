<?php
include dirname(dirname(__FILE__)) .'/init.php';
require FUNC_ROOT .'/func.coinpay.php';


function request_ajax($key) {
	if (isset($_POST[$key])) return trim($_POST[$key]);
	return '';
}
?>