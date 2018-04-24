<?php
include 'init.php';

// ===================================================================== request values
// ---------------------------------------------------------
$is_test  = request_ajax('is_test');
$pay_otp  = request_ajax('pay_otp');
// ---------------------------------------------------------
$is_test = 'N';
if ($is_test == 'Y') {
	$pay_otp = get_otp(getCoinPayTestUser('otp_secret'));
}
// --------------------------------------------------------- check values
if ($pay_otp == '') return ajaxFail('OTP코드가 필요합니다.');
if (strlen($pay_otp) != 6) return ajaxFail('OTP코드를 확인하세요');

// ===================================================================== 결제 정보 조회
// --------------------------------------------------------- 임시결제정보
$pay_token = $_SESSION['pay_token'];
$daoPayTemp = instanceDAO('PayTemp');
$pay_temp_info = $daoPayTemp->getInfo4Token($pay_token);
if (!$pay_temp_info) return ajaxFail('유효하지 않은 결제 토큰 정보입니다.');
// --------------------------------------------------------- 결제정보
$daoPayment = instanceDAO('Payment');
$payment_info = $daoPayment->getInfo4PayTempNo($pay_temp_info['mall_id'], $pay_temp_info['pay_temp_no']);
if (!$payment_info) return ajaxFail('결제정보가 존재하지 않습니다.'. $pay_temp_info['pay_temp_no']);
// ---------------------------------------------------------
switch ($payment_info['status']) {
	case 'paid' : return ajaxFail('이미 결제완료된 상태입니다.');
	case 'cancel' : return ajaxFail('결제 취소 상태입니다.');
}

// ===================================================================== 출금 요청
// ---------------------------------------------------------
$svcCoinPay  = instanceSVC('CoinExch',$payment_info['mall_id']);
$token    = $payment_info['exch_token'];
$amount   = $payment_info['amount'];
$reqId    = $payment_info['exch_req_id'];
$address  = $payment_info['coin_addr'];
$currency = $payment_info['currency'];
$otpCode  = $pay_otp;
// ---------------------------------------------------------



$withdraw_data = $svcCoinPay->execPaymentWithdraw($token, $currency, $amount, $reqId, $address, $otpCode);

if (!$withdraw_data) {		
	$msg = '출금요청에 실패하였습니다.';
	return ajaxFail($msg);
}

if ($withdraw_data['status'] !='0000'){
	$res_code = $withdraw_data['status'];
	$res_msg = $withdraw_data['msg'];

	switch ($res_code) {
		case '4041' : $msg = 'OPT 코드가 유효하지 않습니다.'; break;
		default : $msg = '출금요청에 실패하였습니다.'. PHP_EOL. $res_code .' - '. $res_msg; break;
	}
	//session_destroy();
	return ajaxFail($msg);
}

// ===================================================================== 후처리
// --------------------------------------------------------- 임시결제정보
$pay_temp_data = array(
	'status' => 'paid', 'mod_dt' => date('Y-m-d H:i:s')
);
$daoPayTemp->update($pay_temp_data, 'pay_temp_no = '. $pay_temp_info['pay_temp_no']);
// --------------------------------------------------------- 결제정보 저장
$tid = $daoPayment->getNewTID();
$payment_data = array(
	  'status' => 'paid'
	, 'tid'    => $tid
	, 'pay_dt' => date('Y-m-d H:i:s')
	, 'mod_dt' => date('Y-m-d H:i:s')
);
$result = $daoPayment->update($payment_data, 'pay_no = '. $payment_info['pay_no']);
// ---------------------------------------------------------
$data = array(
	'pay_token'   => $_SESSION['pay_token'],
	'pay_coin'    => $_SESSION['pay_coin'],
	'currency'    => $_SESSION['currency'],
	'pay_tid'         => $tid,
	'pay_reqid'   => $reqId,
	'pay_user_id' => $_SESSION['pay_user_id']
);
// ReceiveSecretKey를 가져와서  반환값에 sha256으로 변환하여 전송해준다.---------------------
//$daoMall = instanceDAO('Mall');
//$mall_info = $daoMall->getMallInfo($payment_info['mall_id']);
//$receive_secret_key = hash('sha256', $mall_info['receive_secret_key']);

$enc_data =base64_encode(json_encode($data));
// ---------------------------------------------------------
$res = array(
	'result_cd'  => '0000',
	'result_msg' => 'Success',
	//'receive_secret_key' => $receive_secret_key, // 안씀 
	'enc_data'   => $enc_data
);
// ---------------------------------------------------------
session_destroy();
ajaxSuccess($res);

?>