<?php
//SHORTCUT TO GET CURRENT LANGUAGE
$LANGUAGE['current_lang'] = 'en';
$LANGUAGE['site_heading'] = "{$SETTINGS->config['coin_code']} Faucet";


$LANGUAGE['faucet_incomplete'] = 'This faucet is incomplete, it may be missing settings or the RPC client is not available.';
$LANGUAGE['faucet_dry'] = "This faucet is dry! Please donate.";
$LANGUAGE['faucet_donate'] = "Please donate to keep this faucet running";
$LANGUAGE['faucet_connect_error'] = "Cannot seem to connect at the moment, please come back later!";
$LANGUAGE['success'] = "Success!";
$LANGUAGE['awarded'] = "You have been awarded with";
$LANGUAGE['bonus'] = "Additionally, you received a bonus of";
$LANGUAGE['try_later'] = "Something went wrong, could not send you {$SETTINGS->config['coin_code']}... Please try again later.";
$LANGUAGE['no_more_for_you'] = "No more {$SETTINGS->config['coin_code']} for you! Try again later.";
$LANGUAGE['enter_captcha'] = "Enter the code you see above";
$LANGUAGE['bad_captcha'] = "The CAPTCHA code you entered was incorrect!";
$LANGUAGE['bad_address'] = "You entered an invalid {$SETTINGS->config['coin_code']} address!";

$LANGUAGE['enter_address'] = "Enter your {$SETTINGS->config['coin_code']} address here";
$LANGUAGE['enter_pomo'] = "Promo code (optional)";
$LANGUAGE['submit_button_text'] = "Get coins";

$LANGUAGE['payouts'] = "payouts";
$LANGUAGE['faucet_balance'] = "Faucet balance";
$LANGUAGE['average_payout'] = "Average payout";



?>