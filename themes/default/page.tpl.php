<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
	<meta name="author" content="tuaris" />
	<link rel="stylesheet" href="<?php echo theme_dir(); ?>css/default.css" type="text/css" />
	<title><?php print($title); ?></title>
</head>
<body>
	<div id="wrapper">
		<h1><?php print($title); ?></h1>
		<div class="container">
			<?php print($content); ?>
		</div>
		<?php if(get_setting('donation_address')) { ?>
		<div class="container">
			<p><?php print translate('faucet_donate'); ?>:</p>
			<p class="big"><?php print(get_setting('donation_address')); ?></p>
		</div>
		<?php } ?>
		<div id="poweredby">
			<!-- Before removing this link please see README -->
			<a href="http://cur.lv/99zcp" title="<?php echo APPLICATION_WEBSITE; ?>">
				<?php echo APPLICATION_NAME . ' ' . APPLICATION_VERSION; ?>
			</a>
		</div>
		<?php if(isset($stats)) { ?>
		<div id="stats">
			<p><?php print translate('faucet_balance'); ?>: <?php print($stats['balance']); ?></p>
			<p><?php print translate('average_payout'); ?>: <?php print($stats['average_payout']); ?></p>
			<p><?php print($stats['number_of_payouts']); ?> <?php print translate('payouts'); ?></p>
		</div>
		<?php } ?>
		<img src="<?php echo theme_dir(); ?>images/droplet.png" class="droplet" alt=""/>
		<img src="<?php echo theme_dir(); ?>images/droplet.png" class="droplet" alt=""/>
		<img src="<?php echo theme_dir(); ?>images/droplet.png" class="droplet" alt=""/>
	</div>
</body>
</html>