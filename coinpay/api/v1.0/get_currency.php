<?php
include 'init.php';

//$svc = instanceSVC('CoinExchange');
//20180321 최인석 신규클래스로 변경 
//$novopay_id 는 mall ID이다. 
$svc = instanceSVC('CoinExch',$novopay_id);

$list = $svc->getOnlyCurrencyList();
$currency = array();

if (is_array($list)) {
	foreach ($list as $v) $currency[$v] = $v;
}

$res = array(
		'currency' => $currency
	);

successapi($res);

?>