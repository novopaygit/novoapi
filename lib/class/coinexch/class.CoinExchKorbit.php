<?php
if (!defined('__CLS_COINEXCH_KORBIT')) define('__CLS_COINEXCH_KORBIT', true); else return;
require 'class.CoinExchBase.php';
class CoinExchKorbit extends CoinExchBase {
	private $korbit;

	function __construct($cfg) {
		$api_key    = isset($cfg['API_KEY'])    ? $cfg['API_KEY']    : '';
		$api_secret = isset($cfg['API_SECRET']) ? $cfg['API_SECRET'] : '';
		//$mode       = isset($cfg['EXEC_MODE'])     ? $cfg['EXEC_MODE']     : 'TEST';
		require 'korbit/Korbit.php';
		$this->korbit = new KorbitAPI($api_key, $api_secret);
		//$this->setMode($mode);
	}

	/**
	 * 최종 체결가격
	 *
	 * request data
	 *     currency_pair : 비트코인 거래 기준으로 필드값을 가져온다. default = btc_krw
	 * response data
	 *     timestamp → timestamp : 최종 체결 시각
	 *     last      → price     : 최종 체결 가격
	 */
	public function getTicker($param) {
		$params = array(
				'currency_pair' => $this->getCurrency4Param($param)
			);
		$res = $this->korbit->getTicker($params);
		if (empty($res)) {
			// TODO : error 처리
			return false;
		}
		$timestamp = floor($res['timestamp'] / 1000);
		$result = array(
				'timestamp' => $timestamp,
				'datetime'  => date('Y-m-d H:i:s', $timestamp),
				'price'     => $res['last']
			);
		return $result;
	}

	public function getTickerDetail($param) {
		$params = array(
				'currency_pair' => $this->getCurrency4Param($param)
			);
		$res = $this->korbit->getDetailedTicker($params);
		if (empty($res)) {
			// TODO : error 처리
			return false;
		}
		$timestamp = floor($res['timestamp'] / 1000);
		$result = array(
				'timestamp'  => $timestamp,
				'datetime'   => date('Y-m-d H:i:s', $timestamp),
				'last_price' => $res['last'],   //
				'bid_price'  => $res['bid'],    // 최우선 매수호가. 매수 주문 중 가장 높은 가격
				'ask_price'  => $res['ask'],    // 최우선 매도호가. 매도 주문 중 가장 낮은 가격
				'low_price'  => $res['low'],    // (최근 24시간) 저가. 최근 24시간 동안의 체결 가격 중 가장 낮 가격
				'high_price' => $res['high'],   // (최근 24시간) 고가. 최근 24시간 동안의 체결 가격 중 가장 높은 가격
				'volume'     => $res['volume']  // 거래량
				// 'change_price'   => $res['change']
				// 'change_percent' => $res['changePercent']
			);
		return $result;
	}

	public function getOrderbook($param) {
		$params = array(
				'currency_pair' => $this->getCurrency4Param($param)
			);
		$res = $this->korbit->getOrderbook($params);
		if (empty($res)) {
			// TODO : error 처리
			return false;
		}
		$bids = $asks = array();
		if (is_array($res['bids'])) { // 매수
			foreach ($res['bids'] as $row) {
				$bids[] = array('price' => $row[0], 'remained_volumn' => $row[1]);
			}
		}
		if (is_array($res['asks'])) { // 매도
			foreach ($res['asks'] as $row) {
				$asks[] = array('price' => $row[0], 'remained_volumn' => $row[1]);
			}
		}
		$timestamp = floor($res['timestamp'] / 1000);
		$result = array(
				'timestamp' => $timestamp,
				'datetime'  => date('Y-m-d H:i:s', $timestamp),
				'bid_list'  => $bids,
				'ask_list'  => $asks,
			);
		return $result;
	}

	public function getTransactions($param) {
		$size = isset($param['size']) ? intval($param['size']) : 0;
		if ($size < 1) $size = $this->getDefaultValue('transactions_size');
		$params = array(
				'currency_pair' => $this->getCurrency4Param($param)
			);
		$res = $this->korbit->getTransactions($params);
		if (empty($res)) {
			// TODO : error 처리
			return false;
		}
		$result = array();
		if (is_array($res)) {
			foreach ($res as $row) {
				$timestamp = floor($row['timestamp'] / 1000);
				$result[] = array(
						'timestamp'    => $timestamp,
						'datetime'     => date('Y-m-d H:i:s', $timestamp),
						'tid'          => $row['tid'],
						'order_type'   => '-',
						'price'        => $row['price'],
						'amount'       => $row['amount'],
						'fee'          => '-',
						'fee_currency' => '-'
					);
				if (count($result) >= $size) break;
			}
		}
		return $result;
	}

	public function getConstants($param) {
		$currency = $this->getCurrency4Param($param);
		$res = $this->korbit->getConstants();
	}

	protected function getCurrencyCode($currency) {
		$currency = strtolower($currency);
		switch ($currency) {
			case 'btc'  : return 'btc_krw'; // 비트코인
			case 'etc'  : return 'etc_krw'; // 이더리움 클래식
			case 'eth'  : return 'eth_krw'; // 이더리움
			case 'xrp'  : return 'xrp_krw'; // 리플
			case 'bch'  : return 'bch_krw'; // 비트코인 캐시
			default : return false;
		}
	}

	function setMode($mode) {
		parent::setMode($mode);
		$mode = $this->getMode();
		$this->korbit->setMode($mode);
	}
}
?>