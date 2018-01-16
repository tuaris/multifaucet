<?php
//SHORTCUT TO GET CURRENT LANGUAGE
$LANGUAGE['current_lang'] = 'de';
$LANGUAGE['site_heading'] = "{$SETTINGS->config['sitedesc']} Wasserhahn";


$LANGUAGE['faucet_incomplete'] = 'Dieser Wasserhahn ist unvollständig, es fehlen möglicherweise Einstellungen oder der RPC-Client ist nicht verfügbar.';
$LANGUAGE['faucet_dry'] = "Dieser Wasserhahn ist trocken! Bitte spende (oder meins).";
$LANGUAGE['faucet_donate'] = "Bitte spenden oder meins, um diesen Wasserhahn laufen zu lassen";
$LANGUAGE['faucet_connect_error'] = "Kann im Moment keine Verbindung herstellen, bitte komm später wieder!";
$LANGUAGE['success'] = "Erfolg!";
$LANGUAGE['awarded'] = "Sie wurden mit ausgezeichnet";
$LANGUAGE['bonus'] = "Zusätzlich haben Sie einen Bonus von erhalten";
$LANGUAGE['try_later'] = "Etwas ging schief, konnte dich {$SETTINGS->config['coin_code']} nicht schicken... Bitte versuche es später erneut.";
$LANGUAGE['no_more_for_you'] = "Keine {$SETTINGS->config['coin_code']} mehr für dich! Komm in {$SETTINGS->config['payout_interval']} zurück.";
$LANGUAGE['enter_captcha'] = "Geben Sie den Code ein, den Sie oben sehen";
$LANGUAGE['bad_captcha'] = "Der eingegebene Code war falsch!";
$LANGUAGE['bad_address'] = "Sie haben eine ungültige {$SETTINGS->config['coin_code']}-Adresse eingegeben!";

$LANGUAGE['enter_address'] = "Geben Sie hier Ihre {$SETTINGS->config['coin_code']}-Adresse ein";
$LANGUAGE['enter_pomo'] = "Angebotscode (optional)";
$LANGUAGE['submit_button_text'] = "Holen Sie sich Münzen";

$LANGUAGE['payouts'] = "Zahlungen";
$LANGUAGE['faucet_balance'] = "Wasserhahn balance";
$LANGUAGE['average_payout'] = "Durchschnittliche Bezahlung";
$LANGUAGE['support_sponsor'] = "Bitte unterstützen Sie diesen Wasserhahn und besuchen Sie unseren Sponsor";

$LANGUAGE['price'] = "Preis";
?>