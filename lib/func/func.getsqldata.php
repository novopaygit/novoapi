<?php 
	/**
	 * 20180404 최인석
	 * SQL에서 값을 가져와야되는 공용함수들 집합
	 */
	
	//노포페이 자체 비트코인화폐정보를 가져오는함수 
	function getCurrency(){
		$query = "select currency code,currency value from tbl_bas_currency order by sort_idx";
		return getSystemMap4Query($query);
	}

	function getSystemMap4Query($query, $no_val='') {
		
		
		global $dbp;
		$list = $dbp->get_all($query);
		$dbp->disconnect();
		$map = array();
		foreach ($list as $row) {
			$code = isset($row['code']) ? $row['code'] : $no_val;
			$map[$code] = $row['value'];
		}
		return $map;
	}
	/**
	 * 20180404 최인석
	 * 몰아이디를 받아서 mall 테이블의 정보를 반환한는함수
	 * @param  [type] $mall_id [mallID]
	 * @return [type]          [row 반환 ]
	 */
	function getMallUserInfo($mall_id) {
		
		
		global $dbp;
		$sql = "select mall_nm,user_nm,user_email from tbl_mall where mall_id ='".$mall_id."'";
		$row = $dbp->get_row($sql);
		$dbp->disconnect();
		
		return $row;
	}

 ?>