<?php
/**
 * 20180405 최인석 
 * tbl_mall 테이블의 secret_key 키반환하는 API
 * init_secret.php 에서는 secret 키르 ㄹ체크하지않는다.
 * init.php는 secret 키르 체크함 
 */

include 'init_secret.php';

$daoMall = instanceDAO('Mall');
$mall_info = $daoMall->getMallInfo($novopay_id);


//----------------------------------------------- make response
$res = array(
	'secret_key' => $mall_info['secret_key']
);

// --------------------------------------------------------- response


successapi($res);


?>