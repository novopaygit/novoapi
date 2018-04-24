<?php
if (!defined('__CLS_AUTH')) define('__CLS_AUTH', true); else return;
class Auth {
	static $inactive;
	static $session_life;
	public static $login_status = '';
	public static $msg = '';

	public static function isLogin() {
		if (!self::checkLoginStatus()){
			return false;
		}else{
			return true;
		}
	}
	public static function checkLogin() {
		if (self::isLogin()) {
			$_SESSION['login_check_time'] = time();
			return true;
		}else{
			self::checkLoginFailResponse();
			return false;
		}
	}
	public static function checkLoginStatus() {
		if (isset($_SESSION['login_check_time']) && $_SESSION['login_check_time'] != ''){
			if(self::checkLoginExpire()){
				return true;
			}else{
				return false;
			}
		}else{
			self::$login_status = 'not_login';
			return false;
		}
	}
	public static function checkLoginExpire() {
		if (isset($_SESSION['login_check_time'])){
			self::$inactive = intval($_SESSION["cache_expire"]); // 세션시간설정 (초단위)
			self::$session_life = intval(time() - intval($_SESSION['login_check_time']));
			if (self::$session_life <= self::$inactive) {
				$_SESSION['login_check_time'] = time();
				return true;
			}else{
				self::$login_status = 'expire';
				return false;
			}
		}
		return false;
	}

	public static function checkLoginFailResponse($msg='') {
		self::execLogout();

		 switch (self::$login_status) {
		 	 case 'not_login' :
		 		$debug = array();
		 		self::$msg = '로그인 후 사용가능합니다.';
		 		break;
			 case 'expire' :
			 	self::$msg = '세션이 종료되었습니다.다시 로그인 하세요.';
				 /* $debug = array();
				 $debug[] = '세션이 종료되었습니다.다시 로그인 하세요.';
				 if (EXEC_MODE == 'DEV') {
					 $debug[] = '';
					 $debug[] = 'time now : '. time() .'('. date('H:i:s', time()) .')';
					 $debug[] = 'time ses : '. $_SESSION['adm_login_check_time'] .'('. date('H:i:s', $_SESSION['adm_login_check_time']) .')';
					 $debug[] = 'expire   : '. self::$inactive .' ('. date('H:i:s', self::$inactive) .')';
					 $debug[] = 'life time : '. self::$session_life .' ('. date('H:i:s', self::$session_life) .')';
				 }
				 self::$msg = implode("\\n", $debug); */
			 	break;
			 default :
			 	self::$msg = '정상적으로 접근하셔야 합니다.';
			 	break;
		 }
		//exit;
	}

	public static function execLogin($sessionParam) {
		$_SESSION['login_check_time'] = time();
		$_SESSION["cache_expire"] = 1800;
		foreach ($sessionParam as $k => $v) $_SESSION[$k] = $v;
		session_write_close();
	}

	public static function execLogout() {
		$keys = self::getSessionKey();
		foreach ($keys as $k=>$v) unset($_SESSION[$k]);
	}
	public static function getLoginInfo($param) {
		static $sess = null;
		if (is_null($sess)) {
			$keys = self::getSessionKey();
			$sess = array();
			foreach ($keys as $k=>$is) {
				if (!$is) continue;
				$sess[$k] = $_SESSION[$k];
			}
		}
		if ($param) {
			if (isset($sess[$param])) return $sess[$param];
			return null;
		}
		return $sess;
	}

	private static function getSessionKey() {
		return array('mall_id'=>true,
					 'mall_nm'=>true,
					 'user_nm'=>true,
					 'isadmin'=>true,
					 'login_check_time'=>false,
					 'cache_expire'=>false
					);
	}
}
?>