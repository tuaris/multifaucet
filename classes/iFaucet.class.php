<?php
/*
 * Improved Faucet Class 1.0
 * Complete Re-Write of Simple Faucet Class
 * 
 * $Id$
 * 
 * Software License Agreement (BSD License)
 * 
 * Copyright (c) 2014, The Daniel Morante Company, Inc.
 * All rights reserved.
 * 
 * Redistribution and use of this software in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 * 
 *   Redistributions of source code must retain the above
 *   copyright notice, this list of conditions and the
 *   following disclaimer.
 * 
 *   Redistributions in binary form must reproduce the above
 *   copyright notice, this list of conditions and the
 *   following disclaimer in the documentation and/or other
 *   materials provided with the distribution.
 * 
 *   Neither the name of The Daniel Morante Company, Inc. nor the names of its
 *   contributors may be used to endorse or promote products
 *   derived from this software without specific prior
 *   written permission of The Daniel Morante Company, Inc.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
 * PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

class iFaucet {
	protected $SETTINGS;
	protected $DB;
	protected $PAYMENT_GATEWAY;

	protected $status = 0;

	protected $payout_amount = 0;
	protected $payout_address = "";
	protected $address_isvalid = false;

	protected $ip_address = "";
	protected $ip_isvalid = false;

	protected $can_collect = false;
	protected $wait_time = 0;

	protected $promo_code = "";
	protected $promo_payout_min = 0;
	protected $promo_payout_max = 0;
	protected $promo_payout_amount = 0;

	protected $balance = 0;
	
	protected $precision = 4;
	
	protected $STATS = array();

	public function __construct($DB, $SETTINGS, $PAYMENT_GATEWAY) {
		if (!defined("FAUCET_STATUS_OPERATIONAL")) {
			define("FAUCET_STATUS_OPERATIONAL",100);
			define("FAUCET_STATUS_PAYOUT_ACCEPTED",101);
			define("FAUCET_STATUS_PAYOUT_AND_PROMO_ACCEPTED",102);

			define("FAUCET_STATUS_RPC_CONNECTION_FAILED",200);
			define("FAUCET_STATUS_MYSQL_CONNECTION_FAILED",201);
			define("FAUCET_STATUS_PAYOUT_DENIED",202);
			define("FAUCET_STATUS_INVALID_COIN_ADDRESS",203);
			define("FAUCET_STATUS_PAYOUT_ERROR",204);
			define("FAUCET_STATUS_CAPTCHA_INCORRECT",205);
			define("FAUCET_STATUS_DRY_FAUCET",206);
			define("FAUCET_STATUS_INVALID_PROMO_CODE",207);
			define("FAUCET_STATUS_INVALID_IP_ADDRESS",208);

			define("FAUCET_STATUS_FAUCET_INCOMPLETE",300);
		}

		//Database, settings, and RPC
		$this->SETTINGS = $SETTINGS;
		$this->DB = $DB;
		$this->PAYMENT_GATEWAY = $PAYMENT_GATEWAY;
		
		//Floating Point precision
		$this->set_precision($this->SETTINGS->config["payout_precision"]);

		// Check database connectivity
		if ($this->DB->connect_error){
			$this->status = FAUCET_STATUS_MYSQL_CONNECTION_FAILED;
			return;
		}

		// Check wallet connectivity
		try{
			$this->PAYMENT_GATEWAY->test();
		}
		catch(Exception $e){
			// Wallet is not working for some reason
			$this->status = FAUCET_STATUS_RPC_CONNECTION_FAILED;
			return;
		}

		// Check Balance
		$this->refresh_balance();

		if ($this->balance >= $this->SETTINGS->config["payout_threshold"]) {
			$this->status = FAUCET_STATUS_OPERATIONAL;
		}
		else {
			$this->status = FAUCET_STATUS_DRY_FAUCET;
		}
	}

	// Checks to see if this user can request a payout.
	public function Open(){
		$this->can_collect = $this->check_payout_time();
	}

	// Give a little if possible
	public function Dispense(){
		if($this->can_collect){
			$this->payout_amount = $this->drip();
		}
		else{
			$this->payout_amount = 0;
		}
	}

	// Send the dispensed coins
	public function Close(){
		if($this->can_collect){
			$this->do_payout();
		}

		// Prevents from sending again
		$this->can_collect = false;
	}

	public function SetAddress($cryptocoin_address){
		$this->payout_address = $this->DB->escape_string($cryptocoin_address);
		$this->validate_address();
	}

	public function SetIP($ip_address){
		$this->ip_address = $this->DB->escape_string($ip_address);
		$this->validate_ip();
	}

	public function SetPromoCode($promo_code = ''){
		if (!$this->SETTINGS->config["use_promo_codes"]) {$this->promo_code = '';}
		$this->promo_code = $this->DB->escape_string($promo_code);
		$this->validate_promo_code();
		$this->calculate_promo();
	}

	// Allows you to perform all 3 actions (open, dispense, close) in a single step
	public function Payout($cryptocoin_address = "", $ip_address = "", $promo_code = ""){
		// Allows on the fly setting or changing of paramaters
		if(!empty($cryptocoin_address)){$this->SetAddress($cryptocoin_address);}
		if(!empty($ip_address)){$this->SetIP($ip_address);}
		if(!empty($promo_code)){$this->SetPromoCode($promo_code);}

		$this->Open();
		$this->Dispense();
		$this->Close();
	}

	public function isValidAddress(){
		return $this->address_isvalid;
	}

	public function GetWaitTime(){
		return $this->wait_time;
	}

	protected function calculate_wait_time($last_time_timestamp){
		if(empty($last_time_timestamp)){
			$this->wait_time = 0;
		}
		else{
			// The current time
			$this_time = new DateTime();
			// The last time payment was made (next_time = last_time)
			$next_time = new DateTime($last_time_timestamp);
			// Add payout_interval to last_time to get next_time
			$next_time->add(new DateInterval($this->interval_php()));
			// Diffrence between this_time and next_time gives wait_time
			$this->wait_time = $this_time->diff($next_time);
		}
	}

	protected function validate_address(){
		try {
			// Check if valid address
			$this->address_isvalid = $this->PAYMENT_GATEWAY->validateaddress($this->payout_address);
			if (!$this->address_isvalid) {
				$this->status = FAUCET_STATUS_INVALID_COIN_ADDRESS;
			}
		}
		catch (Exception $e){
			$this->status = FAUCET_STATUS_RPC_CONNECTION_FAILED;
		}
	}

	protected function validate_ip(){
		// If IP validation is turned on
		if ($this->SETTINGS->config["user_check"] == "ip_address" || $this->SETTINGS->config["user_check"] == "both") {
			// Check if valid IP address
			//TODO: Enable php5-filter
			//$this->ip_isvalid = filter_var($this->ip_address, FILTER_VALIDATE_IP);
			$this->ip_isvalid = !empty($this->ip_address);
		}
		else{
			// We don't care about IP validation
			$this->ip_isvalid = true;
		}

		if (!$this->address_isvalid) {
			$this->status = FAUCET_STATUS_INVALID_IP_ADDRESS;
		}
	}

	protected function interval_php(){
		// Default interval (and example of final format)
		$interval = "PT7H";
		// Extract the numeric value from the string
		$interval_value = intval(substr($this->SETTINGS->config["payout_interval"], 0, -1));
		// Extract the time value (d, h, m) and capitalize it
		$interval_function = strtoupper(substr($this->SETTINGS->config["payout_interval"], -1));

		// Now for Some black magic.... okay, not really. it just a simple conversion from this: "5m" to this: "5 MINUTE"
		if ($interval_value >= 0 && ($interval_function == "H" || $interval_function == "M" || $interval_function == "D" || $interval_function == "S")) {
			$interval = "P";
			switch ($interval_function) {
				case "S":
				case "M":
				case "H":
					$interval .= 'T';
					break;
			}
			$interval .= $interval_value . $interval_function;
		}

		return $interval;
	}

	protected function interval_sql(){
		// Default interval (and example of final format)
		$interval = "7 HOUR";
		
		// Extract the numeric value from the string
		$interval_value = intval(substr($this->SETTINGS->config["payout_interval"], 0, -1));
		// Extract the time value (d, h, m) and capitalize it
		$interval_function = strtoupper(substr($this->SETTINGS->config["payout_interval"], -1));

		// Now for Some black magic.... okay, not really. it just a simple conversion from this: "5m" to this: "5 MINUTE"
		if ($interval_value >= 0 && ($interval_function == "H" || $interval_function == "M" || $interval_function == "D" || $interval_function == "S")) {
			$interval = $interval_value . " ";
			switch ($interval_function) {
				case "S":
					$interval .= "SECOND";
					break;
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

		return $interval;
	}

	protected function check_payout_time(){
		// Initial Validations did not pass
		if(!$this->ip_isvalid){$this->status = FAUCET_STATUS_INVALID_IP_ADDRESS; return false;}
		if(!$this->address_isvalid){$this->status = FAUCET_STATUS_INVALID_COIN_ADDRESS; return false;}

		/// More dark magic...
		$user_check = "(";
		if ($this->SETTINGS->config["user_check"] == "ip_address" || $this->SETTINGS->config["user_check"] == "both") {
			$user_check .= " `ip_address` = '". $this->ip_address ."'";
		}
		if ($this->SETTINGS->config["user_check"] == "wallet_address" || $this->SETTINGS->config["user_check"] == "both") {
			$user_check .= ($this->SETTINGS->config["user_check"] == "both"?" OR":"")." `payout_address` = '". $this->payout_address ."'";
		}
		$user_check .= ")";

		// Some how we end up with a SELECT query to decide whether or not to drip for this request.
		$query = sprintf("SELECT `id`, `timestamp` FROM `%spayouts` WHERE `timestamp` > NOW() - INTERVAL %s AND %s", 
					$this->DB->TB_PRFX,
					$this->interval_sql(),
					$user_check);
		$result = $this->DB->query($query);

		if ($row = @$result->fetch_assoc()) {
			 // user already received a payout within the payout interval
			$this->status = FAUCET_STATUS_PAYOUT_DENIED;
			// Save the wait time
			$this->calculate_wait_time($row['timestamp']);
			return false;
		}
		else {
			// All is good, payment will be given
			return true;
			
		}
	}

	protected function validate_promo_code(){
		// No need to validate if no promo code was set.
		if(empty($this->promo_code)){return;}

		// check for valid promo code
		$query = sprintf("SELECT `minimum_payout`,`maximum_payout` 
						FROM `%spromo_codes` 
						WHERE `code` = '%s'", 
						$this->DB->TB_PRFX, 
						$this->promo_code);
		$result = $this->DB->query($query); 

		if ($promo = @$result->fetch_assoc()){
			// Get promo payout range
			$this->promo_payout_min = $promo["minimum_payout"];
			$this->promo_payout_max = $promo["maximum_payout"];
		}
		else{
			// Invalid Promo Code
			$this->status = FAUCET_STATUS_INVALID_PROMO_CODE;
			$this->promo_code = '';
		}
	}

	protected function calculate_promo(){
		// Ensure sanity
		if (!$this->SETTINGS->config["use_promo_codes"]) {$this->promo_code = '';}

		if(!empty($this->promo_code)){
			$this->promo_payout_amount = $this->drip($this->promo_payout_min, $this->promo_payout_max);
		}
		else{
			$this->promo_payout_amount = 0;
		}
	}

	// Drips a random amount from the faucet
	protected function drip($min = NULL, $max = NULL){
		// Ensure sanity
		if(empty($min)){$min = $this->SETTINGS->config["minimum_payout"];}
		if(empty($max)){$max = $this->SETTINGS->config["maximum_payout"];}
		$min = floatval($min);
		$max = floatval($max);
		if ($min >= $max) {return sprintf('%.' . $this->precision . 'f', $max);}

		// calculate a random COIN amount
		$multiple = pow(10, $this->precision);
		$random = mt_rand($min * $multiple, $max * $multiple) / $multiple; 
		return sprintf('%.' . $this->precision . 'f', $random);
	}

	protected function do_payout(){
		// Can't payout a negative or 0 amount
		if($this->payout_amount <= 0){$this->status = FAUCET_STATUS_PAYOUT_DENIED; return;}

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
			$this->status = FAUCET_STATUS_RPC_CONNECTION_FAILED;
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
			$this->status = true ? $this->promo_payout_amount>0 ? FAUCET_STATUS_PAYOUT_AND_PROMO_ACCEPTED : FAUCET_STATUS_PAYOUT_ACCEPTED : FAUCET_STATUS_PAYOUT_ERROR; 
		}
		else {
			// send the COIN
			try{
				$result = $this->PAYMENT_GATEWAY->sendtoaddress($this->payout_address, ($this->payout_amount + $this->promo_payout_amount));
			}
			catch(Exception $e){
				$result = null;
				$this->status = FAUCET_STATUS_RPC_CONNECTION_FAILED;
			}
			//TODO: Improve This mess
			$this->status = !is_null($result) ? $this->promo_payout_amount>0 ? FAUCET_STATUS_PAYOUT_AND_PROMO_ACCEPTED : FAUCET_STATUS_PAYOUT_ACCEPTED : FAUCET_STATUS_PAYOUT_ERROR;
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
			$this->status = FAUCET_STATUS_RPC_CONNECTION_FAILED;
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
		if ($this->status != FAUCET_STATUS_MYSQL_CONNECTION_FAILED) {
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