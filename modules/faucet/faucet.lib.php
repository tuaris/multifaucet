<?php


function faucet_get_content($command){
	//Find out which module to look for
	$page = 'modules' . DIRECTORY_SEPARATOR . 'faucet' . DIRECTORY_SEPARATOR;
	
	//Find out which file to get
	switch($command){
		case 'status':
			$page .= 'status.php';
			break;
		default:
			$page .= 'form.php';
	}	
	
	return $page;
}

function faucet_get_captcha($SETTINGS){
	if ($SETTINGS->get("use_captcha")){
		//Settings to pass to captcha functions
		$captcha_config = array();
		
		//Load Captcha HTML
		if($SETTINGS->get("captcha") == "simple-captcha") {
			//Load simple captcha library
			$simplecaptcha = './libraries/simple-captcha/simple-php-captcha.php';
			require_once $simplecaptcha;
			$captcha_config['session_name'] = $SETTINGS->config["captcha_config"]["simple_captcha_session_name"];
			@session_name($captcha_config['session_name']);
			@session_start();
			$_SESSION['captcha'] = simple_php_captcha($captcha_config); // set a new CAPTCHA
			return isset($_SESSION['captcha']) ? '<img src="'. $simplecaptcha . $_SESSION['captcha']["image_request"] . '" alt="[captcha]"/>' : '';
		}
		elseif($SETTINGS->get("captcha") == "solvemedia") {
			//Load solvemedia library
			require_once './libraries/solvemedialib.php';
			$captcha_config['solvemedia_challenge_key'] = $SETTINGS->config["captcha_config"]["solvemedia_challenge_key"];
			return solvemedia_get_html($captcha_config["solvemedia_challenge_key"]);
		}
		elseif($SETTINGS->get("captcha") == "recaptcha") {
			//Load re-captcha library
			require_once './libraries/recaptchalib.php';
			$captcha_config['recpatcha_public_key'] = $SETTINGS->config["captcha_config"]["recpatcha_public_key"];
			return recaptcha_get_html($captcha_config["recpatcha_public_key"]);
		}
	}
	else{
		return ''; //Emtpy
	}
}

function faucet_valid_captcha($SETTINGS, $remote_address, $captcha_data = array()) {
	$isGood = false;
	if($SETTINGS->config["use_captcha"]){
		if ($SETTINGS->config["captcha"] == "recaptcha") {
			//Load re-captcha library
			require_once('./libraries/recaptchalib.php');
			$resp = @recaptcha_check_answer($SETTINGS->config["captcha_config"]["recpatcha_private_key"],
											$remote_address, 
											$captcha_data['recaptcha_challenge_field'], 
											$captcha_data['recaptcha_response_field']
			);
			$isGood = $resp->is_valid; // $resp->error;
		}
		elseif ($SETTINGS->config["captcha"] == "solvemedia") {
			//Load solvemedia library
			require_once('./libraries/solvemedialib.php');
			$resp = @solvemedia_check_answer($SETTINGS->config["captcha_config"]["solvemedia_private_key"], 
											 $remote_address, 
											 $captcha_data['adcopy_challenge'],  
											 $captcha_data['adcopy_response'], 
											 $SETTINGS->config["captcha_config"]["solvemedia_hash_key"]
			);
			$isGood = $resp->is_valid; // $resp->error;
		}
		else {
			//Load simple captcha library
			@session_name($SETTINGS->config["captcha_config"]["simple_captcha_session_name"]);
			@session_start();
			$isGood = $captcha_data['captcha_code'] == @$_SESSION['captcha']['code'];
			//Prevent re-submissions
			unset($_SESSION['captcha']['code']);
		}
	}
	else{
		//If no CAPTCHA is in use, then return true
		$isGood = true;
	}
	
	return $isGood;
}

function faucet_check_spammerslapper($SETTINGS, &$vars){
	$isGood = false;

	if(@$SETTINGS->config["use_spammerslapper"]){
		//Load SpammerSlapper library
		require('./libraries/spammerslapperlib.php');

		//We only need to use IP based services
		$options = array(
			'CHECK_SPAMASSASIN' => false,
			'CHECK_PROXY' => true,
			'CHECK_HTTPBL' => true,
			'CHECK_FORUMSPAM' => true,
			'CHECK_EMAIL' => false,
			'CHECK_DBLORG' => false,
			'CHECK_SPAMHAUSDBL' => false
		);

		$result = spammerslapper_check($SETTINGS->config["spammerslapper_key"], array(), $options);
		if($result->SPAM){
			//Using a Proxy or IP is blacklisted
			$vars['error'] = $result->Message;
			$isGood = false;
		}
		else {
			//All is good
			$isGood = true;
		}
	}
	else{
		//If no SpammerSlapper is in use, then return true
		$isGood = true;
	}
	
	return $isGood;
}

function faucet_eval_status($status, &$vars, $LANGUAGE, $SETTINGS){
	switch ($status) {
		case SF_STATUS_FAUCET_INCOMPLETE:
			$vars['status_message'] = $LANGUAGE['faucet_incomplete'];
			$show_form = false;
			break;

		case SF_STATUS_DRY_FAUCET:
			$vars['status_message'] = $LANGUAGE['faucet_dry'];
			$show_form = false;
			break;

		case SF_STATUS_RPC_CONNECTION_FAILED:
		case SF_STATUS_MYSQL_CONNECTION_FAILED:
			$vars['status_message'] = $LANGUAGE['faucet_connect_error'];
			$show_form = false;
			break;
		
		case SF_STATUS_PAYOUT_ACCEPTED:
			$vars['status_message'] = $LANGUAGE['success'] . ' ';
			$vars['status_message'] .= $LANGUAGE['awarded'] . ' ';
			$vars['status_message'] .= $vars['stats']['payout_amount'] . ' ' . $SETTINGS->get('coin_code') .'!';
			$show_form = false;
			break;

		case SF_STATUS_PAYOUT_AND_PROMO_ACCEPTED:
			$vars['status_message'] = $LANGUAGE['success'] . ' ';
			$vars['status_message'] .= $LANGUAGE['awarded'] . ' ';
			$vars['status_message'] .= $vars['stats']['payout_amount'] . ' ' . $SETTINGS->get('coin_code') .'!';
			$vars['status_message'] .= "<br />";
			$vars['status_message'] .= $LANGUAGE['bonus'] . ' ';
			$vars['status_message'] .= $vars['stats']['promo_payout_amount'] . ' ' . $SETTINGS->get('coin_code') .'!';
			$show_form = false;
			break;

		case SF_STATUS_PAYOUT_ERROR:
			$vars['status_message'] = $LANGUAGE['try_later'] . ' ' . $vars['error'];
			$show_form = false;
			break;

		case SF_STATUS_PAYOUT_DENIED:
			$vars['status_message'] = $LANGUAGE['no_more_for_you'];
			$show_form = false;
			break;

		case SF_STATUS_CAPTCHA_INCORRECT:
			$vars['error'] = $LANGUAGE['bad_captcha'];
			$show_form = true;
			break;

		case SF_STATUS_INVALID_COIN_ADDRESS:
			$vars['error'] = $LANGUAGE['bad_address'];
			$show_form = true;
			break;

		case SF_STATUS_OPERATIONAL:
			//No Message
			$show_form = true;
			break;
	}
	return $show_form;
}




?>