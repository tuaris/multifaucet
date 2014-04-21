<?php
//Load application settings
include(APPLICATION_CONFDIR . 'faucet.conf.php');

$SETTINGS = new Settings($config);
$SETTINGS->config['theme_name'] = $SETTINGS->get('template'); //template -> theme_name
?>