<?php
//Simple Hot Wallet

class HotWallet implements Wallet {

	private $RPC;
	private $ENCRYPTION_PASSWORD;

	public function __construct($username, $password, $hostname, $port, $encrypt_pass = "", $protocol = 'http'){
		$this->RPC = new jsonRPCClient($protocol . '://' .urlencode($username).':'.urlencode($password).'@'.urlencode($hostname).':'.urlencode($port));
		$this->ENCRYPTION_PASSWORD = $encrypt_pass;
	}

	public function getbalance(){
		$balance = 0;
		$balance = $this->rpc_call("getbalance");
		return $balance;
	}
	public function sendtoaddress($address, $amount){
		return $this->rpc_call("sendtoaddress",array($address, $amount));
	}
	public function test(){
		return $this->rpc_call("getinfo");
	}
	public function validateaddress($address){
		$result = false;
		$validation = $this->rpc_call("validateaddress", array($address));
		$result = $validation["isvalid"];

		return $result;
	}
	public function walletpassphrase($passphrase){
		$this->rpc_call("walletpassphrase",array($passphrase,5)); 
	}
	public function walletunlock(){
		// unlock wallet if neseccary
		if (!empty($this->ENCRYPTION_PASSWORD)){
			$this->walletpassphrase($this->ENCRYPTION_PASSWORD);
		}
	}
	public function walletlock(){
		// lock wallet if neseccary
		if (!empty($this->ENCRYPTION_PASSWORD)) {
			$this->rpc_call("walletlock"); 
		}
	}

	protected function rpc_call($method, $params = array()) {
		if(!empty($this->RPC)){
			return @$this->RPC->__call($method, $params);
		}
		else {
			return null;
		}
	}
}