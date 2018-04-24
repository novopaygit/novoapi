<?php
if (!defined('__SVC_TOKEN')) define('__SVC_TOKEN', true); else return;
require_once 'class.SVC.php';

class SVC_Token extends SVC {

	//function __construct() {
	//	echo 'SVC_Board'. PHP_EOL;
	//}

	public function saveToken($data) {
		$data['expire_dt'] = date('Y-m-d H:i:s', time() + (20*60)); // 20분
		$data['reg_dt'] = date('Y-m-d H:i:s');
		$dao = $this->getDAO('Token');
		$dao->insert($data);
	}

	public function getTokenInfo($token) {
		$dao = $this->getDAO('Token');
		return $dao->getTokenInfo($token);
	}

	public function checkTokenID($token) {
		$daoToken = $this->getDAO('Token');
		if ($daoToken->getTokenInfo($token)) return false;
		$daoPayTemp = $this->getDAO('PayTemp');
		if ($daoPayTemp->getInfo4Token($token)) return false;
		return true;
	}

	public static function getToken($length=50) {
		$token = "";
		$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
		$codeAlphabet.= "0123456789";
		$max = strlen($codeAlphabet); // edited

		for ($i=0; $i < $length; $i++) {
			if (php_version('major') < 7) {
				$token .= $codeAlphabet[self::crypto_rand_secure(0, $max-1)];
			} else {
				$token .= $codeAlphabet[random_int(0, $max-1)]; // PHP 7
			}
		}

		return $token;
	}

	private static function crypto_rand_secure($min, $max) {
		$range = $max - $min;
		if ($range < 1) return $min; // not so random...
		$log = ceil(log($range, 2));
		$bytes = (int) ($log / 8) + 1; // length in bytes
		$bits = (int) $log + 1; // length in bits
		$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ($rnd > $range);
		return $min + $rnd;
	}
}
?>