<?php
// =====================================================================
// ---------------------------------------------------------
include 'init.php';

// =====================================================================
// --------------------------------------------------------- request values

// --------------------------------------------------------- check values


// --------------------------------------------------------- process
// get token
$svcToken = instanceSVC('Token');
while (true) {
	$token = $svcToken->getToken();
	if ($svcToken->checkTokenID($token)) break;
}
// save token
$data = array(
	'token_id' => $token,
	'mall_id'  => $novopay_id
);
$svcToken->saveToken($data);

// --------------------------------------------------------- make response
$res = array(
	'token' => $token
);

// --------------------------------------------------------- response



successapi($res);

// =====================================================================
// ---------------------------------------------------------
?>