<?php
if (!defined('__SVC')) define('__SVC', true); else return;

class SVC {
	protected $dao = array();
	private $err_list = array();

	protected function getDAO($dao_nm) {
		if (!isset($this->dao[$dao_nm])) $this->dao[$dao_nm] = instanceDAO($dao_nm);
		return $this->dao[$dao_nm];
	}
	protected function loadDAO($dao_nm) {
	}

	protected function addError($err) {
		$this->err_list[] = $err;
		return false;
	}
	public function getLastError() {
		$cnt = count($this->err_list);
		if ($cnt == 0) return '';
		return $this->err_list[$cnt-1];
	}
}
?>