<?php
if (!defined('__DAO_TOKEN')) define('__DAO_TOKEN', true); else return;
require_once 'class.DAO.php';

class DAO_Token extends DAO {

	function __construct() {
		parent::__construct();
		$this->setTable('tbl_token');
		$this->removeExpiredToken();
	}

	function getTokenInfo($token) {
		$sql = "SELECT * FROM ". $this->tbl ." WHERE token_id = ?";
		return $this->dbh->get_row($sql, array($token));
	}

	function removeExpiredToken() {
		$query = array();
		$query[] = "DELETE FROM ". $this->tbl;
		$query[] = "WHERE expire_dt < '". date('Y-m-d H:i:s', time() - 86400)."'";
		$query[] = "  AND token_id NOT IN (";
		$query[] = "        SELECT token_id FROM tbl_pay_temp";
		$query[] = "    )";
		$sql = implode(PHP_EOL, $query);
		$this->dbh->execute($sql);
	}

}
?>