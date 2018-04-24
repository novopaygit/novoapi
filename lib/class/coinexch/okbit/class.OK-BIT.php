<?php
require 'config.OK-BIT.php';
class OkBitClient {
	//private $res
	private $client_id;
	private $client_secret;
	private $mode = 'TEST';
	private $curl;
	private $endpoint;
	private $errno;
	private $error;
	private $http_code;
	private $req_headers;
	private $req_data;
	private $req_json;
	private $response;
	private $res_headers;
	private $res_body;
	private $res_data;
	private $token;
	private $is_encrypt;
	private $is_decrypt;
	private $data_key = '';
	private $data = array();

	// ***************************************************************************************************** Construct
	// =========================================================================================
	function __construct($client_id, $client_secret) {
		$this->setClientID($client_id);
		$this->setClientSecret($client_secret);
	}

	public function setClientID($client_id) {
		$this->client_id     = $client_id;
	}
	public function setClientSecret($client_secret) {
		$this->client_secret = $client_secret;
	}
	public function setMode($mode) {
		$this->mode = $mode;
	}

	// ***************************************************************************************************** Action
	// ========================================================================================= Crypto
	// -----------------------------------------------------------------------------
	public function crypto_key($token, $data_key='') {
		$this->initAction('/crypto/key', $data_key);
		$this->init_post(array(
				'clientId'     => $this->client_id,
				'clientSecret' => $this->encrypt($this->client_secret)
			), $token);
		if (!$this->execute()) return false;

		if ($data_key) {
			$ret_data = $this->data[$data_key];
			if ($ret_data['res_data']['code'] != 'SUCCESS') return false;
			$ret_data['res_data']['code'] = '0000';
			$data = $ret_data['res_data']['data'];
			$json = json_decode($this->decrypt($data), true);
			$this->data[$data_key]['res_data']['data'] = $json;
			$this->data[$data_key]['res_body'] = json_encode($this->data[$data_key]['res_data']);
		} else {
			if ($this->res_data['code'] != 'SUCCESS') return false;
			$this->res_data['code'] = '0000';
			$data = $this->res_data['data'];
			$json = json_decode($this->decrypt($data), true);
			$this->res_data['data'] = $json;
			$this->res_body = json_encode($this->res_data);
		}
		return true;
	}
	// ========================================================================================= Private
	// -----------------------------------------------------------------------------
	public function private_address($token, $currency) {
		$this->initAction('/private/address?currency='. $currency);
		$this->init_get($token);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function private_addr_confirm($token, $address) {
		$this->initAction('/private/address/'. $address);
		$this->init_get($token);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function private_balance($token, $currency) {
		$this->initAction('/private/balance?currency='. $currency);
		$this->init_get($token);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function private_info($token) {
		$this->initAction('/private/info');
		$this->init_get($token);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function private_order_progress($token, $currency, $page=0, $size=20) {
		$this->initAction('/private/order'. '?currency='. $currency .'&page='. $page .'&size='. $size);
		$this->init_get($token);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function private_order_request($token, $currency, $amount, $price, $orderType, $otpCode) {
		// orderType : BUY, SELL
		$this->initAction('/private/order');
		$this->init_post(array(
				'currency'  => $currency,
				'orderType' => $orderType,
				'amount'    => $amount,
				'price'     => $price,
				'otpCode'   => $otpCode
			), $token);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function private_order_cancel($token, $currency, $orderId, $otpCode) {
		$this->initAction('/private/order');
		$this->init_delete(array(
				'currency' => $currency,
				'orderId'  => $orderId,
				'otpCode'  => $otpCode
			), $token);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function private_order_completed($token, $currency, $page=0, $size=20) {
		$this->initAction('/private/orders/completed'. '?currency='. $currency .'&page='. $page .'&size='. $size);
		$this->init_get($token);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function private_transactions($token, $currency, $category='receive', $page=0, $size=20) {
		// category = receive, send, ALL
		$this->initAction('/private/transactions/'. $currency .'?category='. $category .'&page='. $page .'&size='. $size);
		$this->init_get($token);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function private_wallet_create($token, $currency, $otpCode) {
		$this->initAction('/private/wallet');
		$this->init_post(array(
				'currency' => $currency,
				'otpCode'  => $otpCode
			), $token);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function private_wallet_deposit($token, $currency, $amount, $price, $autoSell) {
		$this->initAction('/private/wallet/deposit');
		$this->init_post(array(
				'currency' => $currency,
				'amount'   => $amount,
				'price'    => $price,
				'autoSell' => $autoSell
			), $token);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function private_wallet_withdraw($token, $reqId, $address, $currency, $amount, $otpCode) {
		$this->initAction('/private/wallet/withdraw');
		$this->init_post(array(
				'reqId'    => $reqId,
				'address'  => $address,
				'currency' => $currency,
				'amount'   => $amount,
				'otpCode'  => $otpCode
			), $token);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function private_wallet_cancel($token, $reqId, $otpCode) {
		$this->initAction('/private/wallet/withdraw');
		$this->init_delete(array(
				'reqId'   => $reqId,
				'otpCode' => $otpCode
			), $token);
		return $this->execute();
	}
	// ========================================================================================= Payment
	// -----------------------------------------------------------------------------
	public function payment_balance($token, $currency) {
		$this->initAction('/payment/balance?currency='. $currency);
		$this->init_get($token);
		$this->set_decrypt();
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function payment_deposit($token, $currency, $amount, $price, $autoSell) {
		$this->initAction('/payment/deposit');
		$this->set_encrypt();
		$this->init_post(array(
				'amount'   => $amount,
				'autoSell' => $autoSell,
				'currency' => $currency,
				'price'    => $price
			), $token);
		$this->set_decrypt();
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function payment_info($token) {
		$this->initAction('/payment/info');
		$this->init_get($token);
		$this->set_decrypt();
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function payment_withdraw($token, $currency, $amount, $reqId, $address, $otpCode) {
		$this->initAction('/payment/withdraw');
		$this->set_encrypt();
		$this->init_post(array(
				'address'  => $address,
				'amount'   => $amount,
				'otpCode'  => $otpCode,
				'currency' => $currency,
				'reqId'    => $reqId
			), $token);
		$this->set_decrypt();
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function payment_cancel($token, $reqId, $otpCode) {
		$this->initAction('/payment/withdraw');
		$this->set_encrypt();
		$this->init_delete(array(
				'reqId'   => $reqId,
				'otpCode' => $otpCode
			), $token);
		$this->set_decrypt();
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function payment_status($token, $reqId) {
		$this->initAction('/payment/withdraw/status');
		$this->set_encrypt();
		$this->init_post(array(
				'reqId'    => $reqId
			), $token);
		$this->set_decrypt();
		return $this->execute();
	}
	// ========================================================================================= Client
	// -----------------------------------------------------------------------------
	public function client_get($token) {
		$this->initAction('/client/get');
		$this->init_get($token);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function client_del($token) {
		$this->initAction('/client/del');
		$this->init_post($token);
		return $this->execute();
	}
	// ========================================================================================= Token
	// -----------------------------------------------------------------------------
	public function token_refresh($email, $passwd, $otp, $refresh_token) {
		$this->initAction('/auth/token/refresh');
		$this->init_post(array(
				'clientId'     => $this->client_id,
				'clientSecret' => $this->client_secret,
				'email'    => $email,
				'otpCode'  => $otp,
				'password' => $passwd,
				'refresh_token' => $refresh_token
			));
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function token_client($email, $passwd, $otp) {
		$this->initAction('/auth/token/client');
		$this->init_post(array(
				'clientId'     => $this->client_id,
				'clientSecret' => $this->client_secret,
				'email'    => $email,
				'otpCode'  => $otp,
				'password' => $passwd
			));
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function token_okbit($email, $passwd, $otp) {
		$this->initAction('/auth/token/okbit');
		$this->init_post(array(
				'email'    => $email,
				'otpCode'  => $otp,
				'password' => $passwd
			));
		return $this->execute();
	}
	// ========================================================================================= Public
	// -----------------------------------------------------------------------------
	public function public_client($data) {
		$this->initAction('/public/client');
		$this->init_post($data);
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function pubilc_symbols() {
		$this->initAction('/public/symbols');
		$this->init_get();
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function public_history($currency='BTC', $page=0, $size=20) {
		$this->initAction('/public/histories/'. $currency .'?page='. $page .'&size='. $size);
		$this->init_get();
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function public_orders($currency='BTC', $page=0, $size=20) {
		$this->initAction('/public/orders/'. $currency .'?page='. $page .'&size='. $size);
		$this->init_get();
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function public_ticker($currency='BTC') {
		$this->initAction('/public/ticker/'. $currency);
		$this->init_get();
		return $this->execute();
	}
	// -----------------------------------------------------------------------------
	public function public_exchange() {
		$this->initAction('/public/exchange/ticker');
		$this->init_get();
		return $this->execute();
	}

	// ***************************************************************************************************** Private Function
	// ========================================================================================= Init Action
	// -----------------------------------------------------------------------------
	private function initAction($path, $data_key='') {
		if ($data_key == '') {
			$this->init_action($path);
		} else {
			$this->init_action_key($path, $data_key);
		}
	}
	// -----------------------------------------------------------------------------
	private function init_action($path) {
		$this->endpoint    = $this->getEndPoint($path);
		$this->token       = false;
		$this->is_encrypt  = false;
		$this->is_decrypt  = false;
		$this->errno       = '';
		$this->error       = '';
		$this->http_code   = '';
		$this->req_headers = '';
		$this->req_data    = array();
		$this->req_json    = '';
		$this->response    = '';
		$this->res_headers = '';
		$this->res_body    = '';
		$this->res_data    = array();
		$this->data_key    = '';
	}
	// -----------------------------------------------------------------------------
	private function init_action_key($path, $data_key) {
		$this->data_key = $data_key;
		$this->data[$data_key] = array(
				'endpoint'    => $this->getEndPoint($path),
				'token'       => false,
				'is_encrypt'  => false,
				'is_decrypt'  => false,
				'errno'       => '',
				'error'       => '',
				'http_code'   => '',
				'req_headers' => '',
				'req_data'    => array(),
				'req_json'    => '',
				'response'    => '',
				'res_headers' => '',
				'res_body'    => '',
				'res_data'    => array(),
				'curl'        => null
			);
	}
	// ========================================================================================= Init Method Param
	// -----------------------------------------------------------------------------
	private function init_get($token=false) {
		$this->init_curl('get', $token);
	}
	// -----------------------------------------------------------------------------
	private function init_post($data, $token=false) {
		$this->init_curl('post', $token);
		if ($this->data_key) {
			$this->data[$this->data_key]['req_data'] = $data;
			$is_encrypt = $this->data[$this->data_key]['is_encrypt'];
		} else {
			$this->req_data = $data;
			$is_encrypt = $this->is_encrypt;
		}
		$post_data = json_encode($data);
		if ($is_encrypt) {
			//echo '**** encrypt :'. PHP_EOL;
			$crypt_key = $this->getCryptoKey($token);
			//echo 'crypt_key : '. $crypt_key . PHP_EOL;
			//echo 'post data : '. $post_data . PHP_EOL;
			$post_data = $this->encrypt($post_data, $crypt_key);
			//echo 'encrypt post data : '. $post_data . PHP_EOL;
		}
		if ($this->data_key) {
			$this->data[$this->data_key]['req_json'] = $post_data;
		} else {
			$this->req_json = $post_data;
		}
		$curl = $this->get_curl();
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		$this->set_curl($curl);
	}
	// -----------------------------------------------------------------------------
	private function init_delete($data, $token=false) {
		$this->init_curl('delete', $token);
		if ($this->data_key) {
			$this->data[$this->data_key]['req_data'] = $data;
			$is_encrypt = $this->data[$this->data_key]['is_encrypt'];
		} else {
			$this->req_data = $data;
			$is_encrypt = $this->is_encrypt;
		}
		$delete_data = json_encode($data);
		if ($is_encrypt) {
			$crypt_key = $this->getCryptoKey($token);
			$delete_data = $this->encrypt($delete_data, $crypt_key);
		}
		if ($this->data_key) {
			$this->data[$this->data_key]['req_json'] = $delete_data;
		} else {
			$this->req_json = $delete_data;
		}
		$curl = $this->get_curl();
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($curl, CURLOPT_POSTFIELDS, $delete_data);
		$this->set_curl($curl);
	}
	// -----------------------------------------------------------------------------
	private function init_curl($mode, $token=false) {
		// -------------------------------------------------
		$header = array('Accept: application/json;charset=UTF-8');
		if (in_array($mode, array('post', 'delete'))) $header[] = 'Content-Type: application/json';
		if ($this->data_key) {
			$this->data[$this->data_key]['token'] = $token;
			$endpoint = $this->data[$this->data_key]['endpoint'];
		} else {
			$this->token = $token;
			$endpoint = $this->endpoint;
		}
		if ($token) $header[] = 'Authorization: Bearer '. $token;
		// -------------------------------------------------
		$curl = curl_init();
		if ($this->data_key) {
			$this->data[$this->data_key]['curl'] =& $curl;
		} else {
			$this->curl =& $curl;
		}
		curl_setopt($curl, CURLOPT_URL, $endpoint);
		curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
	}
	// -----------------------------------------------------------------------------
	private function get_curl() {
		return $this->data_key ? $this->data[$this->data_key]['curl'] : $this->curl;
	}
	// -----------------------------------------------------------------------------
	private function set_curl($curl) {
		if ($this->data_key) {
			$this->data[$this->data_key]['curl'] = $curl;
		} else {
			$this->curl = $curl;
		}
	}
	// ========================================================================================= Execute
	// -----------------------------------------------------------------------------
	private function execute() {
		// ------------- response
		$curl = $this->get_curl();
		$response = curl_exec($curl);
		if ($this->data_key) {
			$this->data[$this->data_key]['response'] = $response;
		} else {
			$this->response = $response;
		}
		// ------------- check error
		if (curl_error($curl)) {
			$this->setError(curl_errno($curl), curl_error($curl));
			if ($this->data_key) {
				curl_close($this->data[$this->data_key]['curl']);
			} else {
				curl_close($this->curl);
			}
			$this->data_key = '';
			return false;
		}
		// ------------- header & body
		$info = curl_getinfo($curl);
		$req_headers = $info['request_header'];
		$http_code   = $info['http_code'];
		list($res_headers, $res_body) = $this->get_http_response();
		if ($this->data_key) {
			curl_close($this->data[$this->data_key]['curl']);
		} else {
			curl_close($this->curl);
		}
		// -------------
		$is_decrypt = $this->data_key ? $this->data[$this->data_key]['is_decrypt'] : $this->is_decrypt;
		if ($http_code == '200' && $is_decrypt) {
			$token = $this->data_key ? $this->data[$this->data_key]['token'] : $this->token;
			if ($this->data_key) {
				$this->data[$this->data_key]['is_decrypt'] = false;
			} else {
				$this->is_decrypt = false;
			}
			$crypto_key = $this->getCryptoKey($token);
			$res_body = $this->decrypt($res_body, $crypto_key);
		}
		$res_data = json_decode($res_body, true);
		if ($res_data === null) $res_data = $res_body;
		// -------------
		if ($this->data_key) {
			$this->data[$this->data_key]['req_headers'] = $req_headers;
			$this->data[$this->data_key]['http_code']   = $http_code;
			$this->data[$this->data_key]['res_headers'] = $res_headers;
			$this->data[$this->data_key]['res_body']    = $res_body;
			$this->data[$this->data_key]['res_data']    = $res_data;
			$this->data_key = '';
		} else {
			$this->req_headers = $req_headers;
			$this->http_code   = $http_code;
			$this->res_headers = $res_headers;
			$this->res_body    = $res_body;
			$this->res_data    = $res_data;
		}
		// ------------- return
		return true;
	}
	// -----------------------------------------------------------------------------
	private function get_http_response() {
		$curl_result = $this->data_key ? $this->data[$this->data_key]['response'] : $this->response;
		$rawheader = $body = '';
		if (strpos($curl_result, "\r\n\r\n") !== false) {
			list($rawheader, $body) = explode("\r\n\r\n", $curl_result, 2);
		} else {
			$body = $curl_result;
		}
		$header_array = array();
		if ($rawheader) {
			$header_rows = explode("\n",$rawheader);
			for($i=0;$i<count($header_rows);$i++){
				$fields = explode(":",$header_rows[$i]);

				if($i != 0 && !isset($fields[1])){//carriage return bug fix.
					if(substr($fields[0], 0, 1) == "\t"){
						end($header_array);
						$header_array[key($header_array)] .= "\r\n\t".trim($fields[0]);
					}
					else{
						end($header_array);
						$header_array[key($header_array)] .= trim($fields[0]);
					}
				}
				else{
					$field_title = trim($fields[0]);
					if (!isset($header_array[$field_title])){
						$val = isset($fields[1]) ? $fields[1] : '';
						$header_array[$field_title]=trim($val);
					}
					else if(is_array($header_array[$field_title])){
							$header_array[$field_title] = array_merge($header_array[$fields[0]], array(trim($fields[1])));
						}
					else{
						$header_array[$field_title] = array_merge(array($header_array[$fields[0]]), array(trim($fields[1])));
					}
				}
			}
		}
		return array($header_array, $body);
	}
	// -----------------------------------------------------------------------------
	private function get_http_code() {
		$curl =& $this->get_curl();
		$info = curl_getinfo($curl);
		return $info['http_code'];
	}

	// ***************************************************************************************************** Crypto Function
	// =========================================================================================
	// ----------------------------------------------------------------------------- encrypt
	private function encrypt($input, $key='') {
		if ($key == '') $key = $this->client_secret;
		$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
		$input = $this->pkcs5_pad($input, $size);
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		$data = mcrypt_generic($td, $input);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$data = base64_encode($data);
		return $data;
	}
	// ----------------------------------------------------------------------------- decrypt
	private function decrypt($input, $key='') {
		if (substr($input, 0, 1) == '{') return $input;
		if ($key == '') $key = $this->client_secret;
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
		$decrypted= mcrypt_decrypt(
			MCRYPT_RIJNDAEL_128,
			$key,
			base64_decode($input),
			MCRYPT_MODE_ECB
			//, $iv
		);
		$dec_s = strlen($decrypted);
		$padding = ord($decrypted[$dec_s-1]);
		$data = substr($decrypted, 0, -$padding);
		return $data;
	}
	// -----------------------------------------------------------------------------
	private function getCryptoKey($token) {
		$data_key = 'crypto_key';
		$this->crypto_key($token, $data_key);
		return $this->data[$data_key]['res_data']['data']['key'];
	}
	// =========================================================================================
	// -----------------------------------------------------------------------------
	private function set_encrypt() {
		if ($this->data_key) {
			$this->data[$this->data_key]['is_encrypt'] = true;
		} else {
			$this->is_encrypt = true;
		}
	}
	// -----------------------------------------------------------------------------
	private function set_decrypt() {
		if ($this->data_key) {
			$this->data[$this->data_key]['is_decrypt'] = true;
		} else {
			$this->is_decrypt = true;
		}
	}
	// ========================================================================================= PKCS
	// ----------------------------------------------------------------------------- PAD
	private function pkcs5_pad ($text, $blocksize) {
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad), $pad);
	}
	// ----------------------------------------------------------------------------- UNPAD
	private function pkcs5_unpad($text) {
		$pad = ord($text{strlen($text)-1});
		if ($pad > strlen($text)) return false;
		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
		return substr($text, 0, -1 * $pad);
	}

	// ***************************************************************************************************** Get Data
	// =========================================================================================
	// -----------------------------------------------------------------------------
	public function getHttpCode() {
		return $this->http_code;
	}
	public function getSuccessCode() {
		return '0000';
	}
	// =========================================================================================
	// -----------------------------------------------------------------------------
	public function getReqHeaders() {
		return $this->req_headers;
	}
	// -----------------------------------------------------------------------------
	public function getReqData() {
		return $this->req_data;
	}
	// -----------------------------------------------------------------------------
	public function getReqJson() {
		return $this->req_json;
	}
	// =========================================================================================
	// -----------------------------------------------------------------------------
	public function getResponse() {
		return $this->response;
	}
	// -----------------------------------------------------------------------------
	public function getResHeaders() {
		return $this->res_headers;
	}
	// -----------------------------------------------------------------------------
	public function getResBody() {
		return $this->res_body;
	}
	// -----------------------------------------------------------------------------
	public function getResCode() {
		return $this->res_data['code'];
	}
	// -----------------------------------------------------------------------------
	public function getResMsg() {
		return $this->res_data['msg'];
	}
	// -----------------------------------------------------------------------------
	public function getResData() {
		return $this->res_data['data'];
	}
	// ***************************************************************************************************** Error
	// =========================================================================================
	// -----------------------------------------------------------------------------
	private function setError($errno, $error) {
		$this->setErrCode($errno);
		$this->setErrMsg($error);
	}
	// -----------------------------------------------------------------------------
	private function setErrCode($errno) {
		if ($this->data_key) {
			$this->data[$this->data_key]['errno'] = $errno;
		} else {
			$this->errno = $errno;
		}
	}
	// -----------------------------------------------------------------------------
	private function setErrMsg($error) {
		if ($this->data_key) {
			$this->data[$this->data_key]['error'] = $error;
		} else {
			$this->error = $error;
		}
	}
	// =========================================================================================
	// -----------------------------------------------------------------------------
	public function getErrCode() {
		if ($this->data_key) {
			$errno = $this->data[$this->data_key]['errno'];
		} else {
			$errno = $this->errno;
		}
		return $errno;
	}
	// -----------------------------------------------------------------------------
	public function getErrMsg() {
		if ($this->data_key) {
			$error = $this->data[$this->data_key]['error'];
		} else {
			$error = $this->error;
		}
		return $error;
	}
	// -----------------------------------------------------------------------------
	public function getErrStr() {
		return $this->getErrCode() .' - '. $this->getErrMsg();
	}
	// ***************************************************************************************************** End Point
	// =========================================================================================
	// -----------------------------------------------------------------------------
	private function getEndPoint($path) {
		$endpoint = $this->mode == 'REAL' ? _OKBIT_REAL_API_PATH_ : _OKBIT_TEST_API_PATH_;
		return $endpoint . $path;
	}
	// -----------------------------------------------------------------------------
	private function setEndPoint($path) {
		$this->endpoint = $this->getEndPoint($path);
	}

}
?>