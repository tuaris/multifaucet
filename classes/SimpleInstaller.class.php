<?php
/* Enchilada 3.0 Libraries 
 * Installation Framework - Simple Installation Class
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
 
 // This is a very basic configuration reader and writer that uses defined constants in a 'config file'.
 
class SimpleInstaller { 
	protected $CODE;
	protected $BUFFER;
	protected $FILE;

	public function __construct($filename){
		$this->FILE = $filename;
	}
	
 	// This is actually quite simple
	/*
	*	We compare the before and after of defined constants
	*	 the diffrence is the configuration
	*/
	public function readConfiguration(){
		static $cache = [];
		if(empty($cache)){
			$constantsBeforeInclude = self::getUserDefinedConstants();
			@include($this->FILE);
			$constantsAfterInclude = self::getUserDefinedConstants();
			$cache = array_diff_assoc($constantsAfterInclude, $constantsBeforeInclude);
		}
		return $cache;
	}

	private static function getUserDefinedConstants() {
		$constants = get_defined_constants(true);
		return (isset($constants['user']) ? $constants['user'] : array());  
	}

		// Generates PHP code that can be written as a loadable configuration file
	public function generateConfiguration(SimpleConfiguration $configuration){
		$this->BUFFER = '';
		$this->CODE = '';

		foreach($configuration as $key => $value){
			// Generate code
			$this->CODE .= <<<EOF
define("$key", "$value");
EOF;
			// Add new line
			$this->CODE .= "\n";
		}
	
		// Remove extra blank line at end
		$this->CODE = trim($this->CODE, "\n");

		$this->BUFFER .= <<<EOF
<?php
$this->CODE
?>
EOF;
	}

	public function writeConfiguration(SimpleConfiguration $configuration){
		$this->generateConfiguration($configuration);
		if (!file_put_contents($this->FILE, $this->BUFFER)){throw new Exception("Could not write configuration.", 2);}
	}

	public function getCode(){
		return $this->CODE;
	}
	public function getBuffer(){
		return $this->BUFFER;
	}

 }
?>