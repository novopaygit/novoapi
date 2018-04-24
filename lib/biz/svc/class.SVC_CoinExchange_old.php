<?php
/*
 *  20180403 최인석 
 *  이제안씀 전체주석처리
 */
//
/*

 if (!defined('__SVC_COIN_EXCHANGE')) define('__SVC_COIN_EXCHANGE', true); else return;
require 'class.SVC.php';
//20180321 최인석 -> 앞으로 는 class.SVC_CoinExch.php 를 사용하도록 바꾸는중이다. 
// class.CoinExch.php 를 신규로 만들어 새로적용하기위함 
// 이 클래스는 완성후 없애야됨

class SVC_CoinExchange extends SVC {
	private $exch;	

	// ***************************************************************************************** 거래소 API
	// ============================================================================= 현재 시세
	public function getCurrentQuotation($currency) {
		$this->initExchange();
		$data = $this->actionExchange($this->exch->public_ticker($currency));
		if (!$data) return false;
		return $data[0]['lastPrice'];
		//$list = $this->getCurrencyList();
	}
	// ============================================================================= 토큰 발급
	public function getTokenInfo($user_id, $user_pw, $user_otp) {
		$this->initExchange();
		$data = $this->actionExchange($this->exch->token_client($user_id, $user_pw, $user_otp));
		if (!$data) return false;
		return $data;
	}
	// ============================================================================= 사용자 정보 조회
	public function getUserInfo($token) {
		$this->initExchange();
		$data = $this->actionExchange($this->exch->payment_info($token));
		if (!$data) return false;
		return $data;
	}
	// ============================================================================= 사용자 자산 조회
	public function getUserBalance($token, $currency) {
		$this->initExchange();
		$data = $this->actionExchange($this->exch->payment_balance($token, $currency));
		if (!$data) return false;
		return $data;
	}
	// ============================================================================= 입금 주소 요청
	public function getDepositInfo($token, $currency, $amount, $price, $auto_sell=false) {
		$this->initExchange();
		$data = $this->actionExchange($this->exch->payment_deposit($token, $currency, $amount, $price, $auto_sell));
		if (!$data) return false;
		return $data;
	}
	// ============================================================================= 코인 출금 요청
	public function execPaymentWithdraw($token, $currency, $amount, $reqId, $address, $otpCode) {
		$this->initExchange();
		$data = $this->actionExchange($this->exch->payment_withdraw($token, $currency, $amount, $reqId, $address, $otpCode));
		if (!$data) return false;
		return $data;
	}


	// ***************************************************************************************** 결제화폐
	// ============================================================================= 결제화폐 목록
	public function getOnlyCurrencyList() {
		$list = $this->getCurrencyList();
		if ($list === false) return false;

		$ret = array();
		foreach ($list as $row) {
			$ret[] = $row['currency'];
		}
		return $ret;
	}
	// ============================================================================= 결제화폐 정보
	public function getCurrencyInfo($currency) {
		$currency_list = $this->getCurrencyList();
		foreach ($currency_list as $row) {
			if ($row['currency'] == $currency) return $row;
		}
		return false;
	}
	// ============================================================================= 결제화폐 정보조회
	private function getCurrencyList() {
		static $curreny_info;
		if (!is_null($curreny_info)) return $curreny_info;
		$this->initExchange();

		if (!$this->exch->pubilc_symbols()) return false;
		if ($this->exch->getSuccessCode() != $this->exch->getResCode()) return false;
		$curreny_info = $this->exch->getResData();
		return $curreny_info;
	}


	// ***************************************************************************************** 거래소 통신
	// =============================================================================
	public function getResCode() {
		return $this->exch->getResCode();
	}
	// =============================================================================
	public function getResMsg() {
		return $this->exch->getResMsg();
	}
	// ============================================================================= 초기화
	private function initExchange() {
		//$this->exch = instanceExchange();
		
	}
	// ============================================================================= 통신
	private function actionExchange($act) {
		if (!$act) return $this->addError('서버와 통신 중 오류가 발생하였습니다.');
		if ($this->exch->getSuccessCode() != $this->exch->getResCode()) {
			return $this->addError($this->exch->getResCode() .' - '. $this->exch->getResMsg());
		}
		return $this->exch->getResData();
	}
}

*/
?>