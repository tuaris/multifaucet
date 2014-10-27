<?php
/*
* Simple Faucet script
* You are completely free to use/modify this script in any way. Credit is not required.
*
* Generosity is always appreciated: 
*/

class Faucet {
	protected $SETTINGS;
	protected $DB;
	protected $PAYMENT_GATEWAY;

	protected $status = 0;

	protected $payout_amount = 0;
	protected $payout_address = "";

	protected $promo_code = "";
	protected $promo_payout_amount = 0;

	protected $balance = 0;
	
	protected $precision = 4;
	
	protected $STATS = array();

	public function __construct($DB, $config, $PAYMENT_GATEWAY) {
		if (!defined("SF_STATUS_OPERATIONAL")) {
			define("SF_STATUS_OPERATIONAL",100);
			define("SF_STATUS_PAYOUT_ACCEPTED",101);
			define("SF_STATUS_PAYOUT_AND_PROMO_ACCEPTED",102);
			//define("SF_STATUS_SUCCESS",102);

			define("SF_STATUS_RPC_CONNECTION_FAILED",200);
			define("SF_STATUS_MYSQL_CONNECTION_FAILED",201);
			define("SF_STATUS_PAYOUT_DENIED",202);
			define("SF_STATUS_INVALID_COIN_ADDRESS",203);
			define("SF_STATUS_PAYOUT_ERROR",204);
			define("SF_STATUS_CAPTCHA_INCORRECT",205);
			define("SF_STATUS_DRY_FAUCET",206);

			define("SF_STATUS_FAUCET_INCOMPLETE",300);
		}
		$defaults = array(
			"minimum_payout" => 0.01,
			"maximum_payout" => 10,
			"payout_precision" => $this->get_precision(),
			"payout_threshold" => 250,
			"payout_interval" => "7h",
			"user_check" => "both",
			"use_promo_codes" => true,
		);

		//Database, settings, and RPC
		$this->SETTINGS = $config;
		$this->DB = $DB;
		$this->PAYMENT_GATEWAY = $PAYMENT_GATEWAY;

		//TODO: Improve This
		$this->SETTINGS->config = array_merge($defaults, $config->config);

		// Check database and Balance
		// TODO: Tell the diffrence between a DB and WALLET connection error
		if (!$this->DB->connect_error && $this->refresh_balance()){
			if ($this->balance >= $this->SETTINGS->config["payout_threshold"]) {
				$this->status = SF_STATUS_OPERATIONAL;
			}
			else {
				$this->status = SF_STATUS_DRY_FAUCET;
			}
		}
		else{
			$this->status = SF_STATUS_MYSQL_CONNECTION_FAILED;
		}

		//Floating Point precision
		$this->set_precision($this->SETTINGS->config["payout_precision"]);
	}

	public function payout($cryptocoin_address, $ip_address, $promo_code = ""){
		if($this->validate_address($cryptocoin_address) && $this->check_payout_time($ip_address)){
			$this->calculate_promo($promo_code);
			$this->do_payout();
		}
	}

	protected function validate_address($cryptocoin_address){
		try {
			$cryptocoin_address = $this->DB->escape_string($cryptocoin_address);
			$result = $this->PAYMENT_GATEWAY->validateaddress($cryptocoin_address);
			// Check if valid address
			if ($result) {
				$this->payout_address = $cryptocoin_address;
			}
			else {
				$this->payout_address = "";
				$this->status = SF_STATUS_INVALID_COIN_ADDRESS;
			}
		}
		catch (Exception $e){
			$this->status = SF_STATUS_RPC_CONNECTION_FAILED;
		}

		return $result;
	}
	
	protected function check_payout_time($ip_address){
		$this->ip_address = $this->DB->escape_string($ip_address);
		$interval = "7 HOUR"; // hardcoded default interval if the custom interval is messed up
		$interval_value = intval(substr($this->SETTINGS->config["payout_interval"],0,-1));
		$interval_function = strtoupper(substr($this->SETTINGS->config["payout_interval"],-1));

		if ($interval_value >= 0 && ($interval_function == "H" || $interval_function == "M" || $interval_function == "D")) {
			$interval = $interval_value." ";
			switch ($interval_function) {
				case "M":
					$interval .= "MINUTE";
					break;
				case "H":
					$interval .= "HOUR";
					break;
				case "D":
					$interval .= "DAY";
					break;
			}
		}

		$user_check = " AND (";
		if ($this->SETTINGS->config["user_check"] == "ip_address" || $this->SETTINGS->config["user_check"] == "both") {
			$user_check .= " `ip_address` = '". $this->ip_address ."'";
		}
		if ($this->SETTINGS->config["user_check"] == "wallet_address" || $this->SETTINGS->config["user_check"] == "both") {
			$user_check .= ($this->SETTINGS->config["user_check"] == "both"?" OR":"")." `payout_address` = '". $this->payout_address ."'";
		}

		$user_check .= ")";
		$query = sprintf("SELECT `id` FROM `%spayouts` WHERE `timestamp` > NOW() - INTERVAL %s", 
					$this->DB->TB_PRFX,
					$interval.$user_check);
		$result = $this->DB->query($query);

		if ($row = @$result->fetch_assoc()) {
			 // user already received a payout within the payout interval
			$this->status = SF_STATUS_PAYOUT_DENIED;
			return false;
		}
		else {
			// All is good, payment will be given
			return true;
		}
	}

	protected function calculate_promo($promo_code){
		// check for valid promo code
		if ($this->SETTINGS->config["use_promo_codes"] && isset($promo_code)) {
			$promo_code = $this->DB->escape_string($promo_code);
			$query = sprintf("SELECT `minimum_payout`,`maximum_payout` 
							FROM `%spromo_codes` 
							WHERE `code` = '%s'", 
							$this->DB->TB_PRFX, 
							$promo_code);
			$result = $this->DB->query($query); 

			if ($promo = @$result->fetch_assoc()){
				$this->promo_code = $promo_code;
				$promo["minimum_payout"] = floatval($promo["minimum_payout"]);
				$promo["maximum_payout"] = floatval($promo["maximum_payout"]);
				if ($promo["minimum_payout"] >= $promo["maximum_payout"]) {
					$this->promo_payout_amount = $promo["maximum_payout"];
				}
				else {
					// calculate a random promo COIN amount
					$multiple = pow(10, $this->precision);
					$random = mt_rand($promo["minimum_payout"] * $multiple,$promo["maximum_payout"] * $multiple)/ $multiple;
					$this->promo_payout_amount = sprintf('%.' . $this->precision . 'f', $random);
				}
			}
		}
	}

	protected function do_payout(){
		// calculate a random COIN amount
		$multiple = pow(10, $this->precision);
		$random = mt_rand($this->SETTINGS->config["minimum_payout"] * $multiple,$this->SETTINGS->config["maximum_payout"] * $multiple) / $multiple; 
		$this->payout_amount = sprintf('%.' . $this->precision . 'f', $random);

		// insert the transaction into the payout log
		$query = sprintf("INSERT INTO `%spayouts` (`payout_amount`,`ip_address`,`payout_address`,`promo_code`,`promo_payout_amount`,`timestamp`)
			VALUES ('%s', '%s', '%s', '%s', '%s', NOW())", 
			$this->DB->TB_PRFX,
			$this->payout_amount,
			$this->ip_address,
			$this->payout_address,
			$this->promo_code,
			$this->promo_payout_amount);
		$result = $this->DB->query($query); 
		
		//So we can save the TXID
		$payout_id = $this->DB->insert_id;
		
		try {
			$txid = $this->send_coins();
		}
		catch(Exception $e){
			$this->status = SF_STATUS_RPC_CONNECTION_FAILED;
		}
		
		//Save the TXID
		if(!empty($txid)){
			$query = sprintf('UPDATE %spayouts SET txid = "%s"
						WHERE id = %s', 
						$this->DB->TB_PRFX,
						$txid,
						$payout_id);
			$this->DB->query($query); 
		}
	}
	
	protected function send_coins(){
		// UNlocks wallet if neseccary
		$this->PAYMENT_GATEWAY->walletunlock(); 

		if (isset($this->SETTINGS->config["_debug_test_mode"])){
			// test status
			$this->status = true ? $this->promo_payout_amount>0 ? SF_STATUS_PAYOUT_AND_PROMO_ACCEPTED : SF_STATUS_PAYOUT_ACCEPTED : SF_STATUS_PAYOUT_ERROR; 
		}
		else {
			// send the COIN
			try{
				$result = $this->PAYMENT_GATEWAY->sendtoaddress($this->payout_address, ($this->payout_amount + $this->promo_payout_amount));
			}
			catch(Exception $e){
				$result = null;
				$this->status = SF_STATUS_RPC_CONNECTION_FAILED;
			}
			//TODO: Improve This mess
			$this->status = !is_null($result) ? $this->promo_payout_amount>0 ? SF_STATUS_PAYOUT_AND_PROMO_ACCEPTED : SF_STATUS_PAYOUT_ACCEPTED : SF_STATUS_PAYOUT_ERROR;
		}

		// locks wallet if neseccary
		$this->PAYMENT_GATEWAY->walletlock();

		return $result;
	}
	
	public function refresh_balance(){
		try {
			$this->balance = $this->PAYMENT_GATEWAY->getbalance();
			return true;
		}
		catch(Exception $e){
			$this->status = SF_STATUS_RPC_CONNECTION_FAILED;
			return false;
		}
	}

	protected function load_stats(){
		$this->STATS['average_payout'] = number_format($this->payout_aggregate("AVG"), $this->precision);
		$this->STATS['smallest_payout'] = number_format($this->payout_aggregate("MIN"), $this->precision);
		$this->STATS['largest_payout'] = number_format($this->payout_aggregate("MAX"), $this->precision);
		$this->STATS['number_of_payouts'] = number_format($this->payout_aggregate("COUNT"));
		$this->STATS['total_payouts'] = number_format($this->payout_aggregate("SUM"), $this->precision);

		$this->STATS['total_payout'] = $this->STATS['total_payouts'];

		$this->STATS['balance'] = number_format($this->balance, $this->precision);
		$this->STATS['payout_amount'] = number_format($this->payout_amount, $this->precision);
		$this->STATS['payout_address'] = $this->payout_address;
		$this->STATS['promo_payout_amount'] = $this->promo_payout_amount;

		$this->STATS['minimum_payout'] = number_format($this->SETTINGS->config['minimum_payout'], $this->precision);
		$this->STATS['maximum_payout'] = number_format($this->SETTINGS->config['maximum_payout'], $this->precision);
		$this->STATS['payout_threshold'] = number_format($this->SETTINGS->config['payout_threshold'], $this->precision);
	}

	public function get_stats($refresh = false){
		//Loaded stats if nessesary or requested
		if(empty($this->STATS) || $refresh){$this->load_stats();}
		return $this->STATS;
	}
	
	public function status() {
		return $this->status;
	}

	public function set_precision($precision){
		//Must be an integer
		$precision = (int)$precision;
		// Must be between 1 and 8 inclusive.
		if ($precision > 0 && $precision < 9){
			$this->precision = $precision;
		}
	}

	public function get_precision(){
		return $this->precision;
	}

	// Payout aggregate functions, to make things easier.
	// Possible functions are:
	// AVG - average payout
	// SUM - total payout
	// MIN - smallest payout
	// MAX - largest payout
	// COUNT - number of payouts
	// See: http://dev.mysql.com/doc/refman/5.0/en/group-by-functions.html
	// TODO- add datetime periods.
	public function payout_aggregate($function = "AVG") {
		if ($this->status != SF_STATUS_MYSQL_CONNECTION_FAILED) {
			$query = sprintf("SELECT %s(`payout_amount`) 
					FROM `%spayouts`", 
					$this->DB->escape_string($function), 
					$this->DB->TB_PRFX);
			$result = $this->DB->query($query);
			if($result) {
				$row = $result->fetch_array(MYSQLI_NUM);
				return $row[0];
			}
		}
		return false;
	}


}
?>