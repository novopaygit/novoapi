<?php
include 'init.php';
// ===================================================================== request values
// --------------------------------------------------------- request values
$currency    = request('currency');      // 가상화폐
$price       = request('price');         // 결제금액(원)
$order_id    = request('order_id');      // 주문번호
$item_name   = request('item_name');     // 상품명
$buyer_name  = request('buyer_name');    // 구매자명
$token       = request('novopay_token'); // 토큰
$receive_url = request('receive_url');   // 결과전송 URL
$receive_url_base = base64_decode($receive_url);
// --------------------------------------------------------- check values
if ($currency    == '') return error('결제화폐가 필요합니다.');
if ($price       == '') return error('결제금액이 필요합니다.');
if ($order_id    == '') return error('주문번호가 필요합니다.');
if ($item_name   == '') return error('상품명이 필요합니다.');
if ($buyer_name  == '') return error('구매자명이 필요합니다.');
if ($token       == '') return error('결제 토큰값이 필요합니다');
if ($receive_url == '') return error('결과전송 URL이 필요합니다.');
$receive_url = base64_decode($receive_url);

// ===================================================================== 토큰 정보 확인
$svcToken = instanceSVC('Token');
$token_info = $svcToken->getTokenInfo($token);
if (!$token_info) return error('유효하지 않은 결제 토큰값입니다.');
if ($token_info['expire_dt'] < date('Y-m-d H:i:s')) return error('만료된 결제 토큰값입니다.');
$mall_id = $token_info['mall_id'];

// ===================================================================== 결제정보 확인
// ---------------------------------------------------------
$svcCoin = instanceSVC('CoinExchange');


// --------------------------------------------------------- 가상화폐 목록
$currency_list = $svcCoin->getOnlyCurrencyList();
if (!in_array($currency, $currency_list)) return error('유효하지 않은 결제화폐입니다.');
// --------------------------------------------------------- 현재시세 및 결제금액
$unit_price = $svcCoin->getCurrentQuotation($currency);
$currency_info = $svcCoin->getCurrencyInfo($currency);
$coin_pay = round(floatval($price / $unit_price), 4); // 코인결제금액
$coin_fee = $currency_info['txFee'];
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
		'mod_dt'     => date('Y-m-d H:i:s')
	);

// ===================================================================== 토큰/주문번호 정보 확인
// --------------------------------------------------------- 임시주문정보 확인
$daoPayTemp = instanceDAO('PayTemp');
$pay_temp_info = $daoPayTemp->getInfo4Token($token);
if ($pay_temp_info) {
	if ($pay_temp_info['mall_id'] != $mall_id) return error('유효하지 않은 쇼핑몰코드입니다.');
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
		case 'paid'  : return error('이미 결제 완료된 결제 정보입니다.');
		case 'error' : return error('결제 취소된 결제 정보입니다.');
	}
	if ($order_id != $payment_info['order_id']) return error('주문번호가 일치하지 않습니다.');
}
$order_info = $daoPayment->getInfo4OrderNo($mall_id, $order_id);
if ($order_info) {
	if (!$payment_info) return error('주문정보를 재확인 바랍니다.');
	if ($order_info['token_id'] != $payment_info['token_id']) return error('주문 토큰 정보가 유효하지 않습니다.');
	if ($order_info['order_id'] != $payment_info['order_id']) return error('주문 번호 정보가 유효하지 않습니다.');
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
if (!$result) return error('결제정보 임시 저장 중 오류가 발생하였습니다.');

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
		return error('거래소 통신 토큰정보가 없습니다.', 'document.location.reload()');
	}
	$data = $svcCoin->getUserBalance($exch_token, $currency);
	if (!$data) return error('결제가능 코인 조회 중 오류가 발생하였습니다.');
	$payable_coin = '';
	foreach ($data as $row) {
		if ($row['currency'] != $currency) continue;
		$payable_coin = $row['available'];
		break;
	}
	if ($payable_coin == '') return error('결제가능 코인 정보가 없습니다.');

	$payment_data = array(
		  'exch_cd'         => 'okbit'
		, 'exch_user_id'    => $_SESSION['pay_user_id']
		, 'exch_user_nm'    => $_SESSION['pay_user_nm']
		, 'payable_coin'    => $payable_coin
		, 'token_id'        => $token
		, 'currency'        => $currency
		, 'order_id'        => $order_id
		, 'price'           => $price
		, 'amount'          => $coin_pay
		, 'buyer_name'      => $buyer_name
		, 'item_name'       => $item_name
		, 'exch_token'      => $exch_token
		, 'coin_addr_tag'   => ''
		, 'mod_dt'          => date('Y-m-d H:i:s')
	);
	if ($pay_temp_info['currency'] == $currency) {
	} else {
		$deposit_data = $svcCoin->getDepositInfo($exch_token, $currency, $coin_pay, $price);
		if (!$deposit_data) return error('입금주소 요청 오류가 발생하였습니다.');
		$payment_data['exch_req_id']     = $deposit_data['reqId'];
		$payment_data['coin_addr']       = $deposit_data['address'];
	}
	if ($payment_info) {
		$result = $daoPayment->update($payment_data, 'pay_no = '. $payment_info['pay_no']);
	} else {
		$payment_data['mall_id']     = $pay_temp_info['mall_id'];
		$payment_data['pay_temp_no'] = $pay_temp_info['pay_temp_no'];
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
function request($key) {
	if (isset($_GET[$key])) return trim($_GET[$key]);
	return '';
}
// ---------------------------------------------------------
function error($msg, $add='') {
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
					<div class="header-icon">NovoPay</div>
					<div class="close-icon"><img src="/images/close-icon.svg"></div>
				</div>
				<div class="timer-row">
					<div class="timer-message">
						<span>결제 대기 중입니다...</span>
					</div>
					<div class="timer-clock"></div>
				</div>
			</div>
			<div class="order-details">
				<div>
					<span>1 <?=$currency?> = <?=number_format($unit_price)?>원</span>
				</div>
				<div>
					<span>결제금액</span>
					<span><?=$coin_tot?> <?=$currency?> : 상품금액 <?=$coin_pay?> <?=$currency?> (<?=number_format($price)?>원) + 수수료 <?=$coin_fee?> <?=$currency?></span>
				</div>
			</div>
			<!--div class="tab-tit">
				<span class="on">로그인</span>
				<span>코인주소</span>
			</div-->
			<div class="tab-cont-box">
				<div class="tab-cont">
					<div class="login-box">
						<div id="login-before"<?php if ($is_login) echo ' style="display: none;";' ?>>
							<form name="frmLogin" method="post">
								<input type="hidden" name="currency" value="<?=$currency?>" />
								<input type="hidden" name="is_test" />
								<div class="title">OK-BIT Login</div>
								<div class="user_id"><input type="text" name="user_id" placeholder="E-mail" /></div>
								<div class="user_pw"><input type="password" name="user_pw" placeholder="Password" /></div>
								<div class="user_otp"><input type="text" name="user_otp" placeholder="OTP Code" /></div>
								<div class="button">
									<button type="submit" class="btn-login bg-orange">LOGIN</button>
								</div>
							</form>
						</div>
						<div id="login-after"<?php if (!$is_login) echo ' style="display: none;"'; ?>>
							<form name="frmPay" method="post">
								<input type="hidden" name="is_test" />
								<table class="pay">
									<tr>
										<th>이름 :</th>
										<td class="user_nm"><?php if ($is_login) echo $_SESSION['pay_user_nm']; ?></td>
									</tr>
									<tr>
										<th>결제가능 코인 :</th>
										<td class="payable_coin"><?php if ($is_login) echo $payable_coin .' '. $currency; ?></td>
									</tr>
									<tr>
										<th>OTP Code :</th>
										<td class="pay_otp"><input type="text" name="pay_otp" /></td>
									</tr>
								</table>
								<div class="button">
									<button type="submit" class="bg-orange">Payment</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<!--div class="tab-cont" style="display: none;">
					<div class="payment-box">
						<div>
							<div class="qrcode">
								<img src="<?=$qrcode_path?>" width="250" />
							</div>
							<div class="addr_info">
								<div class="refresh">새로고침</div>
								<div class="addr_title">입금주소 :</div>
								<div class="address">
									<?=$coin_addr?>
								</div>
							</div>
						</div>
						<div class="button">
							<button>Open in wallet</button>
						</div>
					</div>
				</div-->
			</div>
			<div class="test-zone">
				<div style="float: left; margin-right: 5px;">
					<button type="button" id="btn_auto_login">테스트 로그인</button>
				</div>
				<div style="float: left; margin-right: 5px;">
					<button type="button" id="btn_auto_pay">테스트 결제</button>
				</div>
				<div style="float: left;">
					<form name="frmReceive" method="post" action="<?=$receive_url?>">
						<input type="hidden" name="novopay_result_cd" value="0000" />
						<input type="hidden" name="novopay_result_msg" value="성공" />
						<input type="hidden" name="novopay_enc_data" value="<?=getPayEncData('test')?>" />
						<div><button type="submit">테스트 결과전송</button></div>
					</form>
				</div>
				<!--div style="float: left; margin-left: 5px;">
					<form name="frmNotice" method="post" action="<?=$receive_url?>">
						<input type="hidden" name="novopay_result_cd" value="0000" />
						<input type="hidden" name="novopay_result_msg" value="성공" />
						<input type="hidden" name="novopay_enc_data" value="<?=getPayEncData('notice')?>" />
					</form>
					<button type="button" id="btn_callback">입금 통보</button>
				</div-->
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
<!--

//-->
</script>
</body>
</html>