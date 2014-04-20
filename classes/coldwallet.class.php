<?php
//Offline Wallet Object


class ColdWallet implements Wallet  {
	// The coin info (balence for now) is stored in a text file
	protected $DF;
	protected $DB;
	protected $ADDRESS_VALIDATOR;
	
	public function __construct($address_version, $DB, $file = "balance.txt"){
		$this->DB = $DB;
		
		$file_info = pathinfo($file);
		//Might cuase issues for "/", but that should never be used anyway
		$this->DF = $file_info['dirname'] . DIRECTORY_SEPARATOR . $this->DB->TB_PRFX . $file_info['basename'];
		
		//Coin Address Verification Utility
		$this->ADDRESS_VALIDATOR = new CoinAddressValidator($address_version);
		
		$this->verify_db_integrity();
	}

	public function getbalance(){
		$balance = 0;
		if(!file_exists($this->DF)){
			$this->save_balance($balance);
		}
		$info = explode(",", file_get_contents($this->DF));
		$balance = $info[0];
		return $balance;
	}

	protected function save_balance($value){
		$value = $value <0 ?: $value; //Prevent it from going negative
		file_put_contents($this->DF, implode(",", array($value)));
	}

	public function sendtoaddress($address, $amount){
		//Deduct from Balance and save
		$balance = $this->getbalance();
		$balance = $balance - $amount;
		$this->save_balance($balance);
		
		//Insert Record in pending payments table
		$query = sprintf('INSERT INTO %spending_payments (payout_address, payout_amount)
						VALUES("%s", %s)', 
						$this->DB->TB_PRFX,
						$address,
						$amount);
		$this->DB->query($query);
						
		//Return a temporary TXID, the real TXID will be filled in when the payment is actually sent by another process
		return "COLD_WALLET_PENDING_TXID_" . $this->DB->insert_id;
	}

	public function test(){
		//Checks readabilty
		$this->getbalance();
		//Checks writability
		if(!@touch($this->DF)){throw new Exception("File is not writable");}
	}
	
	public function walletpassphrase($passphrase){
		//DO nothing
	}
	public function walletunlock(){
		//DO nothing
	}
	public function walletlock(){
		//DO nothing
	}

	public function validateaddress($address){
		return $this->ADDRESS_VALIDATOR->Check($address);
	}
	
	//Makes sure the needed database tables exists
	private function verify_db_integrity(){
		//Check if the required table exists (only one at the moment)
		$query = sprintf('SHOW TABLES LIKE "%spending_payments"', $this->DB->TB_PRFX);
		$result = $this->DB->query($query);
		
		if ($result->num_rows <= 0){
			//Needs to be created
			$query = sprintf("CREATE TABLE `%spending_payments` (
						`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
						`payout_address` VARCHAR(50) NOT NULL,
						`payout_amount` FLOAT NOT NULL DEFAULT '0',
						`created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
						PRIMARY KEY (`id`)
					)
					COLLATE='latin1_swedish_ci'
					ENGINE=MyISAM", $this->DB->TB_PRFX);
			$this->DB->query($query);
		}
	}
}
?>