<?php
function class_autoload() {
}

/* 최인석 안쓰는것같아서 일단 뺌
//func.common.php 에 autoload 추가 
spl_autoload_register(function($cls_nm) {
	if (substr($cls_nm, 0, 4) == 'SVC_') {
		require SVC_ROOT .'/class.'. $cls_nm .'.php';
	} else if (substr($cls_nm, 0, 4) == 'DAO_') {
		require DAO_ROOT .'/class.'. $cls_nm .'.php';
	}
});
*/
function getSystemConfig($key='') {
	static $cfg;
	if (is_null($cfg)) {
		$cfg_file_path = CFG_ROOT .'/cfg.system.php';
		$cfg = require($cfg_file_path);
	}
	if (!$key) return $cfg;
	if (!isset($cfg[$key])) return null;
	return $cfg[$key];

}

function instanceDBH($dsn_nm='') {
	require_once CLS_ROOT .'/class.DataBase.php';
	return DBH::getInstance($dsn_nm);
}
function instanceDAO($dao_name) {
	$cls_nm = 'DAO_'. $dao_name;
	$cls_path = DAO_ROOT .'/class.'. $cls_nm .'.php';
	if (!file_exists($cls_path)) return null;
	require_once $cls_path;
	return new $cls_nm();
}
function instanceSVC($svc_nm,$mall_id='') {
	/**
	 * 20180402 최인석수정
	 * $mall_id 추가함 
	 * class.SVC_CoinExch.php 를 호출할때는 반드시 mall id 가있어야한다.
	 * 추가한 사유 : mall 마다 고유의 거래소와 거래소아이디를 가져간다. - 20180402박성균 상무님
	 */
	// 
	return instanceService($svc_nm,$mall_id);

}

/**
 * 20180402 최인석수정
 * $mall_id 추가함 
 * class.SVC_CoinExch.php 를 호출할때는 반드시 mall id 가있어야한다.
 * 추가한 사유 : mall 마다 고유의 거래소와 거래소아이디를 가져간다. - 20180402박성균 상무님
 * @param  [type] $svc_nm  [생성항 class 명]
 * @param  [type] $mall_id [mall id ]
 * @return [type] class object  [생성된 클래스 오브젝트]
 */
function instanceService($svc_nm,$mall_id) {

	$cls_nm = 'SVC_'. $svc_nm;
	$cls_path = SVC_ROOT .'/class.'. $cls_nm .'.php';
	if (!file_exists($cls_path)) return null;
	require_once $cls_path;

	if ($svc_nm =='CoinExch') {
		return new $cls_nm($mall_id);
	}else{
		return new $cls_nm();
	}
}

/**
 * 20180404 최인석 작성 
 * 쇼핑몰 config 파일 에서 쇼핑몰정보를 가져오기 위한 함수 
 * @param  [type] $mall_id [쇼핑몰아이디] 
 * @return [type] array()    [보안상 쇼핑몰 아이디 쇼핑몰 AUTH_KEY,RECEIVE_URL(쇼핑몰에서 결제결과를 받기위한페이지), 거래소종류값 EXCHANGE_ID 만 반환한다..]
 */

function getMallConfig($mall_id) {
		static $mallcfg;
		if (is_null($mallcfg)) {
			$mallcfg = parse_ini_file(CLS_ROOT . DS .'coinexch/config.CoinExch.php', true);
		}
		$info = array();
		if (isset($mallcfg[$mall_id])) {

			$info['AUTH_KEY'] = $mallcfg[$mall_id]['AUTH_KEY'];	
			$info['RECEIVE_URL'] = $mallcfg[$mall_id]['RECEIVE_URL'];	
			$info['EXCHANGE_ID'] = $mallcfg[$mall_id]['EXCHANGE_ID'];	//거래소종류 okbit 
					
		}
		return $info;
	}

function php_version($param='') {
	$version = explode('.', PHP_VERSION);
	$param = strtolower($param);
	switch ($param) {
		case 'major'   : return $version[0];
		case 'minor'   : return $version[0];
		case 'release' : return $version[0];
		default : return implode('.', $version);
	}
}
?>