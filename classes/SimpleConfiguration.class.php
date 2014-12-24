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
 
 // This is a very basic paramater holder with validation for configurable objects

class SimpleConfiguration implements IteratorAggregate {
	protected $CONFIGURABLE;
	protected $DATA = [];

	public function __construct($class){
		if(is_object($class)){$class = get_class($class);}
		$this->CONFIGURABLE = $class;
	}

	// Returns an array of parameters that need values
	public function ListOptions() {
		$configurable = $this->CONFIGURABLE;
		try{
			$paramaters = $configurable::listParameters();
		}
		catch(Exception $e){
			$paramaters = [];
		}
		if(empty($paramaters)) {throw new UnexpectedValueException(get_called_class() . ' does not define any configurable options');}
		return array_fill_keys($paramaters, '');
	}

	public function getIterator() {
		return new ArrayIterator($this->DATA);
	}

	// Ensure that each paramater has a value
	// Returns an array with missing paramaters and paramaters with empty values
	public function Validate(){
		// To ensure we have a value set for every paramater
		// First check that the given data has no empty values
		// Remove it if it does.
		foreach($this->DATA as $key => $value){
			//Check if empty
			if(empty($value)){
				// Delete the key if it is
				unset($this->DATA[$key]);
			}
		}

		//Next, return an array with the missing keys
		$reference = $this->ListOptions();
		return array_diff_key($reference, $this->DATA);
	}

	public function Save(array $data){
		$this->DATA = $data;
	}

	// Returns an accosiative array
	public function Load(){
		return $this->DATA;
	}

	public function __set($name, $value){
		$this->DATA[$name] = $value;
	}
	
	public function __get($name){
		if (array_key_exists($name, $this->DATA)) {
			return $this->DATA[$name];
		}
		throw new OutOfBoundsException(get_called_class() . ":: The property '" . $name . "' does not exist.");
	}
	
	public function __isset($name){
		return isset($this->DATA[$name]);
	}
	
	public function __unset($name){
		unset($this->DATA[$name]);
	}
}
?>