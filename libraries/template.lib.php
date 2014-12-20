<?php
/* The Salsa Template System
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
 

//This function curtosy of Drupal (thanks Drupal!)
function render_template($template_file, $variables = array()) {
  extract($variables, EXTR_SKIP);  // Extract the variables to a local namespace
  ob_start();                      // Start output buffering
  include $template_file;      // Include the template file
  $contents = ob_get_contents();   // Get the contents of the buffer
  ob_end_clean();                  // End buffering and discard
  return $contents;                // Return the contents
}

//Support overriding default template
function template_file($template_file, $module = '', $theme = ''){
	//Pre append module name if we are loading module content include file
	$module_inc = empty($module) ? '' : $module . '-inc-';

	//Figure out the theme name to use
	$theme = empty($theme) ? get_current_theme_name() : $theme; //Use given theme name or current theme setting
	$theme = empty($theme) ? 'default' : $theme; //Use default theme if current theme setting is not avialable

	//Attempt to load template file from theme
	$file = template_path($theme) . DIRECTORY_SEPARATOR . $module_inc . $template_file;

	//Fall back to default theme
	if(!file_exists($file)){
		$file = template_path() . DIRECTORY_SEPARATOR . $module_inc . $template_file;
	}

	//Fall back to module or default module if no module name was specified
	if(!file_exists($file)){
		$file = 'modules' . DIRECTORY_SEPARATOR . (empty($module) ? 'default' : $module) . DIRECTORY_SEPARATOR . $template_file;
	}

	return $file;
}

//Support custom installed templates, aka: themes
function template_path($theme = 'default'){
    return 'themes' . DIRECTORY_SEPARATOR . $theme;
}

//Get the path to the current theme
function theme_dir(){
	$current_theme = get_current_theme_name();
	if (empty($current_theme) || !file_exists(template_path($current_theme) . DIRECTORY_SEPARATOR)){
		$current_theme = 'default'; 
	}
	return template_path($current_theme) . DIRECTORY_SEPARATOR;
}

//Gets the currently active theme
function get_current_theme_name(){
	return get_setting('theme_name');
}

function template_scripts($scripts = array()){
	foreach($scripts as $script) {
		$code .= '<script type="text/javascript" src="' . $script . '"></script>' . "\n";
	}
	return $code;
}	
function template_styles($styles = array()){
	foreach($styles as $style) {
		$code .= '<link rel="stylesheet" type="text/css" href="' . $style . '" />' . "\n";			
	}
	return $code;
}	

/*--------------------------------------------- Utilities ---------------------------------------------
*		 					Convience Function that utilize global variables
*------------------------------------------------------------------------------------------------------*/
function translate($phrase){
	global $LANGUAGE; //Nessesary evil ;-)
	if (isset($LANGUAGE)){
		return isset($LANGUAGE[$phrase]) ? $LANGUAGE[$phrase] : $phrase;
	}
	else{
		return $phrase;
	}	
}
function get_setting($name){	
	global $SETTINGS; //Nessesary evil ;-)
	if (isset($SETTINGS)){
		$value = $SETTINGS->get($name);
		return isset($value) ? $value : '';
	}
	else{
		return '';
	}
}
?>