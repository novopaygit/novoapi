

<?php
/**
 * 20180410 최인석 
 * 결제 된내역을 취소하는함수
 *  pay_token(노보토큰), tid,pay_reqid,취소할금액 를 파라메터로받아서
 *  일치하는지확인후 이상업으면 
 *  tbl_payment 테이블에 cancel_date 와 status 값을 없데이트한다.
 *  필요에따라서 log값을 남기자
 
 
 */

include 'init.php';

$order_id = requestapi('order_id');
$pay_tid = requestapi('pay_tid');
$price = requestapi('price');

// 해당결제건이 1개만  존재하는지체크 
$daoPayment = instanceDAO('Payment');
//errorapi('9014', $novopay_id.'/'.$order_id.'/'.$pay_tid.'/'.$price);	
$resultcnt = $daoPayment->getInfoCancelCheck($novopay_id,$order_id,$pay_tid,$price);
if ($resultcnt['row_cnt'] != 1 ){
	errorapi('9014', '결제취소실패(notExists)');	
}
$resultpayno = $daoPayment->getInfoCancelPayno($novopay_id,$order_id,$pay_tid,$price);


//DB에 필드업데이트 
$payment_data = array(
	  'cancel_status' => '1'     // 나중에 정상여부에따라 2가되야됨 ( 1: 정산전반품 2: 정산후반품 )	
	, 'cancel_dt' => date('Y-m-d H:i:s')
	, 'mod_dt' => date('Y-m-d H:i:s')
);
$result = $daoPayment->update($payment_data, 'pay_no = '. $resultpayno['pay_no']);

if (!$result) {
	errorapi('9014', '결제취소실패');
}

successapi();

?>