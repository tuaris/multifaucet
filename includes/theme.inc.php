<?php
//Theme setup

//For templating
$vars = array();

$vars['title'] = $SETTINGS->get('sitename') . " - " .$SETTINGS->get('sitedesc');
$vars['copyright'] = 'Copyright &copy;' . date("Y") . ' by '  . $SETTINGS->get('sitename');
?>