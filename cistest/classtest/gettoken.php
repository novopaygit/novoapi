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

		


	    //입금주소 
	    include CLS_ROOT.'/class.CoinExch.php';	
		$coinExch = new CoinExch('testmall');	
		$param=array();
		//$res = $coinExch->getConstants();
		//
		//		
		$param['email'] =$email;;
		$param['password'] =$password;
		$param['otpcode'] =$otpcode;
		//$param['password'] ='Okbittest0000!';
		//$param['otpcode'] ='114175';
		
		$res = $coinExch->getClientToken($param);

		
	    
	    $return = $res['data'];    
	    
	    var_dump($return);
	}
	
?>
</pre>
</html>