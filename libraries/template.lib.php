<?php
// Function controller display

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
function template_file($template_file, $theme = 'default'){
	$file = template_path($theme) . DIRECTORY_SEPARATOR . $template_file;
	
	//Fall back to default
	if(!file_exists($file)){
		$file = template_path() . DIRECTORY_SEPARATOR . $template_file;
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