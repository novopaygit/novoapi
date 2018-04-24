
<!DOCTYPE html>
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
					<div class="header-icon">NovoPay <span>상품결제</span></div>
					<div class="close-icon"><img src="/images/close-icon.svg"></div>
				</div>
				<div class="timer-row text-R">
					<div class="timer-text">결제유효시간 : <span class="timer-clock pdl-5"></span></div>
				</div>
			</div><!-- /top-header -->

			<div class="payment-wrap">
			    <div class="payment-send">
				    <ul>
					    <li class="w25"><h4>송금가능금액</h4></li>
					    <li class="w50"><h3>11.110508 <span>BTC</span></h3></li>
					    <li class="w25">
						    <div class="send-logo"><span><img src="/images/logo-bitcoin.png" alt="bitcoin" title="bitcoin"></span></div>
						</li>
					</ul>
				</div>
			</div><!-- /payment-wrap -->

			<div class="customerpay-wrap">
			    <ul class="fl-left">
				    <li class="w40">
					    <div class="customerpay-title">
							<h5>고객님이 송금할 금액</h5>
							<span>(10,500<em>원</em>)</span>
						</div>
					</li>
					<li class="w60"><div class="pay-bigtext">0.000322<span>BTC</span></div></li>
				</ul>
				<p class="text-R">(가상화폐 거래 송금 수수료는 포함되지 않았습니다.)</p>
			</div><!-- /customerpay-wrap -->

			<div class="confirmpay-wrap">
				<div class="confirm-box">
					<ul class="fl-left">
						<li><h4>SMS 인증</h4></li>
						<li><div class="sms-input"><input type="text" name="" placeholder="인증번호를 요청하세요." /></div></li>
						<li><div class="confirm-button"><button type="submit" class="bg-blue" value="인증번호요청">인증번호요청</button></div></li>
					</ul>
				</div>
			</div><!-- /confirmpay-wrap -->

			<div class="bottom-agree">
			    <fieldset>
				    <legend>이용약관보기</legend>
					<label><input type="checkbox" name="" value=""><span>이용약관 동의</span></label>
					<label><input type="checkbox" name="" value=""><span>개인정보수집 동의</span></label>
				</fieldset>
			</div><!-- /bottom-agree -->

			<div class="bottom-button"><button type="submit" class="bg-red">송금하기</button></div>
			<div class="bottom-text"><p>powered by NOVOPAY</p></div>


		</div><!-- /modal-content -->
	</div><!-- /modal-content -->
</div><!-- //modal -->
<script type="text/javascript">
<!--

//-->
</script>
</body>
</html>