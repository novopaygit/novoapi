<?php
if (!defined('__DAO_MALL')) define('__DAO_MALL', true); else return;
require_once 'class.DAO.php';

class DAO_Mall extends DAO {

	function __construct() {
		parent::__construct();
		$this->setTable('tbl_mall');
	}

	function getMallInfo($mall_id) {
		$sql = "SELECT * FROM ". $this->tbl ." WHERE mall_id = ?";
		return $this->dbh->get_row($sql, array($mall_id));
	}

	 // mysql 용 password 함수적용 값  반환 
	function getMySqlPassword($password){
		$sql = "SELECT password( ? ) as password ";
		return $this->dbh->get_row($sql, array($password));

	}

}
?>