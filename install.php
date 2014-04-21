<?php
 error_reporting(E_ALL);
 ini_set("display_errors", 0);
?>
<?php @include_once("system/multisite.inc.php"); //Enables Multisite Capabilites ?>
<?php include("system/app.conf.php"); ?>
<?php include('modules/install/install.lib.php'); ?>
<?php

//Default page
$step = isset($_GET['step']) ? $_GET['step'] : '';

//For templating
$vars = array();

//Check if already installed
if(is_file(APPLICATION_CONFDIR . '.install_complete')) {
	//Prevents distructive behavors if already installed
	$vars['clean_install'] = false;
}

//Process any GET requests
if(!empty($_GET)) {
	$redirect = install_process_get_request($step, $vars);
	//Redirect to new page if needed
	if(!empty($redirect)){
		header("Location: $redirect");
		exit;
	}
}

//Process any POST requests
if(!empty($_POST)) {
	$redirect = install_process_post_request($step, $vars);
	//Redirect to new page if needed
	if(!empty($redirect)){
		header("Location: $redirect");
		exit;
	}
}

//Genorate Page Title and heading based on insallation step
$vars['title'] = install_get_page_title($step);
$vars['heading'] = install_get_page_heading($step);

$vars['copyright'] = APPLICATION_NAME . " Install" . ' &copy;' . date("Y");
$vars['step'] = $step;

//Load License
$vars['LICENCE'] = getLicense();

//Load Template Engine
include("libraries/template.lib.php");

//Build and Display Page
$vars['breadcrumb'] = render_template(install_get_breadcrumb($step), $vars); 
$vars['content'] = render_template(install_get_content($step), $vars); 
print render_template(template_file("page.tpl.php"), $vars); 
?>
