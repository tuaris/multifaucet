<?php
//Allows the faucet to be managed remotly.  Usefull when using the Cold Wallet

class FaucetRPC{
	protected $DB;
	protected $COLD_WALLET;
	
	public function __construct($DB, $COLD_WALLET) {
		$this->DB = $DB;
		$this->COLD_WALLET = $COLD_WALLET;
	}

	/*
	* Returns an array of all the unpaid transactions
	* array(id, payout_address, payout_amount, created_date, ip_address, promo_code, promo_payout_amount, timestamp, lastupdate)
	*/
	public function getPendingTx(){
		$query = sprintf("SELECT p.id, p.payout_address, p.payout_amount, p.created_date, o.ip_address, o.promo_code, o.promo_payout_amount, o.timestamp, o.lastupdate
						FROM %spending_payments p, %spayouts o WHERE CONCAT('COLD_WALLET_PENDING_TXID_', p.id) = o.txid",
			$this->DB->TB_PRFX,
			$this->DB->TB_PRFX
		);
		$result = $this->DB->query($query);

		//Combine into one big array
		for ($all_rows = array(); $current_row = $result->fetch_assoc();) {
			$all_rows[] = $current_row;
		}

		return $all_rows;
	}
	
	/*
	* Updates a payout record with the transaction ID and deletes the pending payment
	* $id is the id of the pending payment obtained from getPendingTx()
	* $txid is the transaction ID from the crypto wallet.
	*
	*/
	public function setPaidTx($id, $txid){
		if(empty($id) || empty($txid)){throw new Exception("All fields required");}
		//First delete the pending payment
		$query = sprintf("DELETE FROM %spending_payments WHERE id = %s",
						$this->DB->TB_PRFX,
						$this->DB->real_escape_string($id)
		);
		$this->DB->query($query);

		//Next set the txid in the payouts table
		$query = sprintf('UPDATE %spayouts SET txid = "%s" WHERE txid = "COLD_WALLET_PENDING_TXID_%s"', 
						$this->DB->TB_PRFX,
						$this->DB->real_escape_string($txid),
						$this->DB->real_escape_string($id)
		);
		$this->DB->query($query);
		return true;
	}
	
	/*
	* Adds funds to the cold wallet
	* $amount: amount to be added
	*
	*/
	public function addFunds($amount = 0){
		$total = $this->COLD_WALLET->getbalance() + $amount;
		$this->setFunds($total);
		return $this->getFunds();
	}
	/*
	* Deducts funds to the cold wallet
	* $amount: amount to be subtracted (note if the total is less than 0, 0 is used)
	*
	*/
	public function deductFunds($amount = 0){
		$total = $this->COLD_WALLET->getbalance() - $amount;
		$this->setFunds($total);
		return $this->getFunds();
	}
	/*
	* Sets funds to the given amount
	* $amount: amount to be set
	*
	*/
	public function setFunds($amount = 0){
		$this->COLD_WALLET->save_balance($amount);
		return $this->getFunds();
	}
	/*
	* Gets the current available funds
	*
	*/
	public function getFunds($amount = 0){
		return $this->COLD_WALLET->getbalance();
	}
}

?>