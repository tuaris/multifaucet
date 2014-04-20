<?php
//An Advanced Cold Wallet class that allows more control of the balance.  Used for RPC calls

class ColdWalletAdvanced extends ColdWallet{
	public function __construct($address_version, $DB, $file = "balance.txt"){
		parent::__construct($address_version, $DB, $file);
	}
	
	/*
	* All we are doing is exposing this method publicly.
	*/
	public function save_balance($value){
		parent::save_balance($value);
	}
}

?>