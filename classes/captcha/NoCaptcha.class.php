<?php
/* Enchilada 3.0 Libraries 
 * New Re-CAPTCHA Class (JavascriptVersion)
 * 
 * $Id$
 * 
 * Software License Agreement (BSD License)
 * 
 * Copyright (c) 2013-2014, The Daniel Morante Company, Inc.
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
 
// Implements Google Re-CAPTCHA: http://www.recaptcha.net
 
class NoCaptcha extends StandardCaptcha {

	protected static $Parameters = 'PRIVATE_KEY,PUBLIC_KEY,USE_CURL';
	protected static $Description = 'Google No Captcha Re-CAPTCHA (http://www.recaptcha.net)';
	protected static $Homepage = 'http://recaptcha.net';
	protected static $DefaultLibrary = 'N/a';

	protected $RENDER_URL = 'https://www.google.com/recaptcha/api.js';
	protected $VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

	protected $PRIVATE_KEY;
	protected $PUBLIC_KEY;
	protected $USE_CURL;

	// The required data fields that must be recived in order to validate
	protected $RESPONSE;
	protected $REMOTE_ADDRESS;

	public function __construct($recaptcha_private_key, $recaptcha_public_key, $use_curl = false){
		$this->PRIVATE_KEY = $recaptcha_private_key;
		$this->PUBLIC_KEY = $recaptcha_public_key;
		$this->USE_CURL = $use_curl;
	}

	public function render(){
		// Ensure we have the public key
		if(empty($this->PUBLIC_KEY)){throw new Exception("Public Key not set", 2)}
		// Generates a new captcha and save the resulting HTML
		$this->HEAD = sprintf('<script src="%s" async defer></script>', $this->RENDER_URL);
		$this->HEAD = sprintf('<div class="g-recaptcha" data-sitekey="%s"></div>', $this->PUBLIC_KEY);
	}

	// Overide the automaticly detected data
	public function setData(array $vars){
		// This has to be very specific, but it should not be nessesary 
		// to override the autdetection in the first place.

		// TODO: Maybe I'll find a better way someday with using class Abstraction
		// Where the '$vars' is an object that extends the absrtact class and
		// We have a captcha factory class.
		
		//For now, look for specific array keys in the submitted variable.
		//If any one is missing it will be auto detected later
		$this->RESPONSE = $vars['g-recaptcha-response'] ?: '';
		$this->REMOTE_ADDRESS = $vars['remote_ip'] ?: '';
	}

	protected function validate_data_fields(){
		// We require a response field
		if(empty($this->RESPONSE)){throw new Exception("Response field not found", 5);}
		
		// The remote IP address is also required
		if($this->validate_ip($this->REMOTE_ADDRESS)){throw new Exception("Renote IP address not set correctly", 6);}
	}

	protected auto_detect_fields(){
		// If response is not set, auto detect it from the global POST variable
		if(empty($this->RESPONSE) && isset($_POST['g-recaptcha-response'])){
			$this->RESPONSE = $_POST['g-recaptcha-response'];
		}

		// If remote address is not set, auto detect it from the global SERVER variable
		if(empty($this->REMOTE_ADDRESS) && isset($_SERVER["REMOTE_ADDR"])){
			$this->REMOTE_ADDRESS = $_SERVER["REMOTE_ADDR"];
		}

		// Validate all required fields
		$this->validate_data_fields();
	}

	public function check(){
		// Ensure we have the private key
		if(empty($this->PRIVATE_KEY)){throw new Exception("Private Key not set", 2)}

		// Ensure we have all required data
		$this->auto_detect_fields();

		$request_url = sprintf("%s?secret=%s&response=%s&remoteip=%s",
			$this->VERIFY_URL,
			$this->PRIVATE_KEY,
			$this->RESPONSE,
			$this->REMOTE_ADDRESS
		);

		//Do it!
		if($this->USE_CURL){
			// Use CURL
			$this->RESULT = $this->get_json_curl($request_url);
		}
		else {
			// Use Built-in PHP fopen() functions
			$this->RESULT = $this->get_json_fopen($request_url);
		}
		$this->RESULT = json_decode($this->RESULT, true);
	}

	protected function get_json_curl($request) {
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

		$curlData = curl_exec($curl);
		curl_close($curl);

		return $curlData;
	}
	
	function get_json_fopen($request){
		return file_get_contents($request, false);
	}

	public function isValid(){
		return $this->RESULT['success'];
	}

	public function getError(){
		return $this->RESULT['error-codes'];
	}
}

?>
