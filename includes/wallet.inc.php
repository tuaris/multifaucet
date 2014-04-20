<?php
//Connects to the coin network

//Load wallet config
include(APPLICATION_CONFDIR . 'wallet.conf.php');

//Wallet Address Verification Utility
if(PAYMENT_GW_RPC_HOST){
	//Use a Hot Wallet
	$PAYMENT_GATEWAY = new HotWallet(PAYMENT_GW_RPC_USER, PAYMENT_GW_RPC_PASS, PAYMENT_GW_RPC_HOST, PAYMENT_GW_RPC_PORT, PAYMENT_GW_RPC_ENCR);
}
else{
	//Fall Back to Cold Wallet
	$PAYMENT_GATEWAY = new ColdWallet(ADDRESS_VERSION, $DB, PAYMENT_GW_DATAFILE);
}

?>