<?php
if (!defined('__CLS_DATABASE')) define('__CLS_DATABASE', true); else return;
include 'adodb/adodb.inc.php';
DEFINE('ADODB_ASSOC_CASE',0); // column name is lowercase

class DataBase {
	// *****************************************************************************************
	public $dbh = null;
	private $db_type = '';
	private $stmt = null;
	private $dsn = array();
	private $dbError = array();
	private $dbQuery = array();
	private $dbAffectedRows = array();


	// *****************************************************************************************
	function __construct($dsn=null) {
		if ($dsn) $this->connect($dsn);
	}

	// *****************************************************************************************
	// ============================================================================= Connection
	public function connect($dsn) {
		if (is_null($dsn) || !is_array($dsn)) return false;
		$this->dsn = $dsn;
		$db_type = $this->dsn['db_type'] = strtolower($dsn['db_type']);
		$db_type_list = $this->getDbTypeList();
		foreach ($db_type_list as $k=>$v) {
			if (strpos($db_type, $k) === false) continue;
			$this->db_type = $v;
			break;
		}

		$this->dbh = ADONewConnection($db_type);
		$db_host = $dsn['db_host'];
		if ($dsn['db_port']) $db_host .= ':'. $dsn['db_port'];
		$db_user = $dsn['db_user'];
		$db_pass = $dsn['db_pass'];
		$db_name = $dsn['db_name'];

		$this->dbh->setCharset($dsn['db_char']);
		$result = $this->dbh->connect($db_host, $db_user, $db_pass, $db_name);
		if (!$result) $this->addError();
		$this->default_fetch_mode();
		return false;
	}
	private function getDbTypeList() {
		return array(
				  'mysql'    => 'mysql'
				, 'postgres' => 'postgres'
				, 'mssql'    => 'mssql'
				, 'oci'      => 'oracle'
				, 'oracle'   => 'oracle'
			);
	}
	// ============================================================================= isConnection
	public function is_connect() {
		return $this->dbh->isConnected();
	}
	// ============================================================================= DisConnection
	public function disconnect() {
		if (is_null($this->dbh)) return;
		$this->dbh->Close();
	}

	private function set_fetch_mode($mode) {
		$this->dbh->setFetchMode($mode);
	}
	private function default_fetch_mode() {
		$this->dbh->setFetchMode(ADODB_FETCH_DEFAULT);
	}

	// ***************************************************************************************** SELECT Query
	// =============================================================================
	public function get_all($sql, $bindvars=false) {
		$this->set_fetch_mode(ADODB_FETCH_ASSOC);
		$list = $this->dbh->getAll($sql, $bindvars);
		$this->default_fetch_mode();
		return $list;
	}
	public function get_row($sql, $bindvars=false) {
		$this->set_fetch_mode(ADODB_FETCH_ASSOC);
		$row = $this->dbh->getRow($sql, $bindvars);
		$this->default_fetch_mode();
		return $row;
	}
	public function query($sql, $bindvars=false) {
		return $this->select($sql, $bindvars);
	}


	public function select($sql, $bindvars=false) {
		$this->stmt = $this->dbh->Execute($sql);
		//print_r($this->stmt);
		//echo $this->dbh->ErrorMsg() .'/';
		return $this->stmt;
	}
	// =============================================================================
	// 
	

	public function select_all($sql, $bindvars=false, $forceArray=false, $first2Cols=false) {
		return $this->dbh->getAssoc($sql, $bindvars, true, false);
	}
	public function select_limit($sql, $rows_cnt=-1, $offset=-1, $bindvars=false) {
		if ($rows_cnt <= 0) return $this->get_all($sql, $bindvars);
		$db_type = $this->dsn['db_type'];		
		if (in_array($this->db_type, array('mysql', 'postgres'))) {
			
			$rs = $this->dbh->SelectLimt($sql, $rows_cnt, $offset, $bindvars);
		} else if (in_array($this->db_type, array('oci', 'oracle'))) {
			$query = array();
			$query[] = "SELECT * FROM (";
			$query[] = "SELECT ROWNUM AS RNUM, T.* FROM (";
			$query[] = $sql;
			$query[] = ") T WHERE ROWNUM <= ". ($offset + $rows_cnt);
			$query[] = ") WHERE RNUM >= ".($offset + 1);
			$sql = implode(PHP_EOL, $query);
			//echo $rows_cnt .' / '. $offset;
			//return array();
			echo $sql;
			$rs = $this->get_all($sql, $bindvars);
		} else {
			$rs = array();
		}
		return $rs;
	}
	public function fetch_row($stmt=null) {
		if ($stmt === null) $stmt = $this->stmt;
		return $stmt->FetchRow();
	}
	public function fetch_array($stmt=null) {
		if ($stmt === null) $stmt = $this->stmt;
		//$this->dbh->setFetchMode(ADODB_FETCH_NUM);
		return $stmt->FetchRow();
	}
	public function fetch_assoc($stmt=null) {
		if ($stmt === null) $stmt = $this->stmt;
		//$stmt->FetchRow(false);
		if ($stmt->EOF) return null;
		$row = $stmt->getRowAssoc(false);
		$stmt->moveNext();
		return $row;
	}
	public function fetch_object($stmt=null) {
		if ($stmt === null) $stmt = $this->stmt;
		if ($stmt->EOF) return null;
		return $stmt->fetchNextObj();
	}
	public function fetch_one($stmt=null) {
		if ($stmt === null) $stmt = $this->stmt;
		$row = $stmt->FetchRow();
		return $row[0];
	}
	// ***************************************************************************************** Execute Query
	// =============================================================================
	public function execute($sql, $bind=array()) {
		$result = $this->dbh->execute($sql, $bind);
		if (!$result) $this->addError($this->dbh->ErrorMsg());
		return $result;
	}
	// =============================================================================
	public function insert($tbl, $rs) {
		$result = $this->dbh->autoExecute($tbl, $rs, 'INSERT');
		if (!$result) $this->addError($this->dbh->ErrorMsg());
		return $result;
	}
	// =============================================================================
	public function update($tbl, $rs, $where='') {
		$result = $this->dbh->autoExecute($tbl, $rs, 'UPDATE', $where);
		if (!$result) $this->addError($this->dbh->ErrorMsg());
		return $result;
	}
	// ***************************************************************************************** Transaction
	// =============================================================================
	function beginTrans() {
		$this->dbh->beginTrans();
	}
	// =============================================================================
	function rollback() {
		$this->dbh->rollbackTrans();
	}
	// =============================================================================
	function commit() {
		$this->dbh->commitTrans();
	}
	// ***************************************************************************************** Error
	// =============================================================================
	private function setError() {
	}
	// =============================================================================
	private function addError($err='') {
		if ($err == '') $this->dbh->ErrorMsg();
		$this->dbError[] = $err;
	}
	// =============================================================================
	public function getError() {
		$ret = null;
		if (($cnt = count($this->dbError)) > 0) {
			$ret = $this->dbError[$cnt-1];
		}
		return $ret;
	}
	// =============================================================================
	public function getErrorString() {
		$error = $this->getError();
		$ret = array();
		foreach ($error as $key => $value) $ret[] = $key ." : ". $value;
		return implode(PHP_EOL, $ret);
	}
	// =============================================================================
	public function getErrorAll() {
		return $this->dbError;
	}
	// =============================================================================
	private function exitError() {
	}

}

class DBH {
	private static $instance;
	private static $dsn;
	public static function getInstance($dsn_name='') {
		if ($dsn_name == '') $dsn_name = self::getDefaultDsnName();
		if (null === static::$instance) static::$instance = array();
		if (!isset(static::$instance[$dsn_name])) static::$instance[$dsn_name] = static::connect($dsn_name);
		return static::$instance[$dsn_name];
	}
	public static function connect($dsn_name='') {
		// ------------------------- DSN 정보조회
		if ($dsn_name == '') $dsn_name = self::getDefaultDsnName();
		$dsn = self::getDsnInfo($dsn_name);
		if (is_null($dsn)) return null;
		// -------------------------
		return new DataBase($dsn);
	}
	public static function close($dsn_name='') {
		if (null === static::$instance) return;
		if ($dsn_name) {
			if (!isset(static::$instance[$dsn_name])) return;
			if (is_null(static::$instance[$dsn_name])) return;
			static::$instance[$dsn_name]->disconnect();
			unset(static::$instance[$dsn_name]);
			return;
		}
		foreach (static::$instance as $dsn_nm => $dbh) {
			$dbh->disconnect();
			unset(static::$instance[$dsn_nm]);
		}
	}

	private static function setDsnInfo() {
		if (!is_null(self::$dsn)) return;
		$cfg_file_path = CFG_ROOT .'/cfg.dsn.php';
		if (($config = parse_ini_file($cfg_file_path, true)) === false) return null;
		self::$dsn = $config;

	}

	private static function getDsnInfo($dsn_name) {
		self::setDsnInfo();
		if (!isset(self::$dsn[$dsn_name])) return null;
		return self::$dsn[$dsn_name];
	}

	private static function getDefaultDsnName() {
		return getSystemConfig('default_dsn_name');
	}
}
?>