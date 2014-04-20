<?php

//Simeple Wallet Interface

interface Wallet {
	public function getbalance();
	public function sendtoaddress($address, $amount);
	public function test();
	public function validateaddress($address);
	public function walletpassphrase($passphrase);
	public function walletunlock();
	public function walletlock();
}

?>