<?php
if (!defined('__CLS_COINEXCH_OKBIT')) define('__CLS_COINEXCH_OKBIT', true); else return;
require 'class.CoinExchBase.php';
class CoinExchOkbit extends CoinExchBase{
	private $okbit;

	function __construct($cfg) {
		$client_id     = isset($cfg['CLIENT_ID'])     ? $cfg['CLIENT_ID']     : '';
		$client_secret = isset($cfg['CLIENT_SECRET']) ? $cfg['CLIENT_SECRET'] : '';
		$mode          = isset($cfg['EXEC_MODE'])     ? $cfg['EXEC_MODE']     : 'TEST';
		require 'okbit/class.OK-BIT.php';
		$this->okbit = new OkBitClient($client_id, $client_secret);
		$this->setMode($mode);
	}

	//* 최종 체결가격
	//* cis : 마지막거래정보조회 (GET /api/public/ticker/{currency})
	public function getTicker($param) {
		$currency = $this->getCurrency4Param($param);
		$result = $this->okbit->public_ticker($currency);

		if (!$result){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		//성공코드'0000'가반환되지않을경우  처리 -20180319 최인석 
		$rescode = $this->okbit->getResCode();
		$resmsg = $this->okbit->getResMsg();		
		$errcode = is_null($rescode) or $rescode ='' ? '9002' : $rescode;
		$errmsg = is_null($resmsg) ? 'Unknown Error' : $resmsg;
		if (($rescode !=='0000' and strtoupper($rescode) != strtoupper("SUCCESS")) or is_null($rescode)){

			return array(
				'status' => $rescode, //'9002',
				'msg' => $resmsg.$this->getOKBITErrorMsgKR($rescode),
				'data' => null
			);
		}			
		//성공코드'0000'가 반환되면 아래 수행 
		$list = $this->okbit->getResData();
		foreach ($list as $row) {
			if ($currency == $row['unit']) {
				return array(
					'status'	=> '0000',
					'msg'		=> 'success',
					'data'		=> array(
						'timestamp' => time(), // 최종시각 정보가 없어 현재 시각을 표시
						'datetime'  => date('Y-m-d H:i:s', time()),
						'price'     => $row['lastPrice']
					)
				);
		}
		}
		return false;
	}
	//* 최종 체결가격 상세
	//* cis : 마지막거래정보조회상세  (오케이비트 API주소 GET /api/public/ticker/{currency})
	public function getTickerDetail($param) {
		$currency = $this->getCurrency4Param($param);
		$result = $this->okbit->public_ticker($currency);
		if (!$result){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		//성공코드'0000'가반환되지않을경우  처리 -20180319 최인석 
		$rescode = $this->okbit->getResCode();
		$resmsg = $this->okbit->getResMsg();		
		$errcode = is_null($rescode) or $rescode ='' ? '9002' : $rescode;
		$errmsg = is_null($resmsg) ? 'Unknown Error' : $resmsg;
		if (($rescode !=='0000' and strtoupper($rescode) != strtoupper("SUCCESS")) or is_null($rescode)){

			return array(
				'status' => $rescode, //'9002',
				'msg' => $resmsg.$this->getOKBITErrorMsgKR($rescode),
				'data' => null
			);
		}


		//성공코드'0000'가 반환되면 아래 수행 
		$list = $this->okbit->getResData();
		foreach ($list as $row) {
			if ($currency == $row['unit']) {
				return array(
					'status'	=> '0000',
					'msg'		=> 'success',
					'data'		=> array(
						'timestamp' => time(), // 최종시각 정보가 없어 현재 시각을 표시
						'datetime'  => date('Y-m-d H:i:s', time()),
						'last_price' => $row['lastPrice'],   //
						'bid_price'  => '-',    // 최우선 매수호가. 매수 주문 중 가장 높은 가격
						'ask_price'  => '-',    // 최우선 매도호가. 매도 주문 중 가장 낮은 가격
						'low_price'  => $row['low'],    // (최근 24시간) 저가. 최근 24시간 동안의 체결 가격 중 가장 낮 가격
						'high_price' => $row['high'],   // (최근 24시간) 고가. 최근 24시간 동안의 체결 가격 중 가장 높은 가격
						'volume'     => $row['vol']  // 거래량
					)
				);
			}
		}
		return false;
	}
	//주문정보조회 미구현
	public function getOrderbook($param) {
	}

	//* 체결내역 
	//* cis : 체결완료내역조회 (오케이비트 API주소 GET /api/public/histories/{currency}

	public function getTransactions($param) {
		$currency = $this->getCurrency4Param($param);
		$size = isset($param['size']) ? intval($param['size']) : 0;
		if ($size < 1) $size = $this->getDefaultValue('transactions_size');
		$result = $this->okbit->public_history($currency, 0, $size);
		if (!$result){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		//성공코드'0000'가반환되지않을경우  처리 -20180319 최인석 
		$rescode = $this->okbit->getResCode();
		$resmsg = $this->okbit->getResMsg();		
		$errcode = is_null($rescode) or $rescode ='' ? '9002' : $rescode;
		$errmsg = is_null($resmsg) ? 'Unknown Error' : $resmsg;
		if (($rescode !=='0000' and strtoupper($rescode) != strtoupper("SUCCESS")) or is_null($rescode)){

			return array(
				'status' => $rescode, //'9002',
				'msg' => $resmsg.$this->getOKBITErrorMsgKR($rescode),
				'data' => null
			);
		}	

		//성공코드'0000'가 반환되면 아래 수행 
		$list = $this->okbit->getResData();
		$result = array();
		if (!is_array($list)) return $result;
		foreach ($list as $row) {
			$timestamp = floor($row['dt'] / 1000);

			$result[] = array(

					'timestamp'    => $timestamp,
					'datetime'     => date('Y-m-d H:i:s', $timestamp),
					'tid'          => '-',
					'order_type'   => $row['orderType'],
					'price'        => $row['price'],
					'amount'       => $row['amount'],
					'fee'          => $row['fee'],
					'fee_currency' => $row['feeCurrency']
				);
		}
		//return $result;
		//상태값과 메세지 형태 로 반환 
		return array(
			'status'=> '0000',
			'msg'	=> 'success',
			'data'	=> $result
		);
		

	}


	//* 거래소에서 사용가능한 화폐 리스트 및 화폐에대한정보 
	//* cis : 사용가능 암호화폐 symbols (오케이비트 api 주소 GET /api/public/symbols)
	public function getConstants($param) {
		$currency = $this->getCurrency4Param($param);
		$result = $this->okbit->pubilc_symbols();
		if (!$result){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 

		//성공코드'0000'가반환되지않을경우  처리 -20180319 최인석 
		$rescode = $this->okbit->getResCode();
		$resmsg = $this->okbit->getResMsg();		
		$errcode = is_null($rescode) or $rescode ='' ? '9002' : $rescode;
		$errmsg = is_null($resmsg) ? 'Unknown Error' : $resmsg;
		if (($rescode !=='0000' and strtoupper($rescode) != strtoupper("SUCCESS")) or is_null($rescode)){

			return array(
				'status' => $rescode, //'9002',
				'msg' => $resmsg.$this->getOKBITErrorMsgKR($rescode),
				'data' => null
			);
		}

		//성공코드'0000'이 반환됬을경우 아래처리
		$list = $this->okbit->getResData();

		$result = array();
		if (!is_array($list)) return $result;


		foreach ($list as $row) {
			$data = array(
					'currency' 		=> $row['currency'],  // 가상화폐코드 
					'max_price'     => '-',  // 주문 최대값
					'min_price'     => '-',  // 주문 최소값
					'max_order_day' => $row['withdrawOneDay'],  // 매수/매도 최대
					'max_order'     => $row['withdrawOneTime'],  // 매수/매도 최대
					'min_order'     => $row['minAmount'],  // 매수/매도 최소
					'fee'           => $row['txFee'],
					'fee_percent'   => $row['exFeePercent']
				);
			if ($currency) {
				if ($currency != $row['currency']) continue;
				$result = $data;
				break;
			}
			$result[$row['currency']] = $data;
		}
		//return $result;
		//상태값과 메세지 형태 로 반환 
		return array(
			'status'=> '0000',
			'msg'	=> 'success',
			'data'	=> $result
		);

	}

	//* Ok-bit 토큰 발급 email,otpcode,password 전송후 엑세스토큰 및 리프레시 토큰을 받는다. 
	//* cis : OK-BIT토큰발급 (오케이비트 api 주소 POST /api/auth/token/okbit)
	public function getClientToken($param){
		if ($param['email'] ==false or $param['password'] == false or $param['otpcode'] ==false ){
		return array(
				'status' => '9003',
				'msg' => 'Invalid Params',
				'data' => null
			);
		}

		//파라메타 값 변수에 넣기
		$email = $param['email'];
		$password = $param['password'];
		$otpcode = $param['otpcode'];


		$result = $this->okbit->token_client($email,$password,$otpcode);		
		if (!$result){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		//성공코드'0000'가반환되지않을경우  처리 -20180319 최인석 
		$rescode = $this->okbit->getResCode();
		$resmsg = $this->okbit->getResMsg();		
		$errcode = is_null($rescode) or $rescode ='' ? '9002' : $rescode;
		$errmsg = is_null($resmsg) ? 'Unknown Error' : $resmsg;
		if (($rescode !=='0000' and strtoupper($rescode) != strtoupper("SUCCESS")) or is_null($rescode)){

			return array(
				'status' => $rescode, //'9002',
				'msg' => $resmsg.$this->getOKBITErrorMsgKR($rescode),
				'data' => null
			);
		}			
		//성공코드'0000'가 반환되면 아래 수행 
		$list = $this->okbit->getResData();
		

		if (!$list){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		return array(
			'status'	=> '0000',
			'msg'		=> 'success',
			'data'		=> array(
				'access_token'  =>  $list['access_token'], //Access token
				'token_type'    =>  $list['token_type'], //Access token
				'refresh_token' =>  $list['refresh_token'], //Access token
				'expires_time'  =>  $list['expires_in'], //Access token
				'scope'         =>  $list['scope'] //Access token
				
			)
		);
	
	}


	//* 사용자의 자산조회  발급 : access_token 을 전송하여 사용자의 자산정보를 받는다. 
	//* cis : 사용자의 자산조회 (오케이비트 api 주소 POST /api/payment/balance)
	public function getUserBalance($param){
		if ($param['access_token'] ==false){
		return array(
				'status' => '9003',
				'msg' => 'Invalid Params',
				'data' => null
			);
		}
		$currency = $param['currency'];
		$token = $param['access_token'];
		//$currency = isset($param['currency']) ? intval($param['currency']) : 'ALL';
		$result = $this->okbit->payment_balance($token,$currency);
		if (!$result){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		//성공코드'0000'가반환되지않을경우  처리 -20180319 최인석 
		$rescode = $this->okbit->getResCode();
		$resmsg = $this->okbit->getResMsg();		
		$errcode = is_null($rescode) or $rescode ='' ? '9002' : $rescode;
		$errmsg = is_null($resmsg) ? 'Unknown Error' : $resmsg;
		if (($rescode !=='0000' and strtoupper($rescode) != strtoupper("SUCCESS")) or is_null($rescode)){

			return array(
				'status' => $rescode, //'9002',
				'msg' => $resmsg.$this->getOKBITErrorMsgKR($rescode),
				'data' => null
			);
		}	

		//성공코드'0000'가 반환되면 아래 수행 
		$list = $this->okbit->getResData();
		$result = array();
		if (!is_array($list)) return $result;
		foreach ($list as $row) {
			
			if (is_null($row['currency'])) {
				return array(
				'status' => '9004',
				'msg' => 'NotExistWallet!!',
				'data' => null
			);

			}
			$result[] = array(
					'currency'   => $row['currency'],
					'using_amt'        => $row['using'],
					'available_amt'       => $row['available'],
					'total_amt'          => $row['total'],					
					'search_time'     => date('Y-m-d H:i:s', time())					
				);
		}
		//return $result;
		//상태값과 메세지 형태 로 반환 
		return array(
			'status'=> '0000',
			'msg'	=> 'success',
			'data'	=> $result
		);

	
	}

	//* 사용자의 정보  : access_token 을 전송하여 사용자의 자산정보를 받는다. 
	//* cis : 사용자의 정보조회 (오케이비트 api 주소 POST /api/payment/info)
	public function getUserInfo($param){

		if ($param['access_token'] ==false  ){
		return array(
				'status' => '9003',
				'msg' => 'Invalid Params',
				'data' => null
			);
		}

		//파라메타 값 변수에 넣기
		$token = $param['access_token'];
		


		$result = $this->okbit->payment_info($token);		
		if (!$result){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		//성공코드'0000'가반환되지않을경우  처리 -20180319 최인석 
		$rescode = $this->okbit->getResCode();
		$resmsg = $this->okbit->getResMsg();		
		$errcode = is_null($rescode) or $rescode ='' ? '9002' : $rescode;
		$errmsg = is_null($resmsg) ? 'Unknown Error' : $resmsg;
		if (($rescode !=='0000' and strtoupper($rescode) != strtoupper("SUCCESS")) or is_null($rescode)){

			return array(
				'status' => $rescode, //'9002',
				'msg' => $resmsg.$this->getOKBITErrorMsgKR($rescode),
				'data' => null
			);
		}			
		//성공코드'0000'가 반환되면 아래 수행 
		$list = $this->okbit->getResData();
		

		if (!$list){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		return array(
			'status'	=> '0000',
			'msg'		=> 'success',
			'data'		=> array(
				'email' =>  $list['email'], //이메일주소
				//'level' =>  $list['level'], //사용자레벨 
				//'role'  =>  $list['role'],   //권한 
				'name'  =>  $list['name'],   //사용자이름 					
			)
		);
		
	}		


	//* 입금주소요청   : access_token 을 전송하여 사용자의 자산정보를 받는다. 
	//* cis : 입굼주소요청  (오케이비트 api 주소 POST /api/payment/deposit)
	public function getDepositAddress($param){

		if ( $param['access_token'] == false or $param['currency'] == false or $param['amount'] == false 
			or $param['price'] == false  ){
			return array(
				'status' => '9003',
				'msg' => 'Invalid Params',
				'data' => null
			);
		}

		//파라메타 값 변수에 넣기
		$token = $param['access_token'];
		$currency = $param['currency'];		
		$amount = $param['amount'];
		$price = $param['price'];
		$autoSell = $param['autosell'];

		$result = $this->okbit->payment_deposit($token, $currency, $amount, $price, $autoSell);		
		if (!$result){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		//성공코드'0000'가반환되지않을경우  처리 -20180319 최인석 
		$rescode = $this->okbit->getResCode();
		$resmsg = $this->okbit->getResMsg();		
		$errcode = is_null($rescode) or $rescode ='' ? '9002' : $rescode;
		$errmsg = is_null($resmsg) ? 'Unknown Error' : $resmsg;
		if (($rescode !=='0000' and strtoupper($rescode) != strtoupper("SUCCESS")) or is_null($rescode)){

			return array(
				'status' => $rescode, //'9002',
				'msg' => $resmsg.$this->getOKBITErrorMsgKR($rescode),
				'data' => null
			);
		}			
		//성공코드'0000'가 반환되면 아래 수행 
		$list = $this->okbit->getResData();
		

		if (!$list){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		return array(
			'status'	=> '0000',
			'msg'		=> 'success',
			'data'		=> array(
				'reqid' =>  $list['reqId'], 		// 입금요청 id 
				'currency' =>  $list['currency'],   // 가상화폐
				'address'  =>  $list['address'], 	// 입금주소  
				'amount'   =>  $list['amount'], 		// 입출금액   
				'regdt'    =>  $list['regDt'], 		// 등록일자 (timestamp?)
				'status'   =>  $list['status'], 		// 상태값(문자)  					
			)
		);
		
	}	

	//* 코인출금요청  : 출금처리 
	//* cis : 코인출금요청  (오케이비트 api 주소 POST /api/payment/withdraw)
	public function putWithdraw($param){

		if ( $param['access_token'] == false or $param['reqid'] == false or $param['currency'] == false 
			or $param['address'] == false or $param['amount'] == false or $param['otpcode'] == false  ){
			return array(
				'status' => '9003',
				'msg'  => 'Invalid Params',
				'data' => null
			);
		}
		//출금금액 값 확인 

		if (is_numeric($param['amount']) ==false){
			return array(
				'status' => '9003',
				'msg' => 'Invalid Params : Amount is not numeric : '.$param['amount'],
				'data' => null
			);	
		}
		if ((float)$param['amount'] <= 0){
			return array(
				'status' => '9003',
				'msg'    => 'Invalid Params : Amount is sub-zero! : '.$param['amount'],
				'data'   => null
			);		
		}

		
		//파라메타 값 변수에 넣기
		$token = $param['access_token'];
		$reqid = $param['reqid'];
		$currency = $param['currency'];
		$address = $param['address'];
		$amount = $param['amount'];
		$otpcode = $param['otpcode'];

		$result = $this->okbit->payment_withdraw($token, $currency, $amount, $reqid, $address, $otpcode);
		if (!$result){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		//성공코드'0000'가반환되지않을경우  처리 -20180319 최인석 
		$rescode = $this->okbit->getResCode();
		$resmsg = $this->okbit->getResMsg();		
		$errcode = is_null($rescode) or $rescode ='' ? '9002' : $rescode;
		$errmsg = is_null($resmsg) ? 'Unknown Error' : $resmsg;
		if (($rescode !=='0000' and strtoupper($rescode) != strtoupper("SUCCESS")) or is_null($rescode)){

			return array(
				'status' => $rescode, //'9002',
				'msg' => $resmsg.$this->getOKBITErrorMsgKR($rescode),
				'data' => null
			);
		}			
		//성공코드'0000'가 반환되면 아래 수행 
		$list = $this->okbit->getResData();
		

		if (!$list){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		return array(
			'status'	=> '0000',
			'msg'		=> 'success',
			'data'		=> array(
				'reqid' =>  $list['reqId'], 		// 입금요청 id 
				'currency' =>  $list['currency'],   // 가상화폐
				'address'  =>  $list['address'], 	// 입금주소  
				'amount'   =>  $list['amount'], 		// 입출금액   
				'regdt'    =>  $list['regDt'], 		// 등록일자 (timestamp?)
				'status'   =>  $list['status'], 		// 상태값(문자)  					
				'category' =>  $list['category'], 		// 상태값(문자)  					
			)
		);
		

	}

	//* 출금상태조회
	//* cis : 출금상태조회  (오케이비트 api 주소 POST /api/payment/withdraw/status)
	public function getWithdrawStatus($param){

		if ( $param['access_token'] == false or $param['reqid'] == false ){
			return array(
				'status' => '9003',
				'msg' => 'Invalid Params',
				'data' => null
			);
		}
		
		
		//파라메타 값 변수에 넣기
		$token = $param['access_token'];
		$reqid = $param['reqid'];
		
		
		$result = $this->okbit->payment_status($token, $reqid);
		if (!$result){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		//성공코드'0000'가반환되지않을경우  처리 -20180319 최인석 
		$rescode = $this->okbit->getResCode();
		$resmsg = $this->okbit->getResMsg();		
		$errcode = is_null($rescode) or $rescode ='' ? '9002' : $rescode;
		$errmsg = is_null($resmsg) ? 'Unknown Error' : $resmsg;
		if (($rescode !=='0000' and strtoupper($rescode) != strtoupper("SUCCESS")) or is_null($rescode)){

			return array(
				'status' => $rescode, //'9002',
				'msg' => $resmsg.$this->getOKBITErrorMsgKR($rescode),
				'data' => null
			);
		}			
		//성공코드'0000'가 반환되면 아래 수행 
		$list = $this->okbit->getResData();
		

		if (!$list){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		return array(
			'status'	=> '0000',
			'msg'		=> 'success',
			'data'		=> array(
				'reqid' =>  $list['reqId'], 		// 입금요청 id 
				'currency' =>  $list['currency'],   // 가상화폐
				'address'  =>  $list['address'], 	// 입금주소  
				'amount'   =>  $list['amount'], 		// 입출금액   
				'regdt'    =>  $list['regDt'], 		// 등록일자 (timestamp?)
				'status'   =>  $list['status'], 		// 상태값(문자)  					
				'category' =>  $list['category'], 		// 상태값(문자)  					
			)
		);

	}		
	public function delWithdrawStatus($param){

		if ( $param['access_token'] == false or $param['reqid'] == false or $param['otpcode'] == false ){
			return array(
				'status' => '9003',
				'msg' => 'Invalid Params',
				'data' => null
			);
		}
		
		
		//파라메타 값 변수에 넣기
		$token = $param['access_token'];
		$reqid = $param['reqid'];
		$otpcode = $param['otpcode'];
		
		
		$result = $this->okbit->payment_cancel($token, $reqid,$otpcode);
		if (!$result){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		//성공코드'0000'가반환되지않을경우  처리 -20180319 최인석 
		$rescode = $this->okbit->getResCode();
		$resmsg = $this->okbit->getResMsg();		
		$errcode = is_null($rescode) or $rescode ='' ? '9002' : $rescode;
		$errmsg = is_null($resmsg) ? 'Unknown Error' : $resmsg;
		if (($rescode !=='0000' and strtoupper($rescode) != strtoupper("SUCCESS")) or is_null($rescode)){

			return array(
				'status' => $rescode, //'9002',
				'msg' => $resmsg.$this->getOKBITErrorMsgKR($rescode),
				'data' => null
			);
		}

		//성공코드'0000'가 반환되면 아래 수행 
		$list = $this->okbit->getResData();
		

		if (!$list){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		return array(
			'status'	=> '0000',
			'msg'		=> 'success',
			'data'		=> array(
				'reqid' =>  $list['reqId'], 		// 입금요청 id 
				'currency' =>  $list['currency'],   // 가상화폐
				'address'  =>  $list['address'], 	// 입금주소  
				'amount'   =>  $list['amount'], 		// 입출금액   
				'regdt'    =>  $list['regDt'], 		// 등록일자 (timestamp?)
				'status'   =>  $list['status'], 		// 상태값(문자)  					
				'category' =>  $list['category'], 		// 상태값(문자)  					
			)
		);

	}

	//* 입출금내역조회
	//* cis : 입출금내역조회   (오케이비트 api 주소 GET /api/private/transactions
	public function getCoinTransactions($param){
		if ($param['access_token'] ==false){
		return array(
				'status' => '9003',
				'msg' => 'Invalid Params',
				'data' => null
			);
		}
		
		$token = $param['access_token'];
		$category = $param['category'];
		$currency = $param['currency'];
		$page = $param['page'];
		$size = $param['size'];
		
		//private_transactions($token, $currency, $category='receive', $page=0, $size=20) {
		$result = $this->okbit->private_transactions($token,$currency,$category,$page,$size);
		if (!$result){
			return array(
				'status' => '9001',
				'msg' => 'Connectiong Error',
				'data' => null
			);
		} 
		
		//성공코드'0000'가반환되지않을경우  처리 -20180319 최인석 
		$rescode = $this->okbit->getResCode();
		$resmsg = $this->okbit->getResMsg();		
		$errcode = is_null($rescode) or $rescode ='' ? '9002' : $rescode;
		$errmsg = is_null($resmsg) ? 'Unknown Error' : $resmsg;
		if (($rescode !=='0000' and strtoupper($rescode) != strtoupper("SUCCESS")) or is_null($rescode)){

			return array(
				'status' => $rescode, //'9002',
				'msg' => $resmsg.$this->getOKBITErrorMsgKR($rescode),
				'data' => null
			);
		}	

		//성공코드'0000'가 반환되면 아래 수행 
		$list = $this->okbit->getResData();
		$result = array();
		if (!is_array($list)) return $result;
		foreach ($list as $row) {
			

			$result[] = array(
					'txid'  	 => $row['txId'],
					'currency'   => $row['currency'],
					'address'    => $row['address'],
					'amount'   	 => $row['amount'],
					'regdt'   	 => $row['dt'],
					'status'   	 => $row['status'],
					'category'   => $row['category'],
					'reqid'   	 => $row['reqId']
					
				);
		}
		//return $result;
		//상태값과 메세지 형태 로 반환 
		return array(
			'status'=> '0000',
			'msg'	=> 'success',
			'data'	=> $result
		);

	
	}


	protected function getCurrencyCode($currency) {
		$currency = strtolower($currency);
		switch ($currency) {
			case 'btc'  : return 'BTC';
			case 'eth'  : return 'ETH';
			case 'xrp'  : return 'XRP';
			case 'xmr'  : return 'XMR';
			case 'ltc'  : return 'LTC';
			case 'dash' : return 'DASH';
			default : return false;
		}
	}

	private function getOKBITErrorMsgKR($rescode) {
		$rescode = strtolower($rescode);
		switch ($rescode) {
			case '1000' : return '(실패입니다.)';
			case '1001'	: return '(알수없는 오류입니다.)';
			case '1002'	: return '(잘못된 요청입니다.)';
			case '1003'	: return '(상수값이 없습니다.)';
			case '4001'	: return '(사용자가 없습니다.)';
			case '4002'	: return '(사용자 필수 설정값이 없습니다.)';
			case '4003'	: return '(사용할 수 없는 키 입니다.)';
			case '4004'	: return '(가용한 코인이 0보다 적습니다.)';
			case '4005'	: return '(지갑이 존재하지 않습니다.)';
			case '4006'	: return '(지갑이 이미 있습니다.)';
			case '4007'	: return '(사용할 수 없는 주문타입 입니다.)';
			case '4008'	: return '(잔액이 부족함니다.)';
			case '4009'	: return '(사용할 수 없는 인증코드 입니다.)';
			case '4010'	: return '(사용할 수 없는 이메일 입니다.)';
			case '4011'	: return '(주문 취소 실패 입니다.)';
			case '4012'	: return '(사용할 수 없는 주문타입 입니다.)';
			case '4013'	: return '(주문이 존재하지 않습니다.)';
			case '4014'	: return '(진행중인 주문서가 없습니다.)';
			case '4015'	: return '(최소 수량보다 적습니다.)';
			case '4016'	: return '(지원되지 않습니다.)';
			case '4017'	: return '(사용자가 현재 블록되어 있습니다.)';
			case '4018'	: return '(관리자 지갑의 잔액이 0보다 적습니다.)';
			case '4019'	: return '(지갑의 잠금 해제 실패입니다.)';
			case '4020'	: return '(내부 송금 지갑은 허용되지 않습니다.)';
			case '4021'	: return '(최소출금액 보다 적습니다.)';
			case '4022'	: return '(사용자가 보유중인 지갑이 아님니다.)';
			case '4023'	: return '(출금 목록에 없습니다.)';
			case '4024'	: return '(원화 입금 요청만 가능합니다.)';
			case '4025'	: return '(원화 출금 요청만 가능합니다.)';
			case '4026'	: return '(잘못된 패스워드 입니다.)';
			case '4027'	: return '(이미 완료된 건 입니다.)';
			case '4028'	: return '(이미 전송된 건 입니다.)';
			case '4029'	: return '(진행중인 출금건이 없습니다.)';
			case '4041'	: return '(OTP 코드가 유효하지 않습니다.)';			
			case '5003'	: return '(잘못된 클라이언트 요청입니다.)';
			default : return ' ';
		}
	}


	function setMode($mode) {
		parent::setMode($mode);
		$mode = $this->getMode();
		$this->okbit->setMode($mode);
	}
}

?>