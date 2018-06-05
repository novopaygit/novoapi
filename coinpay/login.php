<?php
include 'init.php';
// ===================================================================== request values
// ---------------------------------------------------------
$is_test  = request_ajax('is_test');
$user_id  = request_ajax('user_id');
$user_pw  = request_ajax('user_pw');
$user_otp = request_ajax('user_otp');
$currency = request_ajax('currency');
// ---------------------------------------------------------
if ($is_test == 'Y') {
	
	//otp 코드만 팝업으로 테스트하기위해 반환 GM4DMODCMFSTCNRV
	//return ajaxFail(get_otp(getCoinPayTestUser('otp_secret')));
	return ajaxFail(get_otp('GM4DMODCMFSTCNRV'));
	/*
		$user_id  = getCoinPayTestUser('user_id');
		$user_pw  = getCoinPayTestUser('user_pw');
		$user_otp = get_otp(getCoinPayTestUser('otp_secret'));
	*/
}
// --------------------------------------------------------- check values
if ($user_id  == '') return ajaxFail('이메일 주소가 필요합니다.');
if ($user_pw  == '') return ajaxFail('비밀번호가 필요합니다.');
if ($user_otp == '') return ajaxFail('OTP코드가 필요합니다.');
if ($currency == '') return ajaxFail('가상화폐 종류가 필요합니다.');

// ===================================================================== 임시결제 정보 조회
$pay_token = $_SESSION['pay_token'];
$daoPayTemp = instanceDAO('PayTemp');
$pay_temp_info = $daoPayTemp->getInfo4Token($pay_token);
if (!$pay_temp_info) return ajaxFail('유효하지 않은 결제 토큰 정보입니다.');




// ===================================================================== 로그인 및 자산정보 조회
// ---------------------------------------------------------
$svcCoin = instanceSVC('CoinExch',$pay_temp_info['mall_id']);
// --------------------------------------------------------- get token
$data = $svcCoin->getTokenInfo($user_id, $user_pw, $user_otp);
if (!$data) return ajaxFail('로그인에 실패하였습니다.');

if ($data['status'] !='0000'){
	$res_code = $data['status'];
	$res_msg = $data['msg'];

	$msg = '로그인에 실패하였습니다.'. PHP_EOL. $res_code .' - '. $res_msg;

	/*switch ($res_code) {
		case '4041' : $msg = 'OTP 코드가 유효하지 않습니다.'; break;
		default : $msg = '로그인에 실패하였습니다.'. PHP_EOL. $res_code .' - '. $res_msg; break;
	}	*/
	return ajaxFail($msg);
}




$exch_token = $data['data']['access_token'];



// --------------------------------------------------------- user info

$data = $svcCoin->getUserInfo($exch_token);

if (!$data) return ajaxFail($svcCoin->getLastError());
$pay_user_id = $data['email'];
$pay_user_nm = $data['name'];
if ($pay_user_nm == '') $pay_user_nm = 'BLANK';




// --------------------------------------------------------- balance
$databal = $svcCoin->getUserBalance($exch_token, $currency);
if (!$databal)  return ajaxFail('해당 코인의 지갑이 존재하지 않거나 잔액이 없습니다.');// return ajaxFail($svcCoin->getLastError());
$payable_coin = '';
foreach ($databal as $row) {
	if ($row['currency'] != $currency) continue;
	$payable_coin = $row['available_amt'];
	break;
}

if ( $payable_coin == '') return ajaxFail('해당 코인의 지갑이 존재하지 않거나 잔액이 없습니다.');

// ===================================================================== 입금주소
/*
$deposit_data = $svcCoin->getDepositInfo($exch_token, $currency, $pay_temp_info['coin_pay'], $pay_temp_info['price'], true);
if (!$deposit_data) {
	$svcErrmsg = $svcCoin->getLastError();
	return ajaxFail($svcErrmsg);
	//return ajaxFail('입금주소 요청 오류가 발생하였습니다.');
}*/
$deposit_result = $svcCoin->getDepositInfo($exch_token, $currency, $pay_temp_info['coin_pay'], $pay_temp_info['price'], true);

if (!$deposit_result) return ajaxFail('입금주소 요청에 실패 하였습니다.');

if ($deposit_result['status'] !='0000'){
	$res_code = $deposit_result['status'];
	$res_msg = $deposit_result['msg'];

	$msg = '입금주소 요청에 실패 하였습니다.'. PHP_EOL. $res_code .' - '. $res_msg;

	return ajaxFail($msg);
}

$deposit_data = $deposit_result['data'];


// ===================================================================== 임시결제정보 저장
$pay_temp_data = array(
	'status' => 'login', 'mod_dt' => date('Y-m-d H:i:s')
);
$daoPayTemp->update($pay_temp_data, 'pay_temp_no = '. $pay_temp_info['pay_temp_no']);

// ===================================================================== 결제정보 저장
$daoPayment = instanceDAO('Payment');
$payment_info = $daoPayment->getInfo4PayTempNo($pay_temp_info['mall_id'], $pay_temp_info['pay_temp_no']);
$mallinfo = getMallConfig($pay_temp_info['mall_id']);
$payment_data = array(	  
	  'exch_cd'         => $mallinfo['EXCHANGE_ID'] //'okbit'
	, 'exch_user_id'    => $pay_user_id
	, 'exch_user_nm'    => $pay_user_nm
	, 'payable_coin'    => $payable_coin
	, 'token_id'        => $pay_token
	, 'currency'        => $currency
	, 'order_id'        => $pay_temp_info['order_id']
	, 'price'           => $pay_temp_info['price']
	, 'amount'          => $pay_temp_info['coin_pay']
	, 'coin_fee'        => $pay_temp_info['coin_fee']
	, 'buyer_name'      => $pay_temp_info['buyer_name']
	, 'buyer_id'   		=> $pay_temp_info['buyer_id']
	, 'buyer_email'     => $pay_temp_info['buyer_email']
	, 'buyer_mobile'    => $pay_temp_info['buyer_mobile']
	, 'item_name'       => $pay_temp_info['item_name']
	, 'exch_token'      => $exch_token
	, 'exch_req_id'     => $deposit_data['reqid']
	, 'coin_addr'       => $deposit_data['address']
	, 'coin_addr_tag'   => ''
	, 'mod_dt'          => date('Y-m-d H:i:s')
);
if ($payment_info) {
	$result = $daoPayment->update($payment_data, 'pay_no = '. $payment_info['pay_no']);
} else {
	$payment_data['mall_id']     = $pay_temp_info['mall_id'];
	$payment_data['pay_temp_no'] = $pay_temp_info['pay_temp_no'];
	$payment_data['status']      = 'login';
	$payment_data['reg_dt'] = date('Y-m-d H:i:s');
	$result = $daoPayment->insert($payment_data);
}
if (!$result) return ajaxFail('결제정보 처리 중 오류가 발생하였습니다. - '. $daoPayment->getErrorMsg() );


// ===================================================================== Login
// ---------------------------------------------------------
$_SESSION['pay_user_id'] = $pay_user_id;
$_SESSION['pay_user_nm'] = $pay_user_nm;
$_SESSION['exch_token']  = $exch_token;

$res = array(
	'user_nm'      => $pay_user_nm,
	'payable_coin' => $payable_coin,
	'currency'     => $currency
);
ajaxSuccess($res);

?>