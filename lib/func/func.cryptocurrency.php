<?php
// =============================================================================================
// ---------------------------------------------------------------------------------

/*
 // 이제안씀 최인석 20180403 . 아래 instanceCoinExch 사용함
 function instanceExchange($exch_cd='okbit') {
	static $objExchange;
	if (is_null($objExchange)) {
		require_once OKBIT_LIB . DS .'class.OK-BIT.php';
		$objExchange = new OkBitClient();
	}
	return $objExchange;
}
*/

 // 20180321 최인석 : CoinExch 클래스를 instance 하는 함수 
function instanceCoinExch($exch_cd='okbit') {

	
	static $objCoinExch;
	if (is_null($objCoinExch)) {
		//require_once CLS_ROOT . DS .'class.CoinExch.php';		
		$objCoinExch = new CoinExch($exch_cd);
	}
	return $objCoinExch;
}


// ============================================================================================= OTP
// ---------------------------------------------------------------------------------
function get_otp($secret) {

	
	require_once CLS_ROOT . DS .'class.GoogleAuthenticator.php';	
	$ga = new PHPGangsta_GoogleAuthenticator();
	return $ga->getCode($secret);
	
	

}
// ============================================================================================= QR CODE
// ---------------------------------------------------------------------------------
function instanceQRCode() {
	include_once CLS_ROOT . DS .'qrcode'. DS .'qrlib.php';
}
// ---------------------------------------------------------------------------------
function makeQRCode($cpmt='') {
	$filename = '027.png';
	$savepath = TMP_ROOT . DS .'qrcode'. DS . $filename;
	$qr_path = '/'. TMP_FOLDER .'/qrcode/'. $filename;
	QRcode::png($cpmt, $savepath, QR_ECLEVEL_L, 7);
	return $qr_path;
}
/*
function getExchangeCurrencyList() {
	$exch = instanceExchange();
	if (!$exch->pubilc_symbols()) return false;
	if ($exch->getSuccessCode() != $exch->getResCode()) return false;
	$data = $exch->getResData();

	$ret = array();
	foreach ($data as $row) {
		$row = (array)$row;
		$currency = $row['currency'];
		$ret[$currency] = $currency;
	}
	return $ret;
}
*/
?>