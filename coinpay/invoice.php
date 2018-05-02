<?php
include 'init.php';
// ===================================================================== request values
// --------------------------------------------------------- request values

$currency    = requestINV('currency');      // 가상화폐
$price       = requestINV('price');         // 결제금액(원)
$order_id    = requestINV('order_id');      // 주문번호
$item_name   = requestINV('item_name');     // 상품명
$buyer_name  = requestINV('buyer_name');    // 구매자명
$buyer_id  = requestINV('buyer_id');    // 구매자아이디
$buyer_email  = requestINV('buyer_email');    // 구매자이메일
$buyer_mobile  = requestINV('buyer_mobile');    // 구매자전화번호
$token       = requestINV('novopay_token'); // 토큰
$receive_url = requestINV('receive_url');   // 결과전송 URL 
$receive_url_base = base64_decode($receive_url); //결과전송 URL 복호화




// --------------------------------------------------------- check values
if ($currency    == '') return errorINV('결제화폐가 필요합니다.');
if ($price       == '') return errorINV('결제금액이 필요합니다.');
if ($order_id    == '') return errorINV('주문번호가 필요합니다.');
if ($item_name   == '') return errorINV('상품명이 필요합니다.');
if ($buyer_name  == '') return errorINV('구매자명이 필요합니다.');
if ($buyer_id  == '')   return errorINV('구매자아이디가 필요합니다.');
if ($token       == '') return errorINV('결제 토큰값이 필요합니다');
if ($receive_url == '') return errorINV('결과전송 URL이 필요합니다.');

$receive_url = $receive_url_base;




// ===================================================================== 토큰 정보 확인


$svcToken = instanceSVC('Token');
$token_info = $svcToken->getTokenInfo($token);
if (!$token_info) return errorINV('유효하지 않은 결제 토큰값입니다.');
if ($token_info['expire_dt'] < date('Y-m-d H:i:s')) return errorINV('만료된 결제 토큰값입니다.');
$mall_id = $token_info['mall_id'];

// ===================================================================== Receive URL 이 등록된값인지 확인 
$mallCfg = getMallConfig($mall_id); //쇼핑몰의 ReceiveUrl config 화일에서 가져와서 등록된 값인지 비교한다.
if ($mallCfg['RECEIVE_URL'] != $receive_url_base){
	//return errorINV('NovoPay에 등록되지않은 ReceiveUrl 입니다. 관리자에게 문의하세요');
	echo "<script>alert(\"등록되지않은 ReceiveURl입니다.확인되지 않은 URL은 보안상 결체처리를 진행 할수없습니다. 관리자에게 문의하세요\");</script>";
	exit;
}



// ===================================================================== 결제정보 확인
// ---------------------------------------------------------
$svcCoin = instanceSVC('CoinExch',$mall_id);


// --------------------------------------------------------- 가상화폐 목록
$currency_list = $svcCoin->getOnlyCurrencyList();
if (!in_array($currency, $currency_list)) return errorINV('유효하지 않은 결제화폐입니다.');
// --------------------------------------------------------- 현재시세 및 결제금액
$unit_price = $svcCoin->getCurrentQuotation($currency);
if ($unit_price == 0 or !$unit_price)return errorINV($currency. " 의 현재시세가 거래소에 존재하지않아 결제가 불가능합니다.");

$currency_info = $svcCoin->getCurrencyInfo($currency);
$coin_pay = round(floatval($price / $unit_price), 4); // 코인결제금액
if ($coin_pay == 1) {
	$coin_pay = 1.0;
}
$coin_fee = $currency_info['fee']; //출금수수료
$coin_tot = $coin_pay + $coin_fee;
// --------------------------------------------------------- 임시주문정보 데이터
$pay_temp_data = array(
		'token_id'   => $token,
		'currency'   => $currency,
		'order_id'   => $order_id,
		'price'      => $price,
		'unit_price' => $unit_price,
		'coin_pay'   => $coin_pay,
		'coin_fee'   => $coin_fee,
		'item_name'  => $item_name,
		'buyer_name' => $buyer_name,
		'buyer_id'   => $buyer_id,
		'buyer_email' => $buyer_email,
		'buyer_mobile' => $buyer_mobile,
		'mod_dt'     => date('Y-m-d H:i:s')
	);

// ===================================================================== 토큰/주문번호 정보 확인
// --------------------------------------------------------- 임시주문정보 확인
$daoPayTemp = instanceDAO('PayTemp');
$pay_temp_info = $daoPayTemp->getInfo4Token($token);
if ($pay_temp_info) {
	if ($pay_temp_info['mall_id'] != $mall_id) return errorINV('유효하지 않은 쇼핑몰코드입니다.');
	$pay_temp_mode = 'update';
} else {
	$pay_temp_data['mall_id'] = $mall_id;
	$pay_temp_data['status']  = 'ready';
	$pay_temp_data['reg_dt']  = date('Y-m-d H:i:s');
	$pay_temp_mode = 'insert';
}
// --------------------------------------------------------- 주문정보 확인
$daoPayment = instanceDAO('Payment');
$payment_info = $daoPayment->getInfo4Token($token);
if ($payment_info) {
	$pay_status = $payment_info['status'];
	switch ($pay_status) {
		case 'paid'  : return errorINV('이미 결제 완료된 결제 정보입니다.');
		case 'error' : return errorINV('결제 취소된 결제 정보입니다.');
	}
	if ($order_id != $payment_info['order_id']) return errorINV('주문번호가 일치하지 않습니다.');
}
$order_info = $daoPayment->getInfo4OrderNo($mall_id, $order_id);
if ($order_info) {
	if (!$payment_info) return errorINV('주문정보를 재확인 바랍니다.');
	if ($order_info['token_id'] != $payment_info['token_id']) return errorINV('주문 토큰 정보가 유효하지 않습니다.');
	if ($order_info['order_id'] != $payment_info['order_id']) return errorINV('주문 번호 정보가 유효하지 않습니다.');
}
// =====================================================================
// --------------------------------------------------------- pay_temp 처리
switch ($pay_temp_mode) {
	case 'insert' :
		$result = $daoPayTemp->insert($pay_temp_data);
		break;
	case 'update' :
		$result = $daoPayTemp->update($pay_temp_data, 'pay_temp_no = '. $pay_temp_info['pay_temp_no']);
		break;
	default :
		$result = true;
		break;
}
if (!$result) return errorINV('결제정보 임시 저장 중 오류가 발생하였습니다.');

//$coin_addr = getCoinAddress();
$coin_addr = '';
// ===================================================================== 기타
// --------------------------------------------------------- QRCode
//$qr_text = 'bitcoin:'. $coin_addr .'?amount='. $coin_tot;
//instanceQRCode();
//$qrcode_path = makeQRCode($qr_text);
$qrcode_path = '';

// --------------------------------------------- 로그인 여부
$is_login = isCoinPayLogin();
if ($is_login) {
	$exch_token = isset($_SESSION['exch_token']) ? $_SESSION['exch_token'] : '';
	if ($exch_token == '') {
		session_destroy();
		return errorINV('거래소 통신 토큰정보가 없습니다.', 'document.location.reload()');
	}

	
	$data = $svcCoin->getUserBalance($exch_token, $currency);
	if (!$data) return errorINV('결제가능 코인 조회 중 오류가 발생하였습니다.');
	$payable_coin = '';
	foreach ($data as $row) {
		if ($row['currency'] != $currency) continue;
		$payable_coin = $row['available_amt'];
		break;
	}

	//$payable_coin = 5.55;
	if ($payable_coin == '') return errorINV('결제가능 코인 정보가 없습니다.');

	$mallinfo = getMallConfig($mall_id);
	$payment_data = array(
		  'exch_cd'         => $mallinfo['EXCHANGE_ID'] //'okbit'
		, 'exch_user_id'    => $_SESSION['pay_user_id']
		, 'exch_user_nm'    => $_SESSION['pay_user_nm']
		, 'payable_coin'    => $payable_coin
		, 'token_id'        => $token
		, 'currency'        => $currency
		, 'order_id'        => $order_id
		, 'price'           => $price
		, 'amount'          => $coin_pay
		, 'coin_fee' 		=> $coin_fee
		, 'buyer_name'      => $buyer_name
		, 'buyer_id' 	    => $buyer_id
		, 'buyer_email' 	=> $buyer_email
		, 'buyer_mobile' 	=> $buyer_mobile
		, 'item_name'       => $item_name
		, 'exch_token'      => $exch_token
		, 'coin_addr_tag'   => ''
		, 'mod_dt'          => date('Y-m-d H:i:s')
	);

	// 아래조건이 왜필요한지 이해가안됨 일단 뺌 cis 
	//if ($pay_temp_info['currency'] == $currency) {
	//} else {

		$deposit_data = $svcCoin->getDepositInfo($exch_token, $currency, $coin_pay, $price, true);
		if (!$deposit_data) return errorINV('입금주소 요청 오류가 발생하였습니다.');
		$payment_data['exch_req_id']     = $deposit_data['reqid'];
		$payment_data['coin_addr']       = $deposit_data['address'];

	//}
	//로직상 단에서 임시결제 정보가없으면 INsert 하는 로직을 타게된다면 다시조회해서 
    // 가져와야됨 안그러면 변수가 존재하지않는 알림 이 나옴 20180323 최인석 추가 
	$pay_temp_infologin = $daoPayTemp->getInfo4Token($token);
	if ($pay_temp_infologin) {
		if ($pay_temp_infologin['mall_id'] != $mall_id) return errorINV('유효하지 않은 쇼핑몰코드입니다.');		
	}
	//=====================================추가 끝 

	if ($payment_info) {
		$result = $daoPayment->update($payment_data, 'pay_no = '. $payment_info['pay_no']);
	} else {
		$payment_data['mall_id']     = $pay_temp_infologin['mall_id'];
		$payment_data['pay_temp_no'] = $pay_temp_infologin['pay_temp_no'];
		$payment_data['status']      = 'login';
		$payment_data['reg_dt'] = date('Y-m-d H:i:s');
		$result = $daoPayment->insert($payment_data);
	}
}
// --------------------------------------------- 세션 저장
$_SESSION['pay_token'] = $token;
$_SESSION['pay_coin']  = $coin_pay;
$_SESSION['currency']  = $currency;



// ===================================================================== Function
// ---------------------------------------------------------
function requestINV($key) {
	if (isset($_GET[$key])) return trim($_GET[$key]);
	return '';
}
// ---------------------------------------------------------
function errorINV($msg, $add='') {
	global $receive_url_base;
	$html = array();
	$html[] = '<!DOCTYPE html>';
	$html[] = '<html>';
	$html[] = '<head>';
	$html[] = '<script type="text/javascript">';
	$html[] = '<!--';
	$html[] = 'alert("'. $msg .'");';
	if ($add) {
		$html[] = $add;
	} else {
		$html[] = 'document.location.href = "'. $receive_url_base .'?novopay_is_close=Y";';
		
		
	}
	$html[] = '//-->';
	$html[] = '</script>';
	$html[] = '</head>';
	$html[] = '<body>';
	$html[] = '</body>';
	$html[] = '</html>';
	echo implode($html, PHP_EOL);
	exit;
}
?><!DOCTYPE html>
<html>

<head>

<link type="text/css" rel="stylesheet" href="/css/invoice.css?dummy=<?=time()?>">
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script type="text/javascript">
<!--
	var varReceiveURL = '<?=$receive_url?>';
	var varPayToken   = '<?=$token?>';
//-->
</script>
<script type="text/javascript" src="/coinpay/js/invoice.js?dummy=<?=time()?>"></script>
</head>

<body style="background: transparent;">
<div class="modal-backdrop fade-in"></div>
<div class="modal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="top-header">
				<div class="header">
					<div class="header-icon"><span class="font-eng" id ="btn_auto_login" >NovoPay</span> 상품결제</div>
					<div class="close-icon"><img src="/images/close-icon.svg"></div>
				</div>				
			</div>

			<div class="tab-cont-box">
				<div class="tab-cont">
					<div class="login-box">
						<div id="login-before"<?php if ($is_login) echo ' style="display: none;";' ?>>
				            <div class="login-image-box"><img src="/images/login-image.png"></div>
							<form name="frmLogin" method="post">
								<input type="hidden" name="currency" value="<?=$currency?>" />
								<input type="hidden" name="is_test" />
								<div class="title"><span class="font-eng">OK-BIT</span> 로그인</div>
								<div class="user_id"><input type="text" name="user_id" placeholder="이메일" /></div>
								<div class="user_pw"><input type="password" name="user_pw" placeholder="비밀번호" /></div>
								<div class="user_otp"><input type="text" name="user_otp" placeholder="OTP" /></div>
								<div class="button">
									<div class="bottom-button"><button type="submit" class="bg-blue-02">로그인</button></div>
								</div>
							</form>
							<div class="before-bottom">
							    <div class="before-timer floL"><!--<div class="timer-clock"></div>--></div>
								<div class="floR"><p class="font-eng">Powered by NOVOPAY&nbsp;</p></div>
							</div>
						</div>

						<div id="login-after"<?php if (!$is_login) echo ' style="display: none;"'; ?>>
							<div class="payment-wrap">
								<div class="payment-send">
									<ul>
										<li class="w25"><h4>송금가능금액</h4></li>
										<li class="w50"><h3 id="payable_coin"><?=$payable_coin?> <span><?=$currency?></span></h3></li>
										<li class="w25">
											<div class="send-logo"><span><img src="/images/logo-<?=$currency?>.png" alt="bitcoin" title="bitcoin"></span></div>
										</li>
									</ul>
								</div>
							</div><!-- /payment-wrap -->

							<div class="customerpay-wrap">
								<ul class="fl-left">
									<li class="w40">
										<div class="customerpay-title">
											<h5>고객님이 송금할 금액</h5>
											<span>(<?=number_format($price)?><em>원</em>)</span>
										</div>
									</li>
									<li class="w60"><div class="pay-bigtext"><?=$coin_pay?> <span><?=$currency?></span></div></li>
								</ul>
								<p class="text-R">(가상화폐 거래 송금 수수료는 포함되지 않습니다.)</p>
							</div><!-- /customerpay-wrap -->

							

							<form name="frmPay" method="post">
								<input type="hidden" name="is_test" />
								
								<div class="otpcode-wrap">
								    <ul class="fl-left">
									    <li class="w30"><h5>OTP Code</h5></li>
										<li class="w70"><div class="pay_otp"><input type="text" name="pay_otp" class="w100"/></div></li>
									</ul>
								</div>
								<div class="bottom-agree">
								    <fieldset>									    
										<ul class="fl-left agree-check-box">
										    <li>
												<label><input type="checkbox" name="agree1" id ="agree1" value=""><span>이용약관 동의</span></label>
												<button type="button" id="see_agree1" class="use-pop-btn">약관보기</button>
											</li>
										    <li>
												<label><input type="checkbox" name="agree2" id="agree2" value=""><span>개인정보수집 동의</span></label>
												<button type="button" id="see_agree2" class="use-pop-btn">약관보기</button>
											</li>
										</ul>

									</fieldset>
								</div><!-- /bottom-agree -->
								
								<div class="bottom-button"><button type="submit" class="bg-blue-02">송금하기</button></div>

								<div class="after-bottom">
							    <div class="after-timer floL"><div class="timer-clock">09:55</div></div>
								<div class="floR"><p class="font-eng">Powered by NOVOPAY&nbsp;</p></div>
							</div>
							</form>
						</div>
					</div>
				</div>		
			</div>
			<div style="float: left; display:none;">
				<form name="frmReceive" method="post" action="<?=$receive_url?>">
					<input type="hidden" name="novopay_result_cd" value="0000" />
					<input type="hidden" name="novopay_result_msg" value="성공" />
					<input type="hidden" name="novopay_receive_secret_key" value="receivekey" />
					<input type="hidden" name="novopay_enc_data" value="" />
					<div><button type="submit">결과전송</button></div>
				</form>
			</div>

		
			<iframe style="display:none;" src="" id="hidden_frame" name="hidden_frame" frameborder="0" width="0" height="0">

		</div>
	</div>
</div>

<script type="text/javascript">
<!--

//-->
</script>
</body>
</html>