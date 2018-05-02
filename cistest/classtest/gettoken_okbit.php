<!DOCTYPE HTML>
<html>

<form action='gettoken.php' method='POST' >
Email<input type="text" name="email" value ="testaccount@ok-bit.com" style="width:300px;" /> <br/>
password <input type="text" name="password" value ="Okbittest0000!" style="width:300px;" /><br/>
OTP <input type="text" name="otpcode" value ="" style="width:300px;" /><br/>


<button type='submit'>Excute </button>
</form>

<pre>
<?php 
	if (isset($_POST['email']) ){
		

		include '../cisinit.php';
		
		
		
		$email =$_POST['email'];
		$password =$_POST['password'];
		if ($_POST['otpcode'] == '' ){
			$otpcode = get_otpcis();
		}else {
			$otpcode =$_POST['otpcode'];
		}
		echo "토큰받기 (OTP : ".$otpcode.")";
		echo "<br/>";

		


	    require CLS_ROOT.'/coinexch/okbit/class.OK-BIT.php';
		$okbit = new OkBitClient('okbittestclient', 'wVJ09XebUkyEZ8n5');
		$result = $okbit->token_client($email,$password,$otpcode);
		//$list = $okbit->getResData();
		//$rescode = $okbit->getResCode();
		$resbody = $okbit->getResBody();

		
		
		//var_dump($list);
		var_dump(json_decode($resbody));
	}
	
?>
</pre>
</html>