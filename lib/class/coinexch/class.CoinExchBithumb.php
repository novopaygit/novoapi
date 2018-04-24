<?php
if (!defined('__CLS_COINEXCH_BITHUMB')) define('__CLS_COINEXCH_BITHUMB', true); else return;
require 'class.CoinExchBase.php';
class CoinExchBithumb extends CoinExchBase {
	private $bithumb;

	function __construct($cfg) {
		$api_key    = isset($cfg['CONNECT_KEY']) ? $cfg['CONNECT_KEY'] : '';
		$api_secret = isset($cfg['SECRET_KEY'])  ? $cfg['SECRET_KEY']  : '';
		//$mode       = isset($cfg['EXEC_MODE'])     ? $cfg['EXEC_MODE']     : 'TEST';
		require 'bithumb/xcoin_api_client.php';
		$this->bithumb = new XCoinAPI($api_key, $api_secret);
		//$this->setMode($mode);
	}

	/**
	 * 최종 체결가격
	 *
	 * end point : /public/ticker/{currency}
	 * response data
	 *     opening_price              : 최근 24시간 내 시작 거래금액
	 *     closing_price → price     : 최근 24시간 내 마지막 거래금액
	 *     min_price                  : 최근 24시간 내 최저 거래금액
	 *     max_price                  : 최근 24시간 내 최고 거래금액
	 *     average_price              : 최근 24시간 내 평균 거래금액
	 *     units_traded               : 최근 24시간 내 Currency 거래량
	 *     volume_1day                : 최근 1일간 Currency 거래량
	 *     volume_7day                : 최근 7일간 Currency 거래량
	 *     buy_price                  : 거래 대기건 최고 구매가
	 *     sell_price                 : 거래 대기건 최소 판매가
	 *     date          → timestamp : 현재 시간 Timestamp
	 */
	public function getTicker($param) {
		$currency = $this->getCurrency4Param($param);
		$params = null;
		$result = $this->bithumb->xcoinApiCall('/public/ticker/'. $currency, $params);
		if ($result['status'] != '0000') return false;
		$data = $result['data'];
		$timestamp = floor($data['date'] / 1000);
		return array(
				'timestamp' => $timestamp,
				'datetime'  => date('Y-m-d H:i:s', $timestamp),
				'price'     => $data['closing_price']
			);
	}

	public function getTickerDetail($param) {
	}

	public function getOrderbook($param) {
	}

	public function getTransactions($param) {
	}

	public function getConstants($param) {
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
			case 'etc'  : return 'ETC';
			case 'bch'  : return 'BCH';
			case 'zec'  : return 'ZEC';
			case 'qtum' : return 'QTUM';
			case 'btg'  : return 'BTG';
			case 'eos'  : return 'EOS';
			default : return false;
		}
	}
}