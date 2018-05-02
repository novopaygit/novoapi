<!DOCTYPE HTML>
<html>

<form action='getinputaddress.php' method='POST' >
Token<input type="text" name="token" value ="aa13217c-a4d3-440c-adb9-4908cee31868" style="width:300px;" /> <br/>
CURRENCY <input type="text" name="currency" value ="BTC" style="width:300px;" /><br/>
amount <input type="text" name="amount" value ="0.2345" style="width:300px;" /><br/>
price <input type="text" name="price" value ="25000" style="width:300px;" /><br/>
autosell<input type="text" name="autosell" value ="true" style="width:300px;" /><br/>

<button type='submit'>Excute </button>
</form>

<pre>
<?php 
	if (isset($_POST['token']) ){
		

		$token =$_POST['token'];
		$currency =$_POST['currency'];
		$amount =$_POST['amount'];
		$price =$_POST['price'];
		$autosell =$_POST['autosell'];
		echo "입금주소요청결과 (토큰 : ".$token." )";
		echo "<br/>";

		include '../cisinit.php';


	    //입금주소 
	    include CLS_ROOT.'/class.CoinExch.php';	
		$coinExch = new CoinExch('testmall');	
		$param=array();
		//$res = $coinExch->getConstants();
		//
		//
		$param['access_token'] =$token;
		$param['currency'] =$currency;
		$param['amount'] =$amount;;
		$param['price'] =$price;
		$param['autosell'] =$autosell;
		//$param['password'] ='Okbittest0000!';
		//$param['otpcode'] ='114175';
		
		$res = $coinExch->getDepositAddress($param);

		
	    
	    $return = $res['data'];    
	    
	    var_dump($return);
	}
	
?>
</pre>
</html>