
	
<?php 	
		
		/************************************************************************
		 * Post (okbit aes 암호화 복호화함) 		 		
		/************************************************************************/
		
        $header = array(
					"Accept: application/json;charset=UTF-8",
					"Content-Type: application/json",
					"Authorization: Bearer 5e1398ff-ad0e-44bd-a3c8-d5f7c66ae15e"
				);
        
        $send_data= array ();

		$send_data['clientId'] ="okbittestclient";
		$send_data['clientSecret'] ="wVJ09XebUkyEZ8n5";
		$send_data['email'] ="testaccount@ok-bit.com";
		$send_data['otpCode'] ="303435";
		$send_data['password'] ="Okbittest0000!";

		$send_json = json_encode($send_data);
		

        $endpoint ='https://testapi.ok-bit.com/api/payment/balance?currency=BTC';

        $curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $endpoint);			
			curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
			//curl_setopt($curl, CURLOPT_HEADER, true);    //heder 필요할경우 
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			curl_setopt($curl, CURLINFO_HEADER_OUT, true);
			//curl_setopt($curl, CURLOPT_POST, true); //POST일경우 활성화			
			//curl_setopt($curl, CURLOPT_POSTFIELDS, $send_json);
			$response = curl_exec($curl);
			curl_close($curl);

			$strtmp = decrypt($response,'hcNAXvrzbbAVESgC');
			var_dump($strtmp);



		 function decrypt($input, $key='') {
			if (substr($input, 0, 1) == '{') return $input;
			if ($key == '') $key = _OKBIT_CLIENT_SECRET_;
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

		/************************************************************************
		 * Post 		 		
		/************************************************************************/
		/*
        $header = array(
					"Accept: application/json;charset=UTF-8",
					"Content-Type: application/json"
				);
        
        $send_data= array ();

		$send_data['clientId'] ="okbittestclient";
		$send_data['clientSecret'] ="wVJ09XebUkyEZ8n5";
		$send_data['email'] ="testaccount@ok-bit.com";
		$send_data['otpCode'] ="303435";
		$send_data['password'] ="Okbittest0000!";

		$send_json = json_encode($send_data);
		

        $endpoint ='https://testapi.ok-bit.com/api/auth/token/client';

        $curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $endpoint);			
			curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
			//curl_setopt($curl, CURLOPT_HEADER, true);    //heder 필요할경우 
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			curl_setopt($curl, CURLINFO_HEADER_OUT, true);
			curl_setopt($curl, CURLOPT_POST, true); //POST일경우 활성화			
			curl_setopt($curl, CURLOPT_POSTFIELDS, $send_json);
			$response = curl_exec($curl);
			curl_close($curl);
			var_dump($response);
		*/
       
		/************************************************************************
		 * Get방식 		 		
		/************************************************************************/
		/*
        $header = array(
					"Novowave: ". 'test',
					"Test: cis"
				);
        $send_data='';

        $endpoint ='https://testapi.ok-bit.com/api/public/symbols';

        $curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $endpoint);			
			curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
			//curl_setopt($curl, CURLOPT_HEADER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			curl_setopt($curl, CURLINFO_HEADER_OUT, true);
			//curl_setopt($curl, CURLOPT_POST, true); //POST일경우 활성화			
			//curl_setopt($curl, CURLOPT_POSTFIELDS, $send_data);
			$response = curl_exec($curl);

			var_dump($response);
		*/
		/************************************************************************
		 * 소켓방식 		 		
		/************************************************************************/
		
		/*
			$port = '443'; //https 포트 

			$host = $url_info['host'];
			$socket = @fsockopen($host, $port, $errno, $errstr, 5);

			//warning message disable '@'
			if (!$socket) {
				$resp_txt = '{"result_cd":"9000", "err_msg":"Socket Connect Error:'. $errstr.'"}';
				return $resp_txt;
			}

			fwrite($socket, "POST ". $path ." HTTP/1.1\r\n");
			fwrite($socket, "Host: ".$host.":".$port."\r\n");
			fwrite($socket, "NovoPay-ID: ". $this->EXEC_AUTH['NOVOPAY_ID'] ."\r\n");
			fwrite($socket, "Secret-Key: ". $this->EXEC_AUTH['SECRET_KEY'] ."\r\n");
			fwrite($socket, "Content-type: application/x-www-form-urlencoded\r\n");
			fwrite($socket, "Content-length: ".strlen($send_data)."\r\n");
			*/
			//fwrite($socket, "Accept: */*\r\n");
			/*fwrite($socket, "\r\n");
			fwrite($socket, $send_data."\r\n");
			fwrite($socket, "\r\n");
			$resp_txt = $this->convertSock($socket);
			fclose($socket);

		*/
	
		
		
?>
