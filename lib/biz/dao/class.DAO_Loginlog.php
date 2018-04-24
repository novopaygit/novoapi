<?php
if (!defined('__DAO_LOGINLOG')) define('__DAO_LOGINLOG', true); else return;
require_once 'class.DAO.php';

class DAO_Loginlog extends DAO {

	function __construct() {
		parent::__construct();
		$this->setTable('tbl_login_log');
	}

	function getUserlastlogIngo($loginid) {
		$sql = "SELECT * FROM ". $this->tbl ." WHERE loginid = ?  order by logno desc limit 1 ";
		return $this->dbh->get_row($sql, array($loginid));
	}

}
?>