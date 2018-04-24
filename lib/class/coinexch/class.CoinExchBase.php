<?php
if (!defined('__CLS_COINEXCH_BASE')) define('__CLS_COINEXCH_BASE', true); else return;

abstract class CoinExchBase {
	protected $api_key;
	protected $api_secret;
	private $mode = 'TEST';

	function __construct($info) {
	}

	/**
	 * 최종 체결가격 : 추상메서드 - CoinExch 클래스 참조
	 */
	abstract public function getTicker($param);

	/**
	 * 최종 체결가격 상세 : 추상메서드 - CoinExch 클래스 참조
	 */
	abstract public function getTickerDetail($param);

	/**
	 * 매도/매수 호가 : 추상메서드 - CoinExch 클래스 참조
	 */
	abstract public function getOrderbook($param);

	/**
	 * 체결내역 : 추상메서드 - CoinExch 클래스 참조
	 */
	abstract public function getTransactions($param);

	/**
	 * 거래서 사용가능한 화폐리스트 및  화폐정보  : 추상메서드 - CoinExch 클래스 참조
	 */
	abstract public function getConstants($param);

	/**
	 * 토큰클라이언트 발급  : 추상메서드 - CoinExch 클래스 참조
	 */
	abstract public function getClientToken($param);


	/**
	 * 사용자 자산정보  : 추상메서드 - CoinExch 클래스 참조
	 */
	abstract public function getUserBalance($param);

	/**
	 * 사용자 정보  : 추상메서드 - CoinExch 클래스 참조
	 */
	abstract public function getUserInfo($param);

	/**
	 * 입금주소 요청 : 추상메서드 - CoinExch 클래스 참조
	 */
	abstract public function getDepositAddress($param);

	/**
	 * 코인출금요청 : 추상메서드 - CoinExch 클래스 참조
	 */
	abstract public function putWithdraw($param);

	/**
	 * 코인출금상태조회 : 추상메서드 - CoinExch 클래스 참조
	 */
	abstract public function getWithdrawStatus($param);

	/**
	 * 출금취소요청 : 추상메서드 - CoinExch 클래스 참조
	 */
	abstract public function delWithdrawStatus($param);

	/**
	 * 입출금내역조회 : 추상메서드 - CoinExch 클래스 참조
	 */
	abstract public function getCoinTransactions($param);



	
	

	



	public function setApiInfo($api_key, $api_secret) {
		$this->setApiKey($api_key);
		$this->setApiSecret($api_secret);
	}

	public function setApiKey($api_key) {
		$this->api_key = $api_key;
	}

	public function setApiSecret($api_secret) {
		$this->api_secret = $api_secret;
	}


	/**
	 * 실행모드 설정
	 *
	 * @param    string      $mode
	 *
	 * @return   object      $this
	 */
	function setMode($mode) {
		$mode = strtoupper($mode);
		if ($mode != 'REAL') $mode = 'TEST';
		$this->mode = $mode;
		return $this;
	}

	/**
	 * 실행모드
	 *
	 * @return   string   $mode
	 */
	public function getMode() {
		return $this->mode;
	}

	protected function getDefaultValue($key) {
		static $cfg;
		if (is_null($cfg)) {
			$cfg = array(
					'transactions_size' => 20
				);
		}
		if (isset($cfg[$key])) return $cfg[$key];
		return false;
	}

	protected function getCurrency4Param($param) {
		return $this->getCurrencyCode(isset($param['currency']) ? $param['currency'] : '');
	}
	protected function getCurrencyCode($currency) {
		return $currency;
	}
}
?>