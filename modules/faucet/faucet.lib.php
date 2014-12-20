<?php


function faucet_get_content($status){
	switch ($status) {
		case FAUCET_STATUS_FAUCET_INCOMPLETE:
		case FAUCET_STATUS_DRY_FAUCET:
		case FAUCET_STATUS_RPC_CONNECTION_FAILED:
		case FAUCET_STATUS_MYSQL_CONNECTION_FAILED:
		case FAUCET_STATUS_PAYOUT_ACCEPTED:
		case FAUCET_STATUS_PAYOUT_AND_PROMO_ACCEPTED:
		case FAUCET_STATUS_PAYOUT_ERROR:
		case FAUCET_STATUS_PAYOUT_DENIED:
			$page = 'status';
			break;

		case FAUCET_STATUS_CAPTCHA_INCORRECT:
		case FAUCET_STATUS_INVALID_COIN_ADDRESS:
		case FAUCET_STATUS_INVALID_IP_ADDRESS:
		case FAUCET_STATUS_OPERATIONAL:
			$page = 'form';
			break;
	}

	return $page . '.php';
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

function faucet_valid_captcha($SETTINGS, $remote_address, $captcha_post_data = array()) {
	$isGood = false;
	if($SETTINGS->config["use_captcha"]){
		if ($SETTINGS->config["captcha"] == "recaptcha") {
			//Load re-captcha library
			require_once('./libraries/recaptchalib.php');
			$resp = @recaptcha_check_answer($SETTINGS->config["captcha_config"]["recpatcha_private_key"],
											$remote_address, 
											$captcha_post_data['recaptcha_challenge_field'], 
											$captcha_post_data['recaptcha_response_field']
			);
			$isGood = $resp->is_valid; // $resp->error;
		}
		elseif ($SETTINGS->config["captcha"] == "solvemedia") {
			//Load solvemedia library
			require_once('./libraries/solvemedialib.php');
			$resp = @solvemedia_check_answer($SETTINGS->config["captcha_config"]["solvemedia_private_key"], 
											 $remote_address, 
											 $captcha_post_data['adcopy_challenge'],  
											 $captcha_post_data['adcopy_response'], 
											 $SETTINGS->config["captcha_config"]["solvemedia_hash_key"]
			);
			$isGood = $resp->is_valid; // $resp->error;
		}
		else {
			//Load simple captcha library
			@session_name($SETTINGS->config["captcha_config"]["simple_captcha_session_name"]);
			@session_start();
			$isGood = $captcha_post_data['captcha_code'] == @$_SESSION['captcha']['code'];
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

function faucet_eval_status(&$vars, &$FAUCET, $LANGUAGE, $SETTINGS){
	switch ($vars['status']) {
		case FAUCET_STATUS_FAUCET_INCOMPLETE:
			$vars['status_message'] = $LANGUAGE['faucet_incomplete'];
			break;

		case FAUCET_STATUS_DRY_FAUCET:
			$vars['status_message'] = $LANGUAGE['faucet_dry'];
			break;

		case FAUCET_STATUS_RPC_CONNECTION_FAILED:
		case FAUCET_STATUS_MYSQL_CONNECTION_FAILED:
			$vars['status_message'] = $LANGUAGE['faucet_connect_error'];
			break;
		
		case FAUCET_STATUS_PAYOUT_ACCEPTED:
			$vars['status_message'] = $LANGUAGE['success'] . ' ';
			$vars['status_message'] .= $LANGUAGE['awarded'] . ' ';
			$vars['status_message'] .= $vars['stats']['payout_amount'] . ' ' . $SETTINGS->get('coin_code') .'!';
			break;

		case FAUCET_STATUS_PAYOUT_AND_PROMO_ACCEPTED:
			$vars['status_message'] = $LANGUAGE['success'] . ' ';
			$vars['status_message'] .= $LANGUAGE['awarded'] . ' ';
			$vars['status_message'] .= $vars['stats']['payout_amount'] . ' ' . $SETTINGS->get('coin_code') .'!';
			$vars['status_message'] .= "<br />";
			$vars['status_message'] .= $LANGUAGE['bonus'] . ' ';
			$vars['status_message'] .= $vars['stats']['promo_payout_amount'] . ' ' . $SETTINGS->get('coin_code') .'!';
			break;

		case FAUCET_STATUS_PAYOUT_ERROR:
			$vars['status_message'] = $LANGUAGE['try_later'] . ' ' . $vars['error'];
			break;

		case FAUCET_STATUS_PAYOUT_DENIED:
			$vars['wait_time'] = $FAUCET->GetWaitTime();
			$vars['status_message'] = $LANGUAGE['no_more_for_you'] . ' ';
			$vars['status_message'] .= $LANGUAGE['come_back_in'] . ' ';
			$vars['status_message'] .= $vars['wait_time']->format('%d') . ' ' . $LANGUAGE['days'] . ' ';
			$vars['status_message'] .= $vars['wait_time']->format('%h') . ' ' . $LANGUAGE['hours'] . ' ';
			$vars['status_message'] .= $vars['wait_time']->format('%i') . ' ' . $LANGUAGE['minutes'] . ' ';
			$vars['status_message'] .= $vars['wait_time']->format('%s') . ' ' . $LANGUAGE['seconds'] . '. ';
			break;

		case FAUCET_STATUS_CAPTCHA_INCORRECT:
			$vars['error'] = $LANGUAGE['bad_captcha'];
			break;

		case FAUCET_STATUS_INVALID_COIN_ADDRESS:
			$vars['error'] = $LANGUAGE['bad_address'];
			break;

		case FAUCET_STATUS_INVALID_IP_ADDRESS:
			$vars['error'] = $LANGUAGE['bad_ip'];
			break;

		case FAUCET_STATUS_OPERATIONAL:
			//No Message
			break;
	}

	if ($vars['status'] == FAUCET_STATUS_CAPTCHA_INCORRECT ||
		$vars['status'] == FAUCET_STATUS_INVALID_COIN_ADDRESS ||
		$vars['status'] == FAUCET_STATUS_OPERATIONAL) 
	{
		// Render a Captcha
		$vars['captcha'] = faucet_get_captcha($SETTINGS);
	}
}

?>