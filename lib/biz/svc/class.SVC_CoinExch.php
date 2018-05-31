<?php
if (!defined('__SVC_COIN_EXCH')) define('__SVC_COIN_EXCH', true); else return;
require_once 'class.SVC.php';
//20180321 최인석 새로운 Coin관련 처리 클래스 
// class.SVC_CoinExchange.php는 사용하지않을 예정 
// 현재 테스트중 

class SVC_CoinExch extends SVC {	
	private $mall_id;
	private $coinExch;

	function __construct($mall_id) {
		$this->mall_id = $mall_id;		
	}

	// ***************************************************************************************** 거래소 API
	// ============================================================================= 현재 시세
	public function getCurrentQuotation($currency) {		
		$this->initExchange();
		$params = array(); //이함수는 param 값이없다.	
		$params['currency']	 = $currency;
		$responseData = $this->actionExchangeSearch($this->coinExch->getTickerDetail($params));
		if (!$responseData) return false;    
		return $responseData['last_price'];

	}
	// ============================================================================= 토큰 발급
	public function getTokenInfo($user_id, $user_pw, $user_otp) {
		$this->initExchange();
		$params = array(); //이함수는 param 값이없다.	
		$params['email']	 = $user_id;
		$params['password']	 = $user_pw;
		$params['otpcode']	 = $user_otp;
		$responseData = $this->actionExchangeProc($this->coinExch->getClientToken($params));
		if (!$responseData) return false;    
		return $responseData;

	}
	// ============================================================================= 사용자 정보 조회
	public function getUserInfo($token) {
		$this->initExchange();
		$params = array(); //이함수는 param 값이없다.	
		$params['access_token']	 = $token;		
		$responseData = $this->actionExchangeSearch($this->coinExch->getUserInfo($params));
		if (!$responseData) return false;    
		return $responseData;

	}
	// ============================================================================= 사용자 자산 조회
	public function getUserBalance($token, $currency) {		
		$this->initExchange();
		$params = array(); //이함수는 param 값이없다.	
		$params['access_token']	 = $token;
		$params['currency']	 = $currency;
		$responseData = $this->actionExchangeSearch($this->coinExch->getUserBalance($params));
		if (!$responseData) return false;    
		return $responseData;
	}
	// ============================================================================= 입금 주소 요청
	public function getDepositInfo($token, $currency, $amount, $price, $auto_sell=false) {		
		$this->initExchange();
		$params = array(); 
		$params['access_token'] = $token;
		$params['currency']	   = $currency;
		$params['amount']       = $amount;
		$params['price']        = $price;
		$params['autosell']     = $auto_sell;

		$responseData = $this->actionExchangeProc($this->coinExch->getDepositAddress($params));
		if (!$responseData) return false;    
		return $responseData;

	}
	// ============================================================================= 코인 출금 요청
	public function execPaymentWithdraw($token, $currency, $amount, $reqId, $address, $otpCode) {
		
		$this->initExchange();
		$params = array(); 
		$params['access_token'] = $token;
		$params['currency']	   = $currency;
		$params['amount']       = $amount;
		$params['reqid']        = $reqId;
		$params['address']     = $address;
		$params['otpcode']     = $otpCode;

		$responseData = $this->actionExchangeProc($this->coinExch->putWithdraw($params));
		if (!$responseData) return false;    
		return $responseData;

	}


	// ***************************************************************************************** 결제화폐
	// ============================================================================= 결제화폐 목록
	public function getOnlyCurrencyList() {
		// novomall 에서 가상화폐목록을 가져갈때 사용한다.
		$this->initExchange();

		$params = array(); //이함수는 param 값이없다.		
		$responseData = $this->actionExchangeSearch($this->coinExch->getConstants($params));	
		if (!$responseData) return false;    
	    $currencylist = array();
	    foreach ($responseData as $row) {
	    	$currencylist[] = $row['currency'];    	
	    }
	    return $currencylist;

		
	}
	// ============================================================================= 결제화폐 정보
	public function getCurrencyInfo($currency) {		
		$this->initExchange();
		$params = array(); //이함수는 param 값이없다.	
		$params['currency']	 = $currency;
		$responseData = $this->actionExchangeSearch($this->coinExch->getConstants($params));
		if (!$responseData) return false;    
		return $responseData;
		
	}
	// ============================================================================= 결제화폐 정보조회
	private function getCurrencyList() {
		//아직까진 필요없어 안만듬 
		/*static $curreny_info;
		if (!is_null($curreny_info)) return $curreny_info;
		$this->initExchange();

		if (!$this->exch->pubilc_symbols()) return false;
		if ($this->exch->getSuccessCode() != $this->exch->getResCode()) return false;
		$curreny_info = $this->exch->getResData();
		return $curreny_info;*/
	}


	// ***************************************************************************************** 거래소 통신
	// =============================================================================
	public function getResCode() {
		//return $this->exch->getResCode();
	}
	// =============================================================================
	public function getResMsg() {
		//return $this->exch->getResMsg();
	}
	// ============================================================================= 초기화
	private function initExchange() {		
		$this->coinExch = instanceCoinExch($this->mall_id);
	}
	// ============================================================================= 통신
	//조회함수의 경우 data 만 반환 
	private function actionExchangeSearch($act) {
		
		if (!$act) return $this->addError('서버와 통신 중 오류가 발생하였습니다.');		
		if (is_null($act['status'])) {
			//return 'Not Found';;
			return $this->addError('파라메타값이 유효하지않습니다.');
		}
		if ($this->coinExch->getSuccessCode() != $act['status']  ) {		
			return $this->addError($act['status'].' - '. $act['msg']);
		}
		return $act['data'];
	}

	//조회함수가아니고 처리함수이겨우 상태값을 다 반환하여 처리폼에서 상태값기준으로 처리할수있도록하자 
	private function actionExchangeProc($act) {
		
		if (!$act) return $this->addError('서버와 통신 중 오류가 발생하였습니다.');				
		return $act;
	}

}
?>