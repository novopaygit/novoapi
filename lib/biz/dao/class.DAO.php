<?php
if (!defined('__DAO')) define('__DAO', true); else return;

class DAO {
	protected $dbh;
	protected $bindvars = false;
	protected $where;
	protected $tbl;
	protected $pk_col = array();
	protected $last_error = '';

	function __construct($dsn_nm='') {
		$this->setDBH($dsn_nm);
		$this->init();
	}

	protected function init($tbl='', $pk='') {
		$this->initWhere();
		if ($tbl) $this->setTable($tbl);
	}

	protected function initWhere() {
		$this->where = array();
	}
	protected function addWhere($sql) {
		if (!is_array($this->where)) $this->where = array();
	}
	protected function getWhere() {
		if ($this->where) return 'WHERE '. implode(PHP_EOL . '  AND ', $this->where);
		return '';
	}

	protected function addBindVar($k, $v) {
		if (!is_array($this->bindvars)) $tis->bindvars = array();
		$this->bindvars[$k] = $v;
	}
	protected function getBindVar() {
		return $this->bindvars;
	}

	protected function getOffset($page, &$size) {
		if ($size > 0) {
			$offset = ($page - 1) * $size;
		} else {
			$size = -1;
			$offset = -1;
		}
		return $offset;
	}

	protected function convQuery($query) {
		return implode(PHP_EOL, $query);
	}

	protected function setDBH($dsn_nm='') {
		if ($dsn_nm == '') $dsn_nm = getSystemConfig('default_dsn_name');
		$this->dbh = instanceDBH($dsn_nm);
	}

	protected function setTable($tbl) {
		$this->tbl = $tbl;
	}
	protected function setPkColumn($pk_col) {
		if (!is_array($pk_col)) $pk_col = array($pk_col);
		$this->pk_col = $pk_col;
	}

	public function insert($data) {
		if (!$this->tbl) {
			$this->last_error = '테이블 설정 필요';
			return false;
		}
		if (!$this->dbh->insert($this->tbl, $data)) {
			$this->last_error = $this->dbh->getError();
			return false;
		}
		return true;
	}
	public function update($data, $where) {
		if (!$this->tbl) {
			$this->last_error = '테이블 설정 필요';
			return false;
		}
		if (!$this->dbh->update($this->tbl, $data, $where)) {
			$this->last_error = $this->dbh->getError();
			return false;
		}
		return true;
	}

	public function getErrorMsg() {
		return $this->last_error;
	}
}
?>