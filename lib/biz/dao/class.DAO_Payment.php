<?php
if (!defined('__DAO_PAYMENT')) define('__DAO_PAYMENT', true); else return;
require_once 'class.DAO.php';

class DAO_Payment extends DAO {

	function __construct() {
		parent::__construct();
		$this->setTable('tbl_payment');
	}

	function getInfo4Token($token_id) {
		$sql = "SELECT * FROM ". $this->tbl ." WHERE token_id = ?";
		return $this->dbh->get_row($sql, array($token_id));
	}

	function getInfo4PayTempNo($mall_id, $pay_temp_no) {
		$sql = "SELECT * FROM ". $this->tbl ." WHERE mall_id = ? AND pay_temp_no = ?";
		return $this->dbh->get_row($sql, array($mall_id, $pay_temp_no));
	}

	function getInfo4OrderNo($mall_id, $order_id) {
		$sql = "SELECT * FROM ". $this->tbl ." WHERE mall_id = ? AND order_id = ?";
		return $this->dbh->get_row($sql, array($mall_id, $order_id));
	}


	function getNewTID() {
		$base = 'T'. date('ymd');
		$sql = "SELECT SUBSTRING(MAX(tid), 8) AS tid FROM ". $this->tbl ." WHERE tid LIKE '". $base ."%'";
		$row = $this->dbh->get_row($sql);
		if ($row) {
			$num = intval($row['tid']) + 1;
		} else {
			$num = 1;
		}
		return $base . str_pad($num, 10, '0', STR_PAD_LEFT);
	}
	/**
	 * 20180410 최인석
	 * Mall 에서 결제취소 API호출시 사용됨 
	 * cancel_status ='0' 인것만가져오는이유는 결제취소할수있는 상태값이 0이기때문
	 *   -> 0:최초결제시상태값 , 1:정산전결제취소   2: 정산후결제취소
	 * @param  [type] $mall_id     [몰아이디 ]
	 * @param  [type] $order_id    [쇼핑몰아이디]
	 * @param  [type] $tid         [노보페이 거래번호 ]
	 * @param  [type] $price       [쇼핑몰 상품가격]
	 * @return [type]              [레코드수 및 해당 pay_no 반환 ]
	 */
	function getInfoCancelCheck($mall_id,$order_id, $tid,$price) {
		$sql = "SELECT count(*) row_cnt FROM ". $this->tbl ." WHERE cancel_status='0' and  mall_id =? and order_id = ? and tid =? and price =? ";
		return $this->dbh->get_row($sql, array($mall_id, $order_id,$tid, $price));
	}
	function getInfoCancelPayno($mall_id,$order_id, $tid,$price) {
		$sql = "SELECT pay_no FROM ". $this->tbl ." WHERE cancel_status='0' and  mall_id =? and order_id = ? and tid =? and price =? ";
		return $this->dbh->get_row($sql, array($mall_id, $order_id,$tid, $price));
	}


}
?>