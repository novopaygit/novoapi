<!DOCTYPE HTML>
<html>

<form action='getinputaddress_okbit.php' method='POST' >
token		<input type="text" name="token" value ="aa13217c-a4d3-440c-adb9-4908cee31868" style="width:300px;" /> <br/>
currency	<input type="text" name="currency" value ="BTC" style="width:300px;" /><br/>
amount 		<input type="text" name="amount" value ="0.2345" style="width:300px;" /><br/>
price 		<input type="text" name="price" value ="25000" style="width:300px;" /><br/>
autosell	<input type="text" name="autosell" value ="true" style="width:300px;" /><br/>

<button type='submit'>Excute </button>
</form>

<pre>
<?php 
	if (isset($_POST['token']) ){

	

		include '../cisinit.php';

		$token =$_POST['token'];
		$currency =$_POST['currency'];
		$amount =$_POST['amount'];
		$price =$_POST['price'];
		$autosell =$_POST['autosell'];
		echo "입금주소요청결과okbit (토큰 : ".$token." )";
		echo "<br/>";

	    require CLS_ROOT.'/coinexch/okbit/class.OK-BIT.php';
		$okbit = new OkBitClient('okbittestclient', 'wVJ09XebUkyEZ8n5');
		$result = $okbit->pubilc_symbols();
		//$list = $okbit->getResData();
		//$rescode = $okbit->getResCode();
		$resbody = $okbit->getResBody();
		
		//var_dump($list);
		var_dump(json_decode($resbody));
	}
	
?>
</pre>
</html>