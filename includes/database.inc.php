<?php
//Connects to the database

//Load database config
include(APPLICATION_CONFDIR . 'db.conf.php');

//Verify database connectivity
$DB = AppDB::GetInstance();
if (!$DB->ping()){
	//Connection failure
	die("Database Error");
	exit;
}	
?>