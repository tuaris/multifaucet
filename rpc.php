<?php require_once('includes/bootstrap.inc.php'); ?>
<?php require_once("classes/advancedcoldwallet.class.php"); ?>
<?php require_once("classes/FaucetRPC.class.php"); ?>
<?php require_once("classes/jsonRPCServer.class.php"); ?>
<?php

//Requires Authentication
$username = null;
$password = null;

//Get Username and Password
if (isset($_SERVER['PHP_AUTH_USER'])) {
	//Try this first
	$username = $_SERVER['PHP_AUTH_USER'];
	$password = $_SERVER['PHP_AUTH_PW'];
} 
elseif (isset($_SERVER['HTTP_AUTHENTICATION'])) {
	// Then try what most other servers might do
	if (strpos(strtolower($_SERVER['HTTP_AUTHENTICATION']),'basic')===0){
		list($username,$password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
	}
}

//Check Login
if (is_null($username)) {
	header('WWW-Authenticate: Basic realm="' . APPLICATION_NAME . ' RPC"');
	header('HTTP/1.0 401 Unauthorized');
	echo '401 Unauthorized';
	die();
} 
else {
	if($username != PAYMENT_GW_RPC_USER || $password != PAYMENT_GW_RPC_PASS){
		// Incorrect Login
		header('HTTP/1.0 401 Unauthorized');
		echo '401 Unauthorized';
		die();
	}
}

//This is only accetable if the cold wallet in in use
if(PAYMENT_GW_DATAFILE == ""){
	header('HTTP/1.0 501 Not Implemented');
	die();
}

//We good from this point forward
$COLD_WALLET = new ColdWalletAdvanced(ADDRESS_VERSION, $DB, PAYMENT_GW_DATAFILE);
$RPC = new FaucetRPC($DB, $COLD_WALLET);
jsonRPCServer::handle($RPC) or header('HTTP/1.0 400 Bad Request');
?>