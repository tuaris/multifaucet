<?php
/* Enchilada 3.0 Libraries 
 * Simple GD CAPTCHA Class
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
 
// Implements a Simple GD CAPTCHA.
 
class SimpleGDCaptcha extends StandardCaptcha {

	protected static $Parameters = 'SESSION_NAME';
	protected static $Description = 'Simple GD CAPTCHA';
	protected static $DefaultLibrary = 'simple-captcha/simple-php-captcha.php';

	protected $SESSION_NAME;

	// The required data fields that must be recived in order to validate
	protected $RESPONSE;

	public function __construct($simple_captcha_session_name){
		$this->SESSION_NAME = $simple_captcha_session_name;
	}

	public function render(){
		// Ensure we have the session name
		if(empty($this->SESSION_NAME)){throw new Exception("Session name not set", 2)}

		// Load Captcha Library
		$this->load_library();

		// Generates a new captcha and save the resulting HTML
		$this->start_session();
		$captcha_config['session_name'] = $this->SESSION_NAME;
		$_SESSION['captcha'] = simple_php_captcha($captcha_config); // set a new CAPTCHA
		$this->HTML = isset($_SESSION['captcha']) ? '<img src="'. $this->LIBRARY . $_SESSION['captcha']["image_request"] . '" alt="[captcha]"/>' : '';

		// Make sure HTML was generated
		if(empty($this->HTML)){throw new Exception("Could not render CAPTCHA", 3)}
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
		$this->RESPONSE = $vars['captcha_code'] ?: '';
	}

	protected function start_session(){
		// This is indeed a little strange, but I didn't make this :-)
		@session_name($this->SESSION_NAME);
		@session_start();
	}

	protected function validate_data_fields(){
		// We require a response field
		if(empty($this->RESPONSE)){throw new Exception("Response field not found", 5);}
	}

	protected auto_detect_fields(){
		// If response is not set, auto detect it from the global POST variable
		if(empty($this->RESPONSE) && isset($_POST['captcha_code'])){
			$this->RESPONSE = $_POST['captcha_code'];
		}

		// Validate all required fields
		$this->validate_data_fields();
	}

	public function check(){
		// Ensure we have the session name
		if(empty($this->SESSION_NAME)){throw new Exception("Session name not set", 2)}

		// Ensure we have all required data
		$this->auto_detect_fields();

		//Do it!
		$this->start_session();
		$this->RESULT = $this->RESPONSE == @$_SESSION['captcha']['code'];

		//Prevent re-submissions
		unset($_SESSION['captcha']['code']);
	}

	public function isValid(){
		return $this->RESULT;
	}

	public function getError(){
		return "Code was not correct.";
	}
}

?>
