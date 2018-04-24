$(document).ready(function() {
	// ----------------------------- WebSocket
	var socket;
	/*
	var host = "ws://222.236.47.89:5555/?client_id="+ varPayToken; // SET THIS TO YOUR SERVER
	try {
		socket = new WebSocket("ws://coin.jpiece.net:5555/?client_id="+ varPayToken);
		socket.onopen    = function(msg) {
						};
		socket.onmessage = function(msg) {
			// TODO : 수신 데이터 처리
			$('#btn_callback').prop('disabled', true);
			document.frmNotice.submit();
						};
		socket.onclose   = function(msg) {
						};

		var socket_timer;
		socket_timer = setInterval(function() {
			if (socket.readyState == '1') {
				socket.send('{"client_id": "'+ varPayToken +'"}');
				clearInterval(socket_timer);
			}
		}, 500);
	} catch(ex) {
		$('#btn_callback').prop('disabled', true);
		console.log(ex);
	}
	$('#btn_callback').click(function() {
		if (socket.readyState == 3) {
			alert('노티 서버와 연결되지 않았습니다.');
			return false;
		}
		$.get('notice.php?token='+ varPayToken, function(res) {
			console.log(res);
		});
	});*/
	// ----------------------------- Payment
	var $frmPay = $('form[name="frmPay"]');
	var $pay_otp = $('input[name="pay_otp"]', $frmPay);
	$frmPay.submit(function(event) {
		event.preventDefault();

		var check1 = $('#agree1').is(':checked');
		var check2 = $('#agree2').is(':checked');
		if (check1 == false || check2 == false) {
			alert("약관 및 개인정보수집에 동의 하셔야 송금이 가능합니다.");
			return false;
		}
		


		if ($('input[name="is_test"]', $frmPay).val() != 'Y') {
			if ($pay_otp.val() == '') {
				alert('OTP코드를 입력하세요.');
				$pay_otp.focus();
				return false;
			}
		}
		

		if (!window.confirm('결제를 진행하시겠습니까?')) return false;
		var req_data = $frmPay.serialize();
		$.ajax({
			type: 'POST',
			url : 'payment.php',
			data: req_data,
			datatype: 'json',
			contentType: 'application/x-www-form-urlencoded; charset=utf-8',
			beforeSend: function(xhr) {
			},
			success: function(res, textStatus, jqXHR) {
				console.log(res);
				if (!res.result) {
					alert(res.err_msg);
					return;
				}
				callbackPayment(res);
			},
			complete : function(jqXHR, textStatus) {
			},
			error: function(jqXHR, textStatus, errorThrown) {
			}
		});
		return false;
	});
	$('button[type="submit"]', $frmPay).click(function() {
		$('input[name="is_test"]', $frmPay).val('N');
	});
	$('#btn_auto_pay').click(function() {
		$('input[name="is_test"]', $frmPay).val('Y');
		//$user_id.val($(this).attr('user_id'));
		//$user_pw.val($(this).attr('user_pw'));
		$frmPay.trigger('submit');
	});
	function callbackPayment(res) {
		var $form = $('form[name="frmReceive"]');
		$('input[name="novopay_result_cd"]', $form).val(res.result_cd);
		$('input[name="novopay_result_msg"]', $form).val(res.result_msg);
		$('input[name="novopay_receive_secret_key"]', $form).val(res.receive_secret_key);		
		$('input[name="novopay_enc_data"]', $form).val(res.enc_data);
		$form.trigger('submit');
	};
	// ----------------------------- Login
	var $frmLogin = $('form[name="frmLogin"]');
	var $user_id  = $('input[name="user_id"]', $frmLogin);
	var $user_pw  = $('input[name="user_pw"]', $frmLogin);
	var $user_otp = $('input[name="user_otp"]', $frmLogin);
	$frmLogin.submit(function(event) {
		event.preventDefault();
		if ($('input[name="is_test"]', $frmLogin).val() != 'Y') {
			if ($user_id.val() == '') {
				alert('이메일을 입력하세요.');
				$user_id.focus();
				return false;
			} else if ($user_pw.val() == '') {
				alert('비밀번호를 입력하세요.');
				$user_pw.focus();
				return false;
			} else if ($user_otp.val() == '') {
				alert('OTP코드를 입력하세요.');
				$user_otp.focus();
				return false;
			}
		}
		var req_data = $frmLogin.serialize();
		$.ajax({
			type: 'POST',
			url : 'login.php',
			data: req_data,
			datatype: 'json',
			contentType: 'application/x-www-form-urlencoded; charset=utf-8',
			beforeSend: function(xhr) {
			},
			success: function(res, textStatus, jqXHR) {
				console.log(res);
				if (!res.result) {
					alert(res.err_msg);
					return;
				}
				afterLogin(res);
			},
			complete : function(jqXHR, textStatus) {
			},
			error: function(jqXHR, textStatus, errorThrown) {
			}
		});
		return false;
	});
	function afterLogin(res) {
		//$('.user_nm').text(res.user_nm);
		$('#payable_coin').text(res.payable_coin +' '+ res.currency);
		$('#login-before').hide();
		$('#login-after').show();
	}
	$('button[type="submit"]', $frmLogin).click(function() {
		$('input[name="is_test"]', $frmLogin).val('N');
	});
	$('#btn_auto_login').click(function() {
		$('input[name="is_test"]', $frmLogin).val('Y');
		//$user_id.val($(this).attr('user_id'));
		//$user_pw.val($(this).attr('user_pw'));
		$frmLogin.trigger('submit');
	});
	$('#btn_auto_logout').click(function() {
		$('input[name="is_test"]', $frmLogin).val('O');
		//$user_id.val($(this).attr('user_id'));
		//$user_pw.val($(this).attr('user_pw'));
		$frmLogin.trigger('submit');
	});
	// ----------------------------- close button
	$('.close-icon').css('cursor', 'pointer').click(function() {
		// 크로스도메인 문제로 쇼핑몰 수신URL로 전달 후 iFrame 컨트롤
		document.location.href = varReceiveURL +'?novopay_is_close=Y';
	});
	// ----------------------------- tab
	// ----------------- tab show
	var $tab_tit = $('.tab-tit > span');
	$tab_tit.css('cursor', 'pointer').each(function(idx) {
		$(this).click(function() {
			$tab_tit.removeClass('on');
			$tab_tit.eq(idx).addClass('on');
			$('.tab-cont').hide().eq(idx).show();
		});
	});
	// ----------------- tab content height
	//var tab_cont_h = 0;
	//$('.tab-cont').each(function() {
		//var h = $(this).height();
		//if (tab_cont_h < h) tab_cont_h = h;
		//console.log(h);
	//});
	//$('.tab-cont-box').height(tab_cont_h);

	// ----------------------------- refresh
	$('.addr_info .refresh').click(function() {
		document.location.reload();
	});
	$('#see_agree1').click(function() {		
		window.open('/coinpay/agreement1_page.php','이용약관보기','width=340, height=420, scrollbars=no');
	});

	$('#see_agree2').click(function() {		
		window.open('/coinpay/agreement2_page.php','개인정보수집','width=340, height=420, scrollbars=no');
	});

	
	// ----------------------------- timer
	var limit_time = 60 * 10 - 5; // 10분에서 딜레이타임 5초 제외
	var timer_display = document.querySelector('.timer-clock');
	var rest_time = limit_time;
	var timer = setInterval(function() {
		minutes = parseInt(rest_time / 60, 10)
		seconds = parseInt(rest_time % 60, 10);

		minutes = minutes < 10 ? "0" + minutes : minutes;
		seconds = seconds < 10 ? "0" + seconds : seconds;

		timer_display.textContent = minutes + ":" + seconds;
		rest_time--;
		if (rest_time < 0) {
			clearInterval(timer);
			
			expired();
		}
	}, 1000);
	var expired = function() {
		//시간만료되면 히든프레임으로 okbit 로그인건을 로그아웃시킨다.
		document.getElementById("hidden_frame").src = '/coinpay/logout.php';		
		alert('결제시간이 종료되었습니다.');
		
		$('.close-icon').trigger('click');
	}
});
