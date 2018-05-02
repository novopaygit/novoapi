<?php
include dirname(dirname(__FILE__)) .'/init.php';


	function get_otpcis($secret='') {
		require_once 'class.googleotp_cis.php';
		$ga = new PHPGangsta_GoogleAuthenticator();
		if ($secret == '') $secret = 'GM4DMODCMFSTCNRV';
		return $ga->getCode($secret);
	}

?>