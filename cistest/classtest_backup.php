<pre>
<?php 
		include 'cisinit.php';
	// okbit 클래스테스트 
/*	require CLS_ROOT.'/coinexch/okbit/class.OK-BIT.php';
	$okbit = new OkBitClient('okbittestclient', 'wVJ09XebUkyEZ8n5');
	$result = $okbit->crypto_key('5e1398ff-ad0e-44bd-a3c8-d5f7c66ae15e');
	$list = $okbit->getResData();
	$rescode = $okbit->getResCode();
	
	var_dump($list);
	var_dump($rescode);*/
	

	//상위클래스테스트  coinexch
	
	
	/*// 잔액조회 
	include CLS_ROOT.'/class.CoinExch.php';	
	$coinExch = new CoinExch('testmall');	
	$param=array();
	//$res = $coinExch->getConstants();
	//
	//
	$param['access_token'] ='5e1398ff-ad0e-44bd-a3c8-d5f7c66ae15e';
	$param['currency'] ='BTC';
	//$param['password'] ='Okbittest0000!';
	//$param['otpcode'] ='114175';
	$res = $coinExch->getUserBalance($param);
	
    //$return = $res['msg'];    
    $return = $res['data'];    
    var_dump($return);*/

    //입금주소 
    include CLS_ROOT.'/class.CoinExch.php';	
	$coinExch = new CoinExch('testmall');	
	$param=array();
	//$res = $coinExch->getConstants();
	//
	//
	$param['access_token'] ='674d2638-50cc-4b66-8e66-4e9805219e4b';
	$param['currency'] ='BTC';
	$param['amount'] ='5.002';
	$param['price'] ='100000';
	$param['autosell'] =true;
	//$param['password'] ='Okbittest0000!';
	//$param['otpcode'] ='114175';
	
	$res = $coinExch->getDepositAddress($param);

	
    //$return = $res['msg'];    
    $return = $res['data'];    
    var_dump($return);
	
?>
</pre>