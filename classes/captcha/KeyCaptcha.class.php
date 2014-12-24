<?php
/* Enchilada 3.0 Libraries 
 * Key CAPTCHA Class
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
 
// Implements KeyCaptcha: http://keycaptcha.com
 
class KeyCaptcha extends StandardCaptcha {

	protected static $Parameters = 'PRIVATE_KEY,USER_ID';
	protected static $Description = 'KeyCaptcha (http://keycaptcha.com)';
	protected static $Homepage = 'http://keycaptcha.com';
	protected static $DefaultLibrary = 'keycaptcha.php';

	protected $PRIVATE_KEY;
	protected $USER_ID;

	// Optional
	protected $SUBMIT_BUTTON_ID;

	// The required data fields that must be recived in order to validate
	protected $RESPONSE;

	protected $KEYCAPTCHA_OBJECT;

	public function __construct($kc_private_key, $kc_user_id, $submit_button_id = ''){
		$this->PRIVATE_KEY = $kc_private_key;
		$this->USER_ID = $kc_user_id;
		setSubmitButtonID($submit_button_id);
	}

	public function setSubmitButtonID($submit_button_id){
		$this->SUBMIT_BUTTON_ID = $submit_button_id;
	}

	protected function load_library(){
		parent::load_library();

		// Creat opbject 
		$this->KEYCAPTCHA_OBJECT = new KeyCAPTCHA_CLASS($this->PRIVATE_KEY, $this->USER_ID);
	}

	public function render(){
		// Ensure we have the private key
		if(empty($this->PRIVATE_KEY)){throw new Exception("Private Key not set", 2)}
		// Ensure we have the user id
		if(empty($this->USER_ID)){throw new Exception("User ID not set", 2)}
		// Ensure we have the submit button id
		if(empty($this->$this->SUBMIT_BUTTON_ID)){throw new Exception("Submit button ID not set", 2)}
		// Load Re-Captcha Library
		$this->load_library();
		// Generates a new captcha and save the resulting HTML
		$this->HTML = $this->KEYCAPTCHA_OBJECT->render_js($this->SUBMIT_BUTTON_ID);
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
		$this->RESPONSE = $vars['capcode'] ?: '';
	}

	protected function validate_data_fields(){
		// We require a response field
		if(empty($this->RESPONSE)){throw new Exception("Response field not found", 5);}
	}

	protected auto_detect_fields(){
		// If response is not set, auto detect it from the global POST variable
		if(empty($this->RESPONSE) && isset($_POST['capcode'])){
			$this->RESPONSE = $_POST['capcode'];
		}

		// Validate all required fields
		$this->validate_data_fields();
	}

	public function check(){
		// Ensure we have the private key
		if(empty($this->PRIVATE_KEY)){throw new Exception("Private Key not set", 2)}
		// Ensure we have the user id
		if(empty($this->USER_ID)){throw new Exception("User ID not set", 2)}

		// Ensure we have all required data
		$this->auto_detect_fields();

		// Load Re-Captcha Library
		$this->load_library();

		//Do it!
		$this->RESULT = $this->KEYCAPTCHA_OBJECT->check_result($this->RESPONSE);
	}

	public function isValid(){
		return $this->RESULT;
	}

	public function getError(){
		return "Puzzle not solved correctly";
	}
}

?>
