<?php
/* Enchilada 3.0 Libraries 
 * Standard CAPTCHA Framework
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

// A fake CAPTCHA that implements common properties and methods that may be used across diffrent CAPTCHA types
 
abstract class StandardCaptcha extends Configurable implements Captcha{
	protected $LIBRARY;
	protected $HEAD;
	protected $HTML;
	protected $RESULT;

	abstract public function render();
	abstract public function setData(array $vars);
	abstract public function check();
	abstract public function getError();
	abstract public function isValid();

	protected load_library(){
		if(empty($this->LIBRARY)){$this->LIBRARY = static::getDefaultLibrary();}
		if(!file_exists($this->LIBRARY)){throw new Exception("Cannot find captcha library", 1)}
		require_once($this->LIBRARY);
	}

	// Overrides the default library
	public function setLibrary($library){
		if (!empty($library)) {
			$this->LIBRARY = $library;
		} 
		else {
			$this->LIBRARY = static::getDefaultLibrary();
		}
	}

	public static getDefaultLibrary(){
		if(!isset(static::$DefaultLibrary)) {throw new UnexpectedValueException(get_called_class() . ' must define a static DefaultLibrary property');}
		return static::$DefaultLibrary;
	}

	public function getHEAD(){
		// Render HTML if not already done
		if(empty($this->HTML)){
			$this->render();
		}
		// Returns any additional HEAD content that must be present for the Captcha
		return $this->HEAD;
	}

	public function getHTML(){
		// Render HTML if not already done
		if(empty($this->HTML)){
			$this->render();
		}
		// Returns the generated HTML for the Captcha
		return $this->HTML;
	}

	protected function validate_ip($IP){
		$ip_valid = false;
		// If IP validation is turned on
		if (function_exists("filter_var")) {
			// Check if valid IP address using php5-filter
			$ip_valid = filter_var($IP, FILTER_VALIDATE_IP);
		}
		else{
			// Not really a check
			$ip_valid = !empty($IP);
		}
		return $ip_valid;
	}

}
?>