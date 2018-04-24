<?php

// =================================================================================

/*function __autoload($className) {

	$classFile = CLS_ROOT . '/class.' . $className . '.php';
	if (file_exists($classFile)) include_once $classFile;

}
*/

// 오토로딩용 함수 20180327 최인석
// __autoload 함수보다는 spl_autoload_register 를 권장하기에 바꿈
spl_autoload_register('NovoPayClassLoader::ClassLoader');
//spl_autoload_register('NovoPayClassLoader::DatabaseLoader'); 
class NovoPayClassLoader
{
    public static function ClassLoader($className)
    {  
    	$classFile = CLS_ROOT . DS . 'class.' . $className . '.php';
    	if (file_exists($classFile)) include_once $classFile;
        
    }
   // public static function DatabaseLoader ($class)
   //{  
   //     include 'databases/' . $class. '.php';
   // }
}



// UTF-8 문자열 자르기
// 출처 : https://www.google.co.kr/search?q=utf8_strcut&aq=f&oq=utf8_strcut&aqs=chrome.0.57j0l3.826j0&sourceid=chrome&ie=UTF-8
function utf8_strcut( $str, $size, $suffix='...' )
{
	$substr = substr( $str, 0, $size * 2 );
	$multi_size = preg_match_all( '/[\x80-\xff]/', $substr, $multi_chars );

	if( $multi_size > 0 )
		$size = $size + intval( $multi_size / 3 ) - 1;

	if( strlen( $str ) > $size ) {
		$str = substr( $str, 0, $size );
		$str = preg_replace( '/(([\x80-\xff]{3})*?)([\x80-\xff]{0,2})$/', '$1', $str );
		$str .= $suffix;
	}

	return $str;
}

// CHARSET 변경 : euc-kr -> utf-8
function iconv_utf8($str)
{
	return iconv('euc-kr', 'utf-8', $str);
}

// CHARSET 변경 : utf-8 -> euc-kr
function iconv_euckr($str)
{
	return iconv('utf-8', 'euc-kr', $str);
}

// ============================================================================================= Ajax
// ---------------------------------------------------------------------------------
function ajaxSuccess($data) {
	if (!headers_sent()) header('Content-Type: application/json');
	$data['result'] = true;
	echo json_encode($data);
	exit;
}
// ---------------------------------------------------------------------------------
function ajaxFail($msg) {
	if (!headers_sent()) header('Content-Type: application/json');
	$res = array(
			'result' => false,
			'err_msg' => $msg
		);
	echo json_encode($res);
	exit;
}
//------------------ 

function redirect($url) {
	Header('Location: '. $url);
	exit;
}


function isAjaxPage() {
	if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		return true;
	} else {
		return false;
	}
}


function request($pKeyName, $pDefault=null, $pVar='REQUEST', $pIsTrim=true) {
	$pVar = strtoupper($pVar);
	switch ($pVar) {
		case 'POST'    : $reqVar = $_POST;    break;
		case 'GET'     : $reqVar = $_GET;     break;
		case 'REQUEST' : $reqVar = $_REQUEST; break;
		default        : $reqVar = $_POST;    break;
	}
	if (!isset($reqVar[$pKeyName])) return $pDefault;
	$reqValue = $reqVar[$pKeyName];
	if (is_array($reqValue)) {
		foreach ($reqValue as $key => $val) {
			//$reqValue[$key] =
		}
	} else {
		if ($pIsTrim) $reqValue = trim($reqValue);
		//$reqValue = utf8_euckr($reqValue);
	}
	return $reqValue;
}
function request_db($pKeyName, $pDefault=null, $pVar='REQUEST', $pIsTrim=true) {
	$pVar = strtoupper($pVar);
	switch ($pVar) {
		case 'POST'    : $reqVar = $_POST;    break;
		case 'GET'     : $reqVar = $_GET;     break;
		case 'REQUEST' : $reqVar = $_REQUEST; break;
		default        : $reqVar = $_POST;    break;
	}
	if (!isset($reqVar[$pKeyName])) return $pDefault;
	$reqValue = $reqVar[$pKeyName];
	if (is_array($reqValue)) {
		foreach ($reqValue as $key => $val) {
			$reqValue[$key] = request_db_arr($val, $pDefault, $pIsTrim);
		}
	} else {
		if ($pIsTrim) $reqValue = trim($reqValue);
		if (_DB_CHARSET_ == 'euckr') $reqValue = utf8_euckr($reqValue);
	}
	return $reqValue;
}
function request_db_arr($pData, $pDefault=null, $pIsTrim=true) {
	if (is_array($pData)) {
		foreach ($pData as $key => $val) {
			$pData[$key] = request_db_arr($val, $pDefault, $pIsTrim);
		}
	} else {
		if ($pIsTrim) $pData = trim($pData);
		$pData = utf8_euckr($pData);
	}
	return $pData;
}
function request_date($pKeyName, $pDefault=null, $pVar='REQUEST', $pIsTrim=true) {
	$ret = request($pKeyName, $pDefault, $pVar, $pIsTrim);
	return str_replace('-', '', $ret);
}
function request_db_date($pKeyName, $pDefault=null, $pVar='REQUEST', $pIsTrim=true) {
	$ret = request_db($pKeyName, $pDefault, $pVar, $pIsTrim);
	return str_replace('-', '', $ret);
}
function request_number($pKeyName, $pDefault=null, $pVar='REQUEST', $pIsTrim=true) {
	return preg_replace('/[^0-9]/i', '', request($pKeyName, $pDefault, $pVar, $pIsTrim));
}
function request_db_number($pKeyName, $pDefault=null, $pVar='REQUEST', $pIsTrim=true) {
	return preg_replace('/[^0-9]/i', '', request_db($pKeyName, $pDefault, $pVar, $pIsTrim));
}
function request_db_paging() {
	$excelmake  = request_db('excelmake');
	$page       = intval(request_db('page'));
	$pagesize   = intval(request_db('pagesize'));
	if ($excelmake == 'Y') {
		$page = 0;
		$pagesize = 0;
	} else {
		$excelmake = '';
		if ($page < 1) $page = 1;
		if ($pagesize < 1) $pagesize = 50;
	}
	return array($page, $pagesize, $excelmake);
	return array($excelmake, $page, $pagesize);
}


// ================================================================================= Request Values
function ajax($pKeyName, $pDefault=null, $pVar='REQUEST', $pIsTrim=true) {
	return request($pKeyName, $pDefault, 'POST', $pIsTrim);
}
function post($pKeyName, $pDefault=null, $pVar='REQUEST', $pIsTrim=true) {
	return request($pKeyName, $pDefault, 'POST', $pIsTrim);
}
function get($pKeyName, $pDefault=null, $pVar='REQUEST', $pIsTrim=true) {
	return request($pKeyName, $pDefault, 'GET', $pIsTrim);
}

// =================================================================================

/**
 * novobiz 쇼핑몰에서사용하는함수로 명칭은 alert -> alertphp
 * @param  [type] $msg    [description]
 * @param  string $move   [description]
 * @param  string $myname [description]
 * @return [type]         [description]
 */
function alertphp($msg, $move="back", $myname='')
{
	if(!$msg) $msg = '올바른 방법으로 이용해 주십시오.';

	switch($move)
	{
		case "back" :
			$url = "history.go(-1);void(1);";
			break;
		case "close" :
			$url = "window.close();";
			break;
		case "parent" :
			$url = "parent.document.location.reload();";
			break;
		case "replace" :
			$url = "opener.document.location.reload();window.close();";
			break;
		case "no" :
			$url = "";
			break;
		case "shash" :
			$url = "location.hash='".$myname."';";
			break;
		case "thash" :
			$url  = "opener.document.location.reload();";
			$url .= "opener.document.location.hash='".$myname."';";
			$url .= "window.close();";
			break;
		default :
			$url = "location.href='".$move."'";
			break;
	}

	echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">";
	echo "<script type='text/javascript'>alert(\"".$msg."\");".$url."</script>";exit;
}


?>