<?php
if (!defined('__DAO_PAY_TEMP')) define('__DAO_PAY_TEMP', true); else return;
require_once 'class.DAO.php';

class DAO_PayTemp extends DAO {

	function __construct() {
		parent::__construct();
		$this->setTable('tbl_pay_temp');
	}

	function getInfo4Token($token_id) {
		$sql = "SELECT * FROM ". $this->tbl ." WHERE token_id = ?";
		return $this->dbh->get_row($sql, array($token_id));
	}

}
?>