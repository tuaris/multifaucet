<?php include('includes/bootstrap.inc.php'); ?>
<?php include('modules/faucet/faucet.lib.php'); ?>
<?php 

$FAUCET = new iFaucet($DB, $SETTINGS, $PAYMENT_GATEWAY);

$vars['title'] = $LANGUAGE['site_heading'];
$vars['copyright'] = 'Copyright &copy;' . date("Y") . ' by SecurePayment CC';

//if Submitting
if (isset($_POST["cryptocoin_address"])) {
	try{
		//Checking CAPTCHA
		$captchaValid = faucet_valid_captcha($SETTINGS, $_SERVER["REMOTE_ADDR"], $_POST);
		//No sense in continueing if CAPTCHA is bad
		if(!$captchaValid){throw new Exception("Post validation error:  Bad CPATCHA", FAUCET_STATUS_CAPTCHA_INCORRECT);}

		//Check if Valid Address
		$FAUCET->SetAddress($_POST["cryptocoin_address"]);
		//No sense in continueing if this is an invalid wallet address
		if(!$FAUCET->isValidAddress()){throw new Exception("Post validation error:  Bad wallet address", FAUCET_STATUS_INVALID_COIN_ADDRESS);}

		// Check SpammerSlapper
		$isNotSPAM = faucet_check_spammerslapper($SETTINGS, $vars);
		//No sense in continueing if this is SPAM
		if(!$isNotSPAM){throw new Exception("Post validation error:  SPAM FAIL", FAUCET_STATUS_PAYOUT_ERROR);}

		//Setup the other items
		$FAUCET->SetIP($_SERVER["REMOTE_ADDR"]);
		$FAUCET->SetPromoCode(@$_POST["promo_code"]);

		//All required validations have passed, attempt payout
		$FAUCET->Payout();

		//Get status
		$status = $FAUCET->status();
	}
	catch(Exception $e){
		$status = $e->getCode();
	}
}
else{
	//No Submition
	$status = $FAUCET->status();
}

// statistics:
$vars['stats'] = $FAUCET->get_stats();

//Save Status
$vars['status'] = $status;

// Render Status Message
faucet_eval_status($vars, $FAUCET, $LANGUAGE, $SETTINGS);

// Render Content
$vars['content'] = render_template(template_file(faucet_get_content($status), 'faucet'), $vars); 

// Render full page
print render_template(template_file("page.tpl.php"), $vars); 

?>