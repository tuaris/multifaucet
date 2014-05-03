<?php
//Instalation Related Functions

/*
* Creates or replaces the database configuration file
*
*/
function write_db_config($host, $name, $user, $pass, $prefix, $file){
$buffer = <<<EOF
<?php
define("DB_HOST", "$host");
define("DB_NAME", "$name");
define("DB_USER", "$user");
define("DB_PASS", "$pass");
define("TB_PRFX", "$prefix");
?>
EOF;
if (!file_put_contents($file, $buffer)){throw new Exception("Could not wrtite configuration.", 2);}
}

/*
* Creates or replaces the wallet configuration file
*
*/
function write_wallet_config($type, $host, $port, $user, $pass, $address_version, $coldwallet_file, $encr, $file){
//Hot or Cold Wallet
if($type == 'cold'){$host = ""; $port = "";}

//Clean up the variables a bit
if(!strcmp($host,"1. RPC Server (default - localhost)")){$host = "localhost";}
if(!strcmp($user,"2. RPC Username.")){$user = "";}
if(!strcmp($pass,"3. RPC Password.")){$pass = "";}
if(!strcmp($port,"4. RPC Port")){$port = "";}
if(!strcmp($address_version,"5. Address Version")){$address_version = "";}
if(!strcmp($encr,"7. If wallet encrypted, Enter PASS")){$encr = "";}
if(!strcmp($coldwallet_file,"6. Cold Wallet Datafile")){$coldwallet_file = "";}

$buffer = <<<EOF
<?php
define("ADDRESS_VERSION", "$address_version"); // Found in src/base58.h:CBitcoinAddress.PUBKEY_ADDRESS

//Hot Wallet Authetication Info
//The cold wallet also has a username/password component that's used by the push/pull client and server
define("PAYMENT_GW_RPC_USER", "$user");
define("PAYMENT_GW_RPC_PASS", "$pass");

//For cold Wallet
define("PAYMENT_GW_DATAFILE", "$coldwallet_file");

//For Hot Wallet - Leave PAYMENT_GW_RPC_HOST empty to use Cold Wallet
define("PAYMENT_GW_RPC_HOST", "$host");
define("PAYMENT_GW_RPC_PORT", "$port");
// If the wallet is encrypted, enter the PASSPHRASE here. Leave it blank otherwise!
define("PAYMENT_GW_RPC_ENCR", "$encr");
?>
EOF;
if (!file_put_contents($file, $buffer)){throw new Exception("Could not wrtite configuration.", 2);}

//Make sure we have what we need
if(empty($coldwallet_file) && $type == 'cold'){throw new Exception("No Datafile");}
if(empty($port) && $type == 'hot'){throw new Exception("No Port Given");}
if($type == 'cold' && (empty($user) || empty($pass))){throw new Exception("Cold wallet needs user and password");}
}


/*
* Creates or replaces the faucet configuration file
*
*/
function write_faucet_config($settings,  $file){
//Clean up the format of some variables
$settings['use_promo_codes'] = $settings['use_promo_codes'] == true ? 'true' : 'false';
$settings['captcha'] = $settings['use_captcha'] == 'false' ? '' : $settings['use_captcha'];
$settings['use_captcha'] = $settings['use_captcha'] == 'false' ? 'false' : 'true';
$settings['use_spammerslapper'] = $settings['use_spammerslapper'] ? 'true' : 'false';

$buffer = <<<EOF
<?php
// Modify these settings to suit your needs.
\$config = array(
	"mysql_table_prefix" => TB_PRFX, // table prefix to use

	"minimum_payout" => {$settings['minimum_payout']}, // minimum to be awarded
	"maximum_payout" => {$settings['maximum_payout']}, // maximum to be awarded
	"payout_threshold" => {$settings['payout_threshold']}, // payout threshold, if the faucet contains less than this, display the 'dry_faucet' message
	"payout_interval" => "{$settings['payout_interval']}", // payout interval, the wait time for a user between payouts. Type any numerical value with either a "m" (minutes), "h" (hours), or "d" (days), attached. Examples: 50m for a 50 minute delay, 7h for a 7 hour delay, etc.

	// this option has 3 possible values: "ip_address", "wallet_address", and "both". It defines what to check for when a user enters an address in order to decide whether or not to award to this user.
	// "ip_address": checks the user IP address in the payout history.
	// "wallet_address": checks the wallet address in the payout history.
	// "both": check both the IP and wallet address in the payout history.
	"user_check" => "{$settings['user_check']}",

	"use_captcha" => {$settings['use_captcha']}, // require the user to enter a captcha
	"use_spammerslapper" => {$settings['use_spammerslapper']}, // Prevent The use of Proxies and check the IP against Blacklists

	"captcha" => "{$settings['captcha']}", // which CAPTCHA to use, possible values are: "recaptcha", "solvemedia", and "simple-captcha".

	"captcha_config" => array(
		//Simple Captcha Session Name
		"simple_captcha_session_name" => "{$settings['captcha_config']['simple_captcha_session_name']}",
		// if you're using reCAPTCHA, enter your private and public keys here:
		"recpatcha_private_key" => "{$settings['captcha_config']['recpatcha_private_key']}",
		"recpatcha_public_key" => "{$settings['captcha_config']['recpatcha_public_key']}",
		// if you're using Solve MEDIA, enter your private, challenge, and hash keys here:
		"solvemedia_private_key" => "{$settings['captcha_config']['solvemedia_private_key']}",
		"solvemedia_challenge_key" => "{$settings['captcha_config']['solvemedia_challenge_key']}",
		"solvemedia_hash_key" => "{$settings['captcha_config']['solvemedia_hash_key']}",
	),

	"spammerslapper_key" => "{$settings['spammerslapper_key']}", // SpammerSlapper API key.

	// promo codes:
	"use_promo_codes" => {$settings['use_promo_codes']}, // accept promo codes

	// Donation address:
	"donation_address" => "{$settings['donation_address']}", // donation address to display

	// Faucet look and feel:
	"title" => "{$settings['title']}", // page title, may be used by the template too
	"sitename" => "{$settings['sitename']}", // page title, may be used by the template too
	"sitedesc" => "{$settings['sitedesc']}", // page title, may be used by the template too
	"coin_code" => "{$settings['coin_code']}",
	"template" => "{$settings['template']}", // template to use (see the templates directory)
	"lang" => "{$settings['lang']}",
);
?>
EOF;

if (!file_put_contents($file, $buffer)){throw new Exception("Could not wrtite configuration.", 2);}

//Validate some settings
//CAPTCHA - Yeah, it's complicated

//Normalize Variables
$settings['captcha_config']['solvemedia_private_key'] = $settings['captcha_config']['solvemedia_private_key'] == 'PRIVATE_KEY_HERE' ? '' : $settings['captcha_config']['solvemedia_private_key'];
$settings['captcha_config']['solvemedia_challenge_key'] = $settings['captcha_config']['solvemedia_challenge_key'] == 'CHALLENGE_KEY_HERE' ? '' : $settings['captcha_config']['solvemedia_challenge_key'];
$settings['captcha_config']['solvemedia_hash_key'] = $settings['captcha_config']['solvemedia_hash_key'] == 'HASH_KEY_HERE' ? '' : $settings['captcha_config']['solvemedia_hash_key'];
$settings['captcha_config']['recpatcha_private_key'] = $settings['captcha_config']['recpatcha_private_key'] == 'PRIVATE_KEY_HERE' ? '' : $settings['captcha_config']['recpatcha_private_key'];
$settings['captcha_config']['recpatcha_public_key'] = $settings['captcha_config']['recpatcha_public_key'] == 'PUBLIC_KEY_HERE' ? '' : $settings['captcha_config']['recpatcha_public_key'];

//Ensure we have the required settings
if(
	//Solve Media
	($settings['captcha'] == 'solvemedia' && 
	(empty($settings['captcha_config']['solvemedia_private_key']) ||
	empty($settings['captcha_config']['solvemedia_challenge_key']) ||
	empty($settings['captcha_config']['solvemedia_hash_key']))
	) ||
	//re-CAPTCHA
	($settings['captcha'] == 'recaptcha' && 
	(empty($settings['captcha_config']['recpatcha_private_key']) ||
	empty($settings['captcha_config']['recpatcha_public_key']))
	) ||
	//Simple Captcha
	($settings['captcha'] == 'simple-captcha' && 
	empty($settings['captcha_config']['simple_captcha_session_name'])
	)
){
	throw new Exception("CAPTACH configuration incomplete.", 3);
}

// Make sure SpammerSlapper is setup properly
if(
	//SpammerSlapper
	($settings['use_spammerslapper'] == 'true' && 
	empty($settings['spammerslapper_key'])
	)
){
	throw new Exception("SpammerSlapper configuration incomplete.", 4);
}
}

function check_wallet($type){
	//Attempt to verify wallet 
	
	//Load Required Libraries (Yeah we need ALL this stuff)
	require_once("classes/wallet.interface.php");
	
	if($type == 'hot'){
		//Load Hot Wallet Libraries
		require_once("classes/jsonRPCClient.class.php");
		require_once("classes/hotwallet.class.php");
	}
	else{
		//Attempt to connect to database
		include('classes/db.class.php');
		include('includes/database.inc.php');
		//Load Cold Wallet Libraries
		require_once("classes/CoinAddressValidator.class.php");
		require_once("classes/coldwallet.class.php");
	}
	
	//Create Wallet
	include('includes/wallet.inc.php');
	
	//Check by calling a simple test procedure
	$PAYMENT_GATEWAY->test();
}

/*
* Creates required database tables
*
*/
function install_database_schema(){
	//Attempt to connect to database
	include('classes/db.class.php');
	include('includes/database.inc.php');
	
	//Build Queries
	$queries[] = sprintf("CREATE TABLE `%spayouts` (
							`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
							`ip_address` VARCHAR(45) NOT NULL DEFAULT '',
							`payout_amount` FLOAT NOT NULL,
							`payout_address` VARCHAR(34) NOT NULL DEFAULT '',
							`promo_code` VARCHAR(80) NOT NULL DEFAULT '',
							`promo_payout_amount` FLOAT NOT NULL,
							`txid` VARCHAR(80) NULL DEFAULT NULL,
							`timestamp` DATETIME NOT NULL,
							`lastupdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
							PRIMARY KEY (`id`)
						)
						COLLATE='utf8_general_ci'
						ENGINE=MyISAM;", 
						TB_PRFX);

	$queries[] = sprintf("CREATE TABLE `%spromo_codes` (
							`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
							`code` VARCHAR(80) NOT NULL DEFAULT '',
							`minimum_payout` FLOAT NOT NULL,
							`maximum_payout` FLOAT NOT NULL,
							PRIMARY KEY (`id`),
							UNIQUE INDEX `unique_code` (`code`)
						)
						COLLATE='utf8_general_ci'
						ENGINE=MyISAM;",
						TB_PRFX);

	//Run Queries
	foreach($queries as $query){
		$DB->query($query);
	}	
}

function finish_install($clean_install){
	//Installation complete, create a touch file
	if($clean_install){
		if (!file_put_contents(APPLICATION_CONFDIR . '.install_complete', date('r'))){throw new Exception("Could not wrtite configuration.", 2);}
	}
}

function create_datastore($path){
	if(!is_dir($path)) {
		mkdir($path, 0777);
	}	
}

function install_get_content($step){
	$template = 'modules' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR;
	$template .= 'install' . $step. '.php';
	
	if(!is_file($template)) {	
		$template = '';
	}
	
	return $template;
}

function install_get_breadcrumb($step){
	$page = 'modules' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR;
	$page .= "install-nav.tpl.php";
	return $page;
}

function install_get_page_heading($step){
	$heading = 'Step ';
	switch($step){
		case 2:
			$heading .= '2 - Database Configuration.';
			break;
		case 3:
		case 4:
			$heading .= "$step - " . APPLICATION_NAME . ' Wallet Configuration.';
			break;
		case 5:
			$heading .= "$step - " . APPLICATION_NAME . ' Configuration.';
			break;
		case 6:
			$heading = APPLICATION_NAME . ' Installed.';
			break;
		default:
			$heading = 'Welcome to ' . APPLICATION_NAME . '. ' . $heading;
			$heading .= '1 - Legal Agreement.';
	}
	return $heading;
}
function install_get_page_title($step){
	$title = APPLICATION_NAME . ' - Install';
	if($step) {
		$title .= " Step $step";
	}
	return $title;
}

function install_process_post_request($step, &$vars = array()){
	$redirect_complete = "";

	try{
		switch($step){
			case 2:
				//Check if Config directory is writable
				if(!@touch(APPLICATION_CONFDIR)){throw new Exception("Configuration directory is not writable", 2);}
				break;
			case 3:
				//Database Setup
				write_db_config($_POST['baseserver'], 
								$_POST['basename'], 
								$_POST['baseuser'], 
								$_POST['basepass'], 
								$_POST['tableprefix'], 
								APPLICATION_CONFDIR . 'db.conf.php'
				);
				if($vars['clean_install']){install_database_schema();}
				break;
			case 4:

				//Wallet Setup
				write_wallet_config($_POST['wallet_type'], 
									$_POST['rpcserver'], 
									$_POST['rpcport'], 
									$_POST['rpcuser'], 
									$_POST['rpcpass'], 
									$_POST['addressV'], 
									$_POST['coldwallet_file'], 
									$_POST['hotwallet_encrypt'], 
									APPLICATION_CONFDIR . 'wallet.conf.php'
				);
				//Check Connection/Permissions

				check_wallet($_POST['wallet_type']);
				break;
			case 5:
				//Basic Site Preferences
				$settings = array(
					'minimum_payout' => $_POST['minimum_payout'], 
					'maximum_payout' => $_POST['maximum_payout'], 
					'payout_threshold' => $_POST['payout_threshold'], 
					'payout_interval' => $_POST['payout_interval'], 
					'user_check' => $_POST['user_check'], 
					'use_captcha' => $_POST['captcha_type'], 
					'captcha_config' => array(
						'simple_captcha_session_name' => $_POST['simple_captcha_session_name'], 
						'recpatcha_private_key' => $_POST['recpatcha_private_key'], 
						'recpatcha_public_key' => $_POST['recpatcha_public_key'],
						'solvemedia_private_key' => $_POST['solvemedia_private_key'],
						'solvemedia_challenge_key' => $_POST['solvemedia_challenge_key'],
						'solvemedia_hash_key' => $_POST['solvemedia_hash_key']
					), 
					'use_promo_codes' => isset($_POST['use_promo_codes']), 
					'use_spammerslapper' => isset($_POST['use_spammerslapper']), 
					'spammerslapper_key' => $_POST['spammerslapper_key'], 
					'donation_address' => $_POST['donation_address'], 
					'title' => $_POST['title'], 
					'sitename' => $_POST['sitename'], 
					'sitedesc' => $_POST['sitedesc'], 
					'template' => $_POST['template'], 
					'coin_code' => $_POST['coin_code'],
					'lang' => $_POST['lang'], 
				);
				//Save
				write_faucet_config($settings, APPLICATION_CONFDIR . 'faucet.conf.php');
				break;
			case 6:
				//Done Installing
				if($_POST['install_complete']){finish_install(@$vars['clean_install']);}
				break;
			default:
				//Do nothing
		}
	}
	catch(Exception $e){
		//Default error code is 1
		$error = ($e->getCode() == 0) ? 1 : $e->getCode();
		$step--;
		//If this fails, the redirect back to previous step and specyfy error code
		if($step <=1 ){
			//Redirect back to first page
			$redirect_complete = $_SERVER['PHP_SELF'] . "?error=$error";
		}
		else{
			//Redirect to previous page
			$redirect_complete = $_SERVER['PHP_SELF'] . "?step=$step&error=$error";
		}
	}
	return $redirect_complete;
}

function install_process_get_request($step, &$vars = array()){
	$redirect_complete = "";

	//Check if this is a clean instalation or we only are reviewing/modifying the configuration, this prevents certain initalization functions
	if (isset($_GET['_'])){
		$vars['clean_install'] = ($_GET['_'] == 'conf') ? false : true;
	}
	else{
		$vars['clean_install'] = true;
	}
	
	//Get any error flags
	$vars['error'] = isset($_GET['error']) ? $_GET['error'] : "";

	switch($step){
		case 2:
			//Load Existing database configuration if present
			$vars['DB'] = install_get_db_config();
			break;
		case 3:
			//Load Existing database configuration if present
			$vars['WALLET'] = install_get_wallet_config();
			break;
		case 4:
			//Load Existing database configuration if present
			$vars['FAUCET'] = install_get_faucet_config();
			break;
		default:
			//Check for valid step
			$redirect_complete = install_get_content($step) ? "" : "index.php";
	}
	
	return $redirect_complete;
}

function install_get_db_config(){
	$constantsBeforeInclude = getUserDefinedConstants();
	@include(APPLICATION_CONFDIR . 'db.conf.php');
	$constantsAfterInclude = getUserDefinedConstants();

	return array_diff_assoc($constantsAfterInclude, $constantsBeforeInclude);
}

function install_get_wallet_config(){
	$constantsBeforeInclude = getUserDefinedConstants();
	@include(APPLICATION_CONFDIR . 'wallet.conf.php');
	$constantsAfterInclude = getUserDefinedConstants();

	return array_diff_assoc($constantsAfterInclude, $constantsBeforeInclude);
}
function install_get_faucet_config(){
	$config = array();
	$faucet = array('CAPTCHA' => array(),
					'SETTINGS' => array(),
					'TEMPLATE' => array(),
					'CHECK' => array(),
	);

	//Default Values:
	$faucet['SETTINGS'] = array(
		"title" => "ZetaCoin Faucet",
		"sitename" => "ZET Faucet", 
		"sitedesc" => "ZetaCoin",
		"coin_code" => "ZET",
		"minimum_payout" => 0.01, 
		"maximum_payout" => 10, 
		"payout_threshold" => 250, 
		"payout_interval" => "7h", 
		"donation_address" => "ZK6kdE5H5q7H6QRNRAuqLF6RrVD4cFbiNX",
	);
	/*
	$captcha_config_default = array(
		'simple_captcha_session_name' => 'multifaucet',
		'recpatcha_private_key' => 'PRIVATE_KEY_HERE', 
		'recpatcha_public_key' => 'PUBLIC_KEY_HERE',
		'solvemedia_private_key' => 'PRIVATE_KEY_HERE',
		'solvemedia_challenge_key' => 'CHALLENGE_KEY_HERE',
		'solvemedia_hash_key' => 'HASH_KEY_HERE',
	)*/
	
	//Load exisitng settings
	@include(APPLICATION_CONFDIR . 'faucet.conf.php');

	//Seprate out Captacha settings to it's own variable and merge with loaded values, set defaults if needed
	$faucet['CAPTCHA']['use_captcha'] = isset($config['use_captcha']) ? $config['use_captcha'] : true;
	$faucet['CAPTCHA']['captcha'] = isset($config['captcha']) ? $config['captcha'] : 'simple-captcha';
	$faucet['CAPTCHA']['captcha_config'] = isset($config['captcha_config']) ? $config['captcha_config'] : array();

	$faucet['CAPTCHA']['captcha_config']['simple_captcha_session_name'] = isset($config['captcha_config']['simple_captcha_session_name']) ? $config['captcha_config']['simple_captcha_session_name'] : 'multifaucet';
	$faucet['CAPTCHA']['captcha_config']['recpatcha_private_key'] = isset($config['captcha_config']['recpatcha_private_key']) ? $config['captcha_config']['recpatcha_private_key'] : 'PRIVATE_KEY_HERE';
	$faucet['CAPTCHA']['captcha_config']['recpatcha_public_key'] = isset($config['captcha_config']['recpatcha_public_key']) ? $config['captcha_config']['recpatcha_public_key'] : 'PUBLIC_KEY_HERE';
	$faucet['CAPTCHA']['captcha_config']['solvemedia_private_key'] = isset($config['captcha_config']['solvemedia_private_key']) ? $config['captcha_config']['solvemedia_private_key'] : 'PRIVATE_KEY_HERE';
	$faucet['CAPTCHA']['captcha_config']['solvemedia_challenge_key'] = isset($config['captcha_config']['solvemedia_challenge_key']) ? $config['captcha_config']['solvemedia_challenge_key'] : 'CHALLENGE_KEY_HERE';
	$faucet['CAPTCHA']['captcha_config']['solvemedia_hash_key'] = isset($config['captcha_config']['solvemedia_hash_key']) ? $config['captcha_config']['solvemedia_hash_key'] : 'HASH_KEY_HERE';

	//Seperate out only basic Settings by ignoring the other stuff
	foreach($config as $key => $conf){
		switch($key){
			case 'mysql_table_prefix': 
			case 'use_captcha': 
			case 'captcha': 
			case 'captcha_config': 
			case 'use_promo_codes': 
			case 'use_spammerslapper': 
			case 'spammerslapper_key': 
			case 'wallet_passphrase': 
			case 'template': 
			case 'user_check': 
				break;
			default:
				//Merge Loaded Settings
				$faucet['SETTINGS'][$key] = $conf;
		}
	}

	//Theme - Default if not set
	$faucet['TEMPLATE']['options'] = getAvaiableTemplates();
	$faucet['TEMPLATE']['current'] = isset($config['template']) ? $config['template'] : 'default';
	
	//User Check setup
	$faucet['CHECK']['options'] = array('ip_address', 'wallet_address', 'both');
	$faucet['CHECK']['current'] = isset($config['user_check']) ? $config['user_check'] : 'both';
	
	//Language - Default if not set
	$faucet['LANG']['options'] = getAvaiableLanguages();
	$faucet['LANG']['current'] = isset($config['template']) ? $config['template'] : 'en';

	//Other options
	$faucet['PROMO'] = isset($config['use_promo_codes']) ? $config['use_promo_codes'] : true;
	
	// SpammerSlapper
	$faucet['spammerslapper_key'] = isset($config['spammerslapper_key']) ? $config['spammerslapper_key'] : '';
	$faucet['use_spammerslapper'] = isset($config['use_spammerslapper']) ? $config['use_spammerslapper'] : false;

	return $faucet;
}

function getAvaiableTemplates(){
	$themes = array();
	try{
		//Read The theme directory contents:
		if ($entries = scandir('themes')) {
			foreach($entries as $entry) {
				//Check for the 'page.tpl.php' file under each directory
				if ($entry != "." && 
					$entry != ".." && 
					is_dir('themes' . DIRECTORY_SEPARATOR . $entry) && 
					file_exists('themes' . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'page.tpl.php')) {
					$themes[] = $entry;
				}
			}
		}
	}
	catch(Exception $e){
		//Upon failure just return a single default theme
		$themes = array('default');
	}

	return $themes;
}

function getAvaiableLanguages(){
	$langs = array();
	try{
		//Read The theme directory contents:
		if ($entries = scandir('langs')) {
			foreach($entries as $entry) {
				//Check for the 'lang.php' file under each directory
				if ($entry != "." && 
					$entry != ".." && 
					is_dir('langs' . DIRECTORY_SEPARATOR . $entry) && 
					file_exists('langs' . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'lang.php')) {
					$langs[] = $entry;
				}
			}
		}
	}
	catch(Exception $e){
		//Upon failure just return english
		$langs = array('en');
	}

	return $langs;
}

function getLicense(){
	return file_get_contents('LICENSE.md');
}

function getUserDefinedConstants() {
    $constants = get_defined_constants(true);
    return (isset($constants['user']) ? $constants['user'] : array());  
}
?>