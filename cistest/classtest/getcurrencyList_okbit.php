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


	   require CLS_ROOT.'/coinexch/okbit/class.OK-BIT.php';
		$okbit = new OkBitClient('okbittestclient', 'wVJ09XebUkyEZ8n5');
		$result = $okbit->payment_deposit($token, $currency, $amount, $price, $autosell);
		//$list = $okbit->getResData();
		//$rescode = $okbit->getResCode();
		$resbody = $okbit->getResBody();
		
		//var_dump($list);
		var_dump(json_decode($resbody));
	}
	
?>
</pre>
</html>