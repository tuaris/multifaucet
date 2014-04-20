<?php
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

//FB::log("Detected Language: " . $lang);

if(isset($_REQUEST['lang'])){$lang = $_REQUEST['lang'];}
switch ($lang){
    case "es":
        //echo "PAGE ES";
        break;
    case "en":
        //echo "PAGE EN";
        break;        
    default:
        //echo "PAGE EN - Setting Default";
		if(empty($lang)) {$lang = isset($SETTINGS->config['lang']) ? $SETTINGS->config['lang'] : 'en' ;}
        break;
}


if(is_file('langs/'.$lang.'/lang.php')) {
	include('langs/'.$lang.'/lang.php');
} 
else {
	//Using Englsh as Default
	include('langs/en/lang.php');
}

?>