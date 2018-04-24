<?php
include dirname(dirname(dirname(__DIR__))) .'/init.php';

// --------------------------------------------------------- variables
$novopay_id = '';
$secret_key = '';
$auth_key   = '';

// --------------------------------------------------------- http header
$headers = getallheaders();

foreach ($headers as $k => $v) {
	switch ($k) {
		case 'NovoPay-ID' : $novopay_id = $v; break;
		case 'Secret-Key' : $secret_key = $v; break;
		case 'Auth-Key'   : $auth_key   = $v; break;
	}
}

// --------------------------------------------------------- check values
if ($novopay_id == '') errorapi('9001', 'need novopay id');
if ($secret_key == '') errorapi('9002', 'need secret key');
if ($auth_key   == '') errorapi('9003', 'need auth key');

// --------------------------------------------------------- TODO : 아이디/키 확인 등

$daoMall = instanceDAO('Mall');
$mall_info = $daoMall->getMallInfo($novopay_id);

$mallCfg = getMallConfig($novopay_id); //몰의  인증암호는 config 화일에서 가져오자 

if (!$mall_info) errorapi('9010', 'not exists mall');
if ($mallCfg['AUTH_KEY'] != $auth_key) errorapi('9012', 'invalid auth key');
//secret 키는 db에서가져온다.
if (hash('sha256',$mall_info['secret_key']) != $secret_key) errorapi('9011', 'invalid secret key');


// --------------------------------------------------------- default parameter
$opt_lang = requestapi('opt_lang');
$opt_ver  = requestapi('opt_ver');
$opt_time = requestapi('opt_time');

// ===================================================================== function
// ---------------------------------------------------------
function requestapi($k) {
	if (!isset($_POST[$k])) return '';
	return base64_decode($_POST[$k]);
}
// ---------------------------------------------------------
function successapi($data=array()) {
	$def = array('result_cd' => '0000', 'err_msg' => 'success');
	$res = array_merge($def, $data);
	responseapi($res);
}

// ---------------------------------------------------------
function errorapi($err_cd, $err_msg) {
	$res = array('result_cd' => $err_cd, 'err_msg' => $err_msg);
	responseapi($res);
}
// ---------------------------------------------------------
function responseapi($json) {
	if (is_array($json)) $json = json_encode($json);
	echo $json;
	exit;
}
?>