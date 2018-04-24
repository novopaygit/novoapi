<?php
if (!defined('__CLS_AJAX_ACTION')) define('__CLS_AJAX_ACTION', true); else return;

class AjaxAction {
	var $res;
	var $objJson;

	function __construct() {
		$this->res = array(
				  'result' => false
				, 'rescode' => ''
				, 'res_msg' => ''
				, 'err_msg' => ''
				, 'err_dtl' => ''
			);
		require_once CLS_ROOT .'/class.Services_JSON.php';
		$this->objJson = new Services_JSON;
	}

	function success($rescode='', $res_msg='') {
		if (isAjaxPage()) header('Content-type: application/json');
		$this->res['result'] = true;
		if ($res_msg == '') {
			$this->res['rescode'] = '';
			$this->res['res_msg'] = $rescode;
		} else {
			$this->res['rescode'] = $rescode;
			$this->res['res_msg'] = $res_msg;
		}
		return $this->response();
	}

	function jsonpSuccess($rescode='', $res_msg='') {
		$this->res['result'] = true;
		if ($res_msg == '') {
			$this->res['rescode'] = '';
			$this->res['res_msg'] = $rescode;
		} else {
			$this->res['rescode'] = $rescode;
			$this->res['res_msg'] = $res_msg;
		}
		return $this->response(true);
	}

	function alert($msg, $act='') {
		echo '<!DOCTYPE html>'. PHP_EOL;
		echo '<html lang="kr">'. PHP_EOL;
		echo '<head>'. PHP_EOL;
		echo includePageHeader(false);
		echo '<script language="javascript">'. PHP_EOL;
		echo 'alert("'. str_replace("\"", "\\\"", $msg) .'");'. PHP_EOL;
		switch ($act) {
			case 'close' :
				//echo 'alert(opener);'. PHP_EOL;
				echo 'if (opener) self.close();'. PHP_EOL;
				break;
		}
		echo '</script>'. PHP_EOL;
		echo '</head>'. PHP_EOL;
		echo '<body>'. PHP_EOL;
		echo '</body>'. PHP_EOL;
		echo '</html>'. PHP_EOL;
		exit;
	}

	function fail($rescode='', $err_msg='', $err_dtl='') {
		if (isAjaxPage()) header('Content-type: application/json');
		$this->res['result'] = false;
		if ($err_msg == '' && $err_dtl == '') {
			$this->res['rescode'] = '';
			$this->res['err_msg'] = $rescode;
			$this->res['err_dtl'] = '';
		} else {
			$this->res['rescode'] = $rescode;
			$this->res['err_msg'] = $err_msg;
			$this->res['err_dtl'] = $err_dtl;
		}
		return $this->response();
	}

	function jsonpFail($rescode='', $err_msg='', $err_dtl='') {
		$this->res['result'] = false;
		if ($err_msg == '' && $err_dtl == '') {
			$this->res['rescode'] = '';
			$this->res['err_msg'] = $rescode;
			$this->res['err_dtl'] = '';
		} else {
			$this->res['rescode'] = $rescode;
			$this->res['err_msg'] = $err_msg;
			$this->res['err_dtl'] = $err_dtl;
		}
		return $this->response(true);
	}

	function permission($msg='') {
		switch ($msg) {
			case 'excel' : $msg = '엑셀 다운로드 권한이 없습니다.'; break;
			case 'search' : $msg = '조회 권한이 없습니다.'; break;
			case 'insert' : $msg = '추가 권한이 없습니다.'; break;
			case 'update' : $msg = '수정 권한이 없습니다.'; break;
			case 'delete' : $msg = '삭제 권한이 없습니다.'; break;
		}
		$this->res['result']  = false;
		$this->res['rescode'] = 'permission';
		$this->res['err_msg'] = $msg;
		return $this->response();
	}

	function addResponse($key, $val) {
		$this->res[$key] = $val;
	}

	function addDataList($key, $stmt) {
		$return_json = '';
		$ret = array();
		while ($row = mssql_fetch_assoc($stmt)) {
			/*
			$row = str_replace(array('"', "'"), '', $row);
			$row = str_replace(array("\r\n", "\n\r", "\r", "\n", chr(13), chr(10)), ' ', $row);
			$ret[] = $this->str_urlencode($row);
			//$return_json .= $json->encode(lib::str_urlencode($res)) . ",";
			*/
			$ret[] = $this->convertRowData($row);
		}
		addResponse($key, $ret);
	}

	function convertRowData($row) {
		$ret = array();
		foreach ($row as $k => $v) {
			$row[$k] = euckr_utf8($v);
		}
		return $ret;
	}
	// 인코딩
	function str_urlencode($param) {
		if(is_array($param)) {
			$ret = array();
			foreach ($param AS $k => $v) {
				$key = strtolower($k);
				$val = is_array($v) ? $this->str_urlencode($v) : rawurlencode($v);
				$ret[$key] = $val;
			}
		} else {
			$ret = rawurlencode($param);
		}
		return $ret;
	}

	function response($is_jsonp=false) {
		global $dbp;
		if (isset($dbp) && is_object($dbp)) {
			$dbp->disconnect();
			unset($dbp);
		}
		if (isAjaxPage()) {
			if ($is_jsonp) $callback = $_GET['callback'];
			if ($is_jsonp) echo $callback .'(';
			echo $this->objJson->encode($this->res);
			if ($is_jsonp) echo ')';
		} else {
			if ($is_jsonp) {
				$callback = $_GET['callback'];
				echo $callback .'('. $this->objJson->encode($this->res) .')';
			} else {
				echo '<pre>'. print_r($this->res, true) .'</pre>';
			}
		}
		exit;
	}
}
?>