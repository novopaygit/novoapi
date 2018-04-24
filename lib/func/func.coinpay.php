<?php
// ============================================================================================= Login
// ---------------------------------------------------------------------------------
function isCoinPayLogin() {
	if (!isset($_SESSION['pay_user_id'])) return false;
	if (!$_SESSION['pay_user_id']) return false;
	return true;
}
// ============================================================================================= Test User
// ---------------------------------------------------------------------------------
/*function getCoinPayTestUser($k) {
	instanceExchange();
	switch ($k) {
		case 'user_id'    : return _OKBIT_USER_EMAIL_;
		case 'user_pw'    : return _OKBIT_USER_PASSWD_;
		case 'otp_secret' : return _OKBIT_OTP_SECRET_;
		default : return '';
	}
}
*//*
function NovoEncrypt($str, $secret_key='secret key')
{
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_key), 0, 16)    ;

    return str_replace("=", "", base64_encode(
                 openssl_encrypt($str, "AES-128-CBC", $key, 0, $iv))
    );
}


function NovoDecrypt($str, $secret_key='secret key')
{
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_key), 0, 16);

    return openssl_decrypt(
            base64_decode($str), "AES-128-CBC", $key, 0, $iv
    );
}*/

?>