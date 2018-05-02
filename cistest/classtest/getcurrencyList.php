<!DOCTYPE HTML>
<html>

<form action='getcurrencyList.php' method='POST' >
Token<input type="text" name="token" value ="인자값필요없음" style="width:300px;" /> <br/>


<button type='submit'>Excute </button>
</form>

<pre>
<?php 
	if (isset($_POST['token']) ){
		

		$token =$_POST['token'];
		
		echo "사용가능 화폐  (토큰 : ".$token." )";
		echo "<br/>";

		include '../cisinit.php';


	    //입금주소 
	    include CLS_ROOT.'/class.CoinExch.php';	
		$coinExch = new CoinExch('testmall');	
		$param=array();
		$param['currency'] ='';
		//$param['password'] ='Okbittest0000!';
		//$param['otpcode'] ='114175';
		
		$res = $coinExch->getConstants($param);

		
	    
	    $return = $res['data'];    
	    
	    var_dump($return);
	}
	
?>
</pre>
</html>