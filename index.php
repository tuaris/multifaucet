<?php include('includes/bootstrap.inc.php'); ?>
<?php include('modules/faucet/faucet.lib.php'); ?>
<?php 

$FAUCET = new Faucet($DB, $SETTINGS, $PAYMENT_GATEWAY);

$vars['title'] = $LANGUAGE['site_heading'];
$vars['copyright'] = 'Copyright &copy;' . date("Y") . ' by SecurePayment CC';

//Check if Submitting and validate CAPTCHA
if (isset($_POST["cryptocoin_address"]) && 
	faucet_valid_captcha($SETTINGS, $_SERVER["REMOTE_ADDR"], array(
						'captcha_code' => @$_POST["captcha_code"], 
						'recaptcha_response_field' => @$_POST["recaptcha_response_field"], 
						'recaptcha_challenge_field' => @$_POST["recaptcha_challenge_field"],
						'adcopy_challenge' => @$_POST["adcopy_challenge"],
						'adcopy_response' => @$_POST["adcopy_response"])) 
) {
	//Good CAPTCHA - attempt payout
	$FAUCET->payout($_POST["cryptocoin_address"], $_SERVER["REMOTE_ADDR"], @$_POST["promo_code"]);

	//Get status
	$status = $FAUCET->status();
}
elseif (isset($_POST["cryptocoin_address"])) {
	//BAD CAPTCHA
	$status = SF_STATUS_CAPTCHA_INCORRECT;
}
else{
	//No Submition
	$status = $FAUCET->status();
}

// statistics:
$vars['stats'] = $FAUCET->get_stats();

//TODO: I need to think of a better way to do this
$show_form = faucet_eval_status($status, $vars, $LANGUAGE, $SETTINGS);
if ($show_form){
	// Render Captcha
	$vars['captcha'] = faucet_get_captcha($SETTINGS);
	// Render Form
	$vars['content'] = render_template(faucet_get_content('form'), $vars); 
}
else{
	// Render Error/Status
	$vars['content'] = render_template(faucet_get_content('status'), $vars); 
}

// Render full page
print render_template(template_file("page.tpl.php", get_current_theme_name()), $vars); 

?>