<?php
/* Enchilada 3.0 Libraries 
 * 
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

abstract class Configurable extends EnchiladaLibrary {
	// Returns an array of parameters that need values
	public static function listParameters() {
		$paramaters = static::parseParameters();
		array_walk($paramaters, 'self::apply_paramater_prefix');
		return $paramaters;
	}

	private static function parseParameters(){
		if(!isset(static::$Parameters)) {throw new UnexpectedValueException(get_called_class() . ' must define a static Parameters property');}
		return explode(',', static::$Parameters);
	}

	private static function apply_paramater_prefix(&$value){
		$value = strtoupper(get_called_class()) . '_' . $value;
	}

	// Copies the values from the configuration into the paramaters of the implimenting class
	public function setConfiguration(SimpleConfiguration $configuration){
		foreach($this->parseParameters() as $parameter){
			$prefixed_parameter = $parameter; //Copy the value
			$this->apply_paramater_prefix($prefixed_parameter);  //Apply prefix
			$this->$parameter = $configuration->$prefixed_parameter;
		}
	}
}
?>