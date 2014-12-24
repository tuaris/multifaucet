<?php
/* Enchilada 3.0 Libraries 
 * Are you a Human (CAPTCHA) Class
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
 
// Implements the "Are you a Human" anti-bot verification: http://areyouahuman.com

class AreYouHuman extends StandardCaptcha {

	protected static $Parameters = 'PUBLISHER_KEY,SCORING_KEY';
	protected static $Description = 'Are you a Human anti-bot verification (http://areyouahuman.com)';
	protected static $Homepage = 'http://areyouahuman.com';
	protected static $DefaultLibrary = 'ayah.php';

	protected $PUBLISHER_KEY;
	protected $SCORING_KEY;

	// Extra
	protected $RUNTIME_OPTIONS;

	private $AYAH_OBJECT;

	// 	
	/*
	* This is a very simple implemantation of Are You a Human by passing configuration to the function calls.
	* Optional settings should be overriden using global constants
	*
	*	AYAH_WEB_SERVICE_HOST = ws.areyouahuman.com
	*	AYAH_TIMEOUT = 0
	*	AYAH_DEBUG_MODE = false
	*	AYAH_USE_CURL = true
	*/
	public function __construct($ayah_publisher_key, $ayah_scoring_key){
		$this->PUBLISHER_KEY = $ayah_publisher_key;
		$this->SCORING_KEY = $ayah_scoring_key;
	}

	// Allows some further customization
	public function setRuntimeOptions(array $runtime_options = array()){
		$this->RUNTIME_OPTIONS = $runtime_options;
	}

	protected function load_library(){
		parent::load_library();

		$params = array(
			'publisher_key' => $this->PUBLISHER_KEY, 
			'scoring_key' => $this->SCORING_KEY
		);

		// Creat opbject 
		$this->AYAH_OBJECT = new AYAH($params);
	}

	public function render(){
		// Ensure we have the publisher key
		if(empty($this->PUBLISHER_KEY)){throw new Exception("Publisher Key not set", 2)}
		// Ensure we have the scoring key
		if(empty($this->SCORING_KEY)){throw new Exception("Scoring Key not set", 2)}
		// Load AreYouHuman Captcha Library
		$this->load_library();
		// Generates a new captcha and save the resulting HTML
		$this->HTML = $this->AYAH_OBJECT->getPublisherHTML($this->RUNTIME_OPTIONS);
		// Make sure HTML was generated
		if(empty($this->HTML)){throw new Exception("Could not render CAPTCHA", 3)}
	}

	public function setData(array $vars){
		//This captcha does not allow you to override verification field data
		//throw new Exception("Are You a Human does not provide this option.", 7); //Maybey not
	}

	public function check(){
		// Ensure we have the publisher key
		if(empty($this->PUBLISHER_KEY)){throw new Exception("Publisher Key not set", 2)}
		// Ensure we have the scoring key
		if(empty($this->SCORING_KEY)){throw new Exception("Scoring Key not set", 2)}

		// Load Re-Captcha Library
		$this->load_library();

		//Do it!
		$this->RESULT = $this->AYAH_OBJECT->scoreResult();
	}

	public function isValid(){
		return $this->RESULT;
	}

	public function getError(){
		return "Sorry, but we were not able to verify you as human. Please try again.";
}
?>