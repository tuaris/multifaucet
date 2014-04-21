<?php
 error_reporting(E_ALL);
 ini_set("display_errors", 0);

@include_once("system/multisite.inc.php"); //Enables Multisite Capabilites
require_once("system/app.conf.php"); //Application Constants
@include_once("config/local.conf.php"); //User Made Application Options
require_once("libraries/template.lib.php"); //Load Template Engine
require_once("classes/db.class.php");
require_once("classes/settings.class.php");
require_once("classes/wallet.interface.php");
require_once("classes/CoinAddressValidator.class.php");
require_once("classes/coldwallet.class.php");
require_once("classes/jsonRPCClient.class.php");
require_once("classes/hotwallet.class.php");
require_once("classes/faucet.class.php");

//Check if database is installed
if(is_file(APPLICATION_CONFDIR . 'db.conf.php')) {
	include('includes/database.inc.php');	
}
else {
	//No database configuration found, redirect to installer
	header('Location: install.php');
	exit;
}

//Check if settings is installed
if(is_file(APPLICATION_CONFDIR . 'faucet.conf.php')) {
	include('includes/settings.inc.php');	
}
else {
	//No configuration found redirect to installer
	header('Location: install.php');
	exit;
}

//Wallet
if(is_file(APPLICATION_CONFDIR . 'wallet.conf.php')) {
	include('includes/wallet.inc.php');
}
else{
	//No wallet configuration found
	header('Location: install.php');
	exit;
}

//Load Language
include('includes/lang.inc.php');

//Setup Theme
include('includes/theme.inc.php');
?>