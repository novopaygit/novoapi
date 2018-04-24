<?php
if (!defined('__CLS_COINEXCH')) define('__CLS_COINEXCH', true); else return;

class CoinExch {
	//$exchange_nm -> mall Id로 변경 Mall 아이디에따라서 접속거래소가 틀리다.
	private $mall_id;
	private $module = null;

	function __construct($mall_id) {
		$this->mall_id = $mall_id;
		$this->instanceModule();
	}

	/**
	 * 최종 체결가격
	 * cis : 마지막거래정보조회 (오케이비트 API주소 GET /api/public/ticker/{currency})
	 * @param    array      $param
	 *           string     $param['currency']     암호화폐
	 *
	 * @return   array      $result
	 * 						$result['status']      코드  		: 성공시 0000
	 * 						$result['msg'] 	   	   status 메세지 	: 성공시 success
	 *                      $result['data']['timestamp']   최종 체결 시각 : timestamp
	 *                      $result['data']['datetime']    최종 체결 시간 : datetime(Y-m-d H:i:s)
	 *                      $result['data']['price']       최종 체결 가격
	 */
	public function getTicker($param) {
		return $this->module->getTicker($param);
	}

	/**
	 * 최종 체결가격 상세
	 * cis : 마지막거래정보조회상세  (오케이비트 API주소 GET /api/public/ticker/{currency})
	 * @param    array      $param
	 *           string     $param['currency']     암호화폐
	 *
	 * @return   array      $result
	 * 						$result['status']      코드  		: 성공시 0000
	 * 						$result['msg'] 	   	   status 메세지 	: 성공시 success
	 *           int        $result['data']['timestamp']   최종 체결 시각 : timestamp
	 *           datetime   $result['data']['datetime']    최종 체결 시간 : datetime(Y-m-d H:i:s)
	 *           int        $result['data']['last_price']  최종 체결 가격
	 *           int        $result['data']['bid_price']   최우선 매수호가. 매수 주문 중 가장 높은 가격
	 *           int        $result['data']['ask_price']   최우선 매도호가. 매도 주문 중 가장 낮은 가격
	 *           int        $result['data']['low_price']   (최근 24시간) 저가. 최근 24시간 동안의 체결 가격 중 가장 낮 가격
	 *           int        $result['data']['high_price']  (최근 24시간) 고가. 최근 24시간 동안의 체결 가격 중 가장 높은 가격
	 *           float      $result['data']['volume']      거래량
	 */
	public function getTickerDetail($param) {
		return $this->module->getTickerDetail($param);
	}

	/**
	 * 매도/매수 호가  미구현
	 *
	 * @param    array      $param
	 *           string     $param['currency']     암호화폐
	 *
	 * @return   array      $result
	 *           int        $result['timestamp']   최종 체결 시각 : timestamp
	 *           datetime   $result['datetime']    최종 체결 시간 : datetime(Y-m-d H:i:s)
	 *           array      $result['bid_list']    매수 목록
	 *                      $result['bid_list'][]['price']            가격
	 *                      $result['bid_list'][]['remained_volumn']  미체결잔량
	 *           array      $result['ask_list']    매도 목록
	 *                      $result['ask_list'][]['price']            가격
	 *                      $result['ask_list'][]['remained_volumn']  미체결잔량
	 */
	public function getOrderbook($param) {
		return $this->module->getOrderbook($param);
	}

	/**

	 * 체결내역 
	 * cis : 체결완료내역조회 (오케이비트 API주소 GET /api/public/histories/{currency}
	 * @param    array      $param
	 *           string     $param['currency']     암호화폐
	 *           int        $param['size']         목록수 : default 20
	 *
	 * @return   array      $result
	 * 						$result['status']      코드  		: 성공시 0000
	 * 						$result['msg'] 	   	   status 메세지 	: 성공시 success
	 *           int        $result['data'][]['timestamp'] 체결 시각 : timestamp
	 *           datetime   $result['data'][]['datetime']  체결 시간 : datetime(Y-m-d H:i:s)
	 *           string     $result['data'][][tid']        체결일련번호
	 *           string     $result['data'][][order_type'] 체결구분
	 *           int        $result['data'][][price']      체결가격
	 *           float      $result['data'][][amount']     체결수량
	 *                      $result['data'][][fee']          수수료
	 *                      $result['data'][][fee_currency'] 수수료 단위
	 */
	public function getTransactions($param) {
		return $this->module->getTransactions($param);
	}

	/**
	 * 거래소에서 사용가능한 화폐 리스트 및 화폐에대한정보 
	 * cis : 사용가능 암호화폐 symbols (오케이비트 api 주소 GET /api/public/symbols)
	 * @param    array      $param
	 *           string     $param['currency']     암호화폐 필수값아님 안넣으면 사용가능한 화폐에대한정보가 다반환됨 
	 *
	 * @return   array      $result
	 * 						$result['status']      코드  		: 성공시 0000
	 * 						$result['msg'] 	   	   status 메세지 	: 성공시 success
	 *           string     $result['data']['currency'] 가상화폐코드
	 *           float     $result['data']['max_price'] 주문최대값
	 *           float     $result['data']['min_price'] 주문최소값
	 *           float     $result['data']['max_order_day'] 1일최대출금액
	 *           float     $result['data']['max_order'] 1회최대출금액
	 *           float     $result['data']['min_order'] 1최최소출금액
	 *           float     $result['data']['fee'] 출금수수료
	 *           float     $result['data']['fee_percent'] 거래수수료(%)
	 
	 */
	public function getConstants($param=array()) {
		return $this->module->getConstants($param);
	}

	/**
	 * 토큰클라이언트  발급 email,otpcode,password 전송후 엑세스토큰 및 리프레시 토큰을 받는다. 
	 * cis : 토큰클라이언트 (오케이비트 api 주소 POST /api/auth/token/client)
	 * @param    array      $param
	 *           string     $param['email']			Email   --  필수
	 *           string     $param['password']      비밀번호 --필수
	 *           string     $param['otpcode']     	OTP코드 --필수 
	 *
	 * @return   array      $result
	 * 						$result['status']      코드  		: 성공시 0000
	 * 						$result['msg'] 	   	   status 메세지 	: 성공시 success
	 *           string     $result['data']['access_tocken'] Access Token
	 *           string     $result['data']['token_type'] 토큰유형 
	 *           string     $result['data']['refresh_token'] 주문최대값
	 *           string     $result['data']['expires_time'] 만료시간
	 *           string     $result['data']['scope']    SCOPT?
	 
	 */
	public function getClientToken($param=array()) {
		return $this->module->getClientToken($param);
	}


	/**
	 * 사용자의 자산조회  : access_token 을 전송하여 사용자의 자산정보를 받는다. 
	 * cis : 사용자의 자산조회 (오케이비트 api 주소 POST /api/payment/balance)
	 * @param    array      $param
	 *           string     $param['access_token']	access_token 필수 
	 *           string     $param['currency']      가상화폐코드 - 선택
	 
	 * @return   array      $result
	 * 						$result['status']      코드  		: 성공시 0000
	 * 						$result['msg'] 	   	   status 메세지 	: 성공시 success
	 *           string     $result['data'][]['currency'] 가상화폐코드
	 *           float     $result['data'][]['using_amt']   사용금액 (? okbit에서 사용중인금액인지 정확하지않음)
	 *           float     $result['data'][]['available_amt'] 사용가능금액
	 *           float     $result['data'][]['total_amt']  총금액  = 잔액 + 사용가능 금액
	 *           string     $result['data'][]['search_time']  조회시간	 
	 */
	public function getUserBalance($param=array()) {
		return $this->module->getUserBalance($param);
	}

	/**
	 * 사용자의 정보  : access_token 을 전송하여 사용자의 자산정보를 받는다. 
	 * cis : 사용자의 정보조회 (오케이비트 api 주소 POST /api/payment/info)
	 * @param    array      $param
	 *           string     $param['access_token']	access_token 필수 
	 
	 * @return   array      $result
	 * 						$result['status']      코드  		: 성공시 0000
	 * 						$result['msg'] 	   	   status 메세지 	: 성공시 success
	 *           string     $result['data']['email'] 이메일주소
	 *           string     $result['data']['level'] 사용자레벨
	 *           string     $result['data']['role']  권한
	 *           string     $result['data']['name'] 실명 
	 
	 */
	public function getUserInfo($param=array()) {
		return $this->module->getUserInfo($param);
	}


	/**
	 * 입금주소요청   : access_token 을 전송하여 사용자의 자산정보를 받는다. 
	 * cis : 입굼주소요청  (오케이비트 api 주소 POST /api/payment/deposit)
	 * @param    array      $param
	 *           string     $param['access_token']	access_token 필수 
	 *           string     $param['currency']	가상화폐
	 *           float     $param['amount']	deposit Request Amount
	 *           float     $param['price']		Deposit KRW Amount
	 *           bool     $param['autosell']	자동 매매여부 ? true= 1
	 
	 * @return   array      $result
	 * 						$result['status']      코드  		: 성공시 0000
	 * 						$result['msg'] 	   	   status 메세지 	: 성공시 success
	 *           string     $result['data']['reqid']   입금주소요청 요청 아이디 
	 *           string     $result['data']['currency']   가상화폐코드
	 *           string     $result['data']['address']   입금주소
	 *           string     $result['data']['amount']   입출금액 
	 *           string     $result['data']['regdt']    등록일자 timestamp?
	 *           string     $result['data']['status']   상태값(PENDING, COMPLETED, CANCEL, PROGRESS,
	 *						 								ANSWERED, USE, FAILED, EXPIRED, PLACED)

	 
	 */
	public function getDepositAddress($param=array()) {
		return $this->module->getDepositAddress($param);
	}
	

	/**
	 * 코인출금요청  : 출금처리 
	 * cis : 코인출금요청  (오케이비트 api 주소 POST /api/payment/withdraw)
	 * @param    array      $param
	 *           string     $param['access_token']	access_token 필수 
	 *           string     $param['reqid']   	입금주소요청 요청 아이디 
	 *           string     $param['currency']   가상화폐코드
	 *           string     $param['address']   입금주소
	 *           float      $param['amount']    출금할 금액
	 *           string     $param['otpcode']    OTP코드
	 * @return   array      $result
	 * 						$result['status']      코드  		: 성공시 0000
	 * 						$result['msg'] 	   	   status 메세지 	: 성공시 success
	 *           string     $result['data']['reqid']   입금주소요청 요청 아이디 
	 *           string     $result['data']['currency']   가상화폐코드
	 *           string     $result['data']['address']   입금주소
	 *           float      $result['data']['amount']   입출금액 
	 *           int   	    $result['data']['regdt']    등록일자 timestamp?
	 *           string     $result['data']['status']   상태값(PENDING, COMPLETED, CANCEL, PROGRESS,
	 *						 								ANSWERED, USE, FAILED, EXPIRED, PLACED)
	 *           string     $result['data']['category']    send,receive,all
	*/
    public function putWithdraw($param=array()) {
		return $this->module->putWithdraw($param);
	}

	/**
	 * 코인출금요청  : 출금상태조회  / 출금요청처리의 반환값이 같다.
	 * cis : 출금상태조회  (오케이비트 api 주소 POST /api/payment/withdraw/status
	 * @param    array      $param
	 *           string     $param['access_token']	access_token 필수 
	 *           string     $param['reqid']   	입금주소요청 요청 아이디 
	  * @return   array      $result
	 * 						$result['status']      코드  		: 성공시 0000
	 * 						$result['msg'] 	   	   status 메세지 	: 성공시 success
	 *           string     $result['data']['reqid']   입금주소요청 요청 아이디 
	 *           string     $result['data']['currency']   가상화폐코드
	 *           string     $result['data']['address']   입금주소
	 *           float      $result['data']['amount']   입출금액 
	 *           int   		$result['data']['regdt']    등록일자 timestamp?
	 *           string     $result['data']['status']   상태값(PENDING, COMPLETED, CANCEL, PROGRESS,
	 *						 								ANSWERED, USE, FAILED, EXPIRED, PLACED)
	 *           string     $result['data']['category']    send,receive,all
	*/
	public function getWithdrawStatus($param=array()) {
		return $this->module->getWithdrawStatus($param);
	}

	/**
	 * 출금취소요청 PENDING 중인 출금요청건에대해서만 취소가가능한듯 .
	 * cis : 출금취소요청  (오케이비트 api 주소 DELETE /api/payment/withdraw
	 * @param    array      $param
	 *           string     $param['access_token']	access_token 필수 
	 *           string     $param['reqid']   	입금주소요청 요청 아이디 
	 *           string     $param['otpcode']   	OTP코드
	 * @return   array      $result
	 * 						$result['status']      코드  		: 성공시 0000
	 * 						$result['msg'] 	   	   status 메세지 	: 성공시 success
	 *           string     $result['data']['reqid']   입금주소요청 요청 아이디 
	 *           string     $result['data']['currency']   가상화폐코드
	 *           string     $result['data']['address']   입금주소
	 *           float     $result['data']['amount']   입출금액 
	 *           int 	    $result['data']['regdt']    등록일자 timestamp?
	 *           string     $result['data']['status']   상태값(PENDING, COMPLETED, CANCEL, PROGRESS,
	 *						 								ANSWERED, USE, FAILED, EXPIRED, PLACED)
	 *           string     $result['data']['category']    send,receive,all
	 
	*/
	//payment_cancel
	public function delWithdrawStatus($param=array()) {
		return $this->module->delWithdrawStatus($param);
	}
	/**
	 * 입출금내역조회
	 * cis : 입출금내역조회   (오케이비트 api 주소 GET /api/private/transactions
	 * @param    array      $param
	 *           string     $param['access_token']	access_token 필수 
	 *           string     $param['category']   	ALL / receive / send  필수 
	 *           string     $param['currency']   	가상화폐 필수 
	 *           string     $param['page']   		페이지 
	 *           string     $param['size']   		사이즈 
	 * @return   array      $result
	 * 						$result['status']      코드  		: 성공시 0000
	 * 						$result['msg'] 	   	   status 메세지 	: 성공시 success
	 *           string     $result['data'][]['txid']   오케이비트 트랜젝션 아이디 (거래완료된건만 표기?)
	 *           string     $result['data'][]['currency']  r가상화폐코드
	 *           string     $result['data'][]['address']    입금주소
	 *           string     $result['data'][]['amount']     입출금금액
	 *           string     $result['data'][]['regdt']      처리일자 timestamp 13자리(dt)
	 *           string     $result['data'][]['status']     상태값 
	 *           string     $result['data'][]['category']   카테고리 (send/receive 송금 수금 구분 인듯)
	 *           string     $result['data'][]['reqid']   	출금요청아이디(reqID)
	 
	*/
	public function getCoinTransactions($param=array()) {
		return $this->module->getCoinTransactions($param);
	}

	// 성공코드 반환
	public function getSuccessCode() {
		return '0000';
	}

	//----------클레스 세팅용 멤버함수 ----------------------

	public function setApiInfo($api_key, $api_secret) {
		if (!$this->checkModule()) return;
		$this->setApiKey($api_key);
		$this->setApiSecret($api_secret);
	}

	public function setApiKey($api_key) {
		if (!$this->checkModule()) return;
		$this->module->setApiKey($api_key);
		
	}

	public function setApiSecret($api_secret) {
		if (!$this->checkModule()) return;
		$this->module->setApiSecret($api_secret);
	}


	/**
	 * 실행모드 설정
	 *
	 * @param    string      $mode
	 *
	 * @return   void
	 */
	function setMode($mode) {
		if (!$this->checkModule()) return;
		$this->module->setMode($mode);
	}

	/**
	 * 실행모드
	 *
	 * @return   string   $mode
	 */
	public function getMode() {
		if (!$this->checkModule()) return '';
		return $this->module->getMode();
	}

	/**
	 * 거래소 API 모듈 생성
	 *
	 */
	private function instanceModule() {
		$cfg = $this->getModuleConfig();		
		$cls_nm = 'CoinExch'. ucfirst($cfg['EXCHANGE_ID']);
		require 'coinexch/class.'. $cls_nm .'.php';
		$this->module = new $cls_nm($cfg);
	}

	/**
	 * 거래소 API 설정값
	 *
	 * @return    array    $info
	 */
	private function getModuleConfig() {
		static $cfg;
		if (is_null($cfg)) {
			$cfg = parse_ini_file('coinexch/config.CoinExch.php', true);
		}
		$info = array();
		if (isset($cfg[$this->mall_id])) $info = $cfg[$this->mall_id];
		return $info;
	}

	/**
	 * 거래소 API 모듈 생성확인
	 *
	 * @return   boolean
	 */
	private function checkModule() {
		if (is_null($this->module)) return false;
		return true;
	}

}
?>