<!DOCTYPE HTML>
<html>

<form action='curl_cryptokey.php' method='POST' >
Token<input type="text" name="token" value ="bf942e48-6698-44d6-8665-c6f606212071" style="width:300px;" /> <br/>


<button type='submit'>Excute </button>
</form>
<pre>
<?php 	
		if (isset($_POST['token']) ){
			/************************************************************************
			 * Post (okbit aes 암호화 복호화함) 		 		
			/************************************************************************/
			

			$token =$_POST['token'];
	        $header = array(
						"Accept: application/json;charset=UTF-8",
						"Content-Type: application/json",
						"Authorization: Bearer ".$token//bf942e48-6698-44d6-8665-c6f606212071"
					);
	        
	        $send_data= array ();

			$send_data['clientId'] ="okbittestclient";
			$send_data['clientSecret'] =encrypt("wVJ09XebUkyEZ8n5","wVJ09XebUkyEZ8n5");
			

			$send_json = json_encode($send_data);
			

	        $endpoint ='https://testapi.ok-bit.com//api/crypto/key';

	        $curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $endpoint);			
				curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			
				curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($curl, CURLOPT_HEADER, true);    //heder 필요할경우 
				curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
				curl_setopt($curl, CURLINFO_HEADER_OUT, true);
				curl_setopt($curl, CURLOPT_POST, true); //POST일경우 활성화			
				curl_setopt($curl, CURLOPT_POSTFIELDS, $send_json);
				$response = curl_exec($curl);
				curl_close($curl);


				$req_headers = '';
				$http_code   = '';
				list($res_headers, $res_body) = get_http_response($response);

				$dataTmp = (array) json_decode($res_body);
				$dataTmp2 = (array) decrypt($dataTmp["data"],'wVJ09XebUkyEZ8n5');

				echo "AES암호키받기 (토큰 : ".$token." )";
				echo "<br/>";
				echo "<br/>";

				$result_data = (array) json_decode($dataTmp2[0]);
				var_dump($result_data);
				//$data = decrypt($dataTmp2,'wVJ09XebUkyEZ8n5');


				//var_dump($data);



			 
		}
		

		function decrypt($input, $key='') {
			if (substr($input, 0, 1) == '{') return $input;
			if ($key == '') $key = 'wVJ09XebUkyEZ8n5';
			$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
			$decrypted= mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128,
				$key,
				base64_decode($input),
				MCRYPT_MODE_ECB
				//, $iv
			);
			$dec_s = strlen($decrypted);
			$padding = ord($decrypted[$dec_s-1]);
			$data = substr($decrypted, 0, -$padding);
			return $data;
		}

		function encrypt($input, $key='') {
			if ($key == '') $key = 'wVJ09XebUkyEZ8n5';
			$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
			$input = pkcs5_pad($input, $size);
			$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
			$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
			mcrypt_generic_init($td, $key, $iv);
			$data = mcrypt_generic($td, $input);
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
			$data = base64_encode($data);
			return $data;
		}
		function pkcs5_pad ($text, $blocksize) {
			$pad = $blocksize - (strlen($text) % $blocksize);
			return $text . str_repeat(chr($pad), $pad);
		}

		function get_http_response($response) {
			$curl_result = $response;
			$rawheader = $body = '';
			if (strpos($curl_result, "\r\n\r\n") !== false) {
				list($rawheader, $body) = explode("\r\n\r\n", $curl_result, 2);
			} else {
				$body = $curl_result;
			}
			$header_array = array();
			if ($rawheader) {
				$header_rows = explode("\n",$rawheader);
				for($i=0;$i<count($header_rows);$i++){
					$fields = explode(":",$header_rows[$i]);

					if($i != 0 && !isset($fields[1])){//carriage return bug fix.
						if(substr($fields[0], 0, 1) == "\t"){
							end($header_array);
							$header_array[key($header_array)] .= "\r\n\t".trim($fields[0]);
						}
						else{
							end($header_array);
							$header_array[key($header_array)] .= trim($fields[0]);
						}
					}
					else{
						$field_title = trim($fields[0]);
						if (!isset($header_array[$field_title])){
							$val = isset($fields[1]) ? $fields[1] : '';
							$header_array[$field_title]=trim($val);
						}
						else if(is_array($header_array[$field_title])){
								$header_array[$field_title] = array_merge($header_array[$fields[0]], array(trim($fields[1])));
							}
						else{
							$header_array[$field_title] = array_merge(array($header_array[$fields[0]]), array(trim($fields[1])));
						}
					}
				}
			}
			return array($header_array, $body);
		}

?>
</pre>
</html>