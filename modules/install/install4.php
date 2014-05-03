<form action='?step=5' method='post' id="install-form">
						<?php if($error == "1") { ?>
						<p class='error-message faucet-error'>Please, check Faucet information</p>
						<?php }	?>
						<?php if($error == "2") { ?>
						<p class='error-message faucet-error'>Could not write configuration, check file permissions</p>
						<?php }	?>
						<?php if($error == "3") { ?>
						<p class='error-message faucet-error'>Please, check CAPTCHA information</p>
						<?php }	?>
						<?php if($error == "4") { ?>
						<p class='error-message faucet-error'>Please, check SpammerSlapper information</p>
						<?php }	?>
						<div id='site-setup' class='installer settings-holder'>
							<h4>Faucet Settings:</h4>
							<div id='site-settings' class='installer subsection settings-holder'>
								<?php $counter = 0; ?>
								<?php foreach ($FAUCET['SETTINGS'] as $key => $value){ ?>
								<p class='<?php echo ($counter % 2) ? 'even' : 'odd'; ?>'>
								<label for="<?php echo $key;?>"><?php echo ucwords(str_replace('_', ' ', $key)); ?>:</label>
								<input type='text' size='37' value='<?php echo @$value ?: ''; ?>' name='<?php echo $key; ?>' />
								</p>
								<?php $counter++; ?>
								<?php } ?>
							</div>
							
							<h4>Anti-Abuse Settings:</h4>
							<div id='admin-settings' class='installer subsection settings-holder'>
								<p class='odd'>Captcha: 
								<label><input type="radio" name="captcha_type" value="false" <?php echo $FAUCET['CAPTCHA']['use_captcha'] ? '' : 'checked="checked"'; ?> />None</label>
								<label><input type="radio" name="captcha_type" value="simple-captcha" <?php echo $FAUCET['CAPTCHA']['captcha'] == 'simple-captcha' ? 'checked="checked"' : ''; ?> />Simple</label>
								<label><input type="radio" name="captcha_type" value="recaptcha" <?php echo $FAUCET['CAPTCHA']['captcha'] == 'recaptcha' ? 'checked="checked"' : ''; ?>/>reCaptcha</label>
								<label><input type="radio" name="captcha_type" value="solvemedia" <?php echo $FAUCET['CAPTCHA']['captcha'] == 'solvemedia' ? 'checked="checked"' : ''; ?>/>Solve Media</label>
								</p>

								<p class='odd'>If using Simple Captcha, setup the values below</p>
								<p class='even'>
								<label for="simple_captcha_session_name">Session Name:</label>
								<input type='text' size='37' value='<?php echo $FAUCET['CAPTCHA']['captcha_config']['simple_captcha_session_name'] ?: ''; ?>' name='simple_captcha_session_name' />
								</p>
								
								<p class='odd'>If using Re-Cpatcha, setup the values below</p>
								<p class='even'>
								<label for="recpatcha_private_key">Private Key:</label>
								<input type='text' size='37' value='<?php echo $FAUCET['CAPTCHA']['captcha_config']['recpatcha_private_key'] ?: ''; ?>' name='recpatcha_private_key' />
								</p>
								
								<p class='odd'>
								<label for="recpatcha_public_key">Public Key:</label>
								<input type='text' size='37' value='<?php echo $FAUCET['CAPTCHA']['captcha_config']['recpatcha_public_key'] ?: ''; ?>' name='recpatcha_public_key' />
								</p>
								
								<p class='odd'>If using Solve Media, setup the values below</p>
								<p class='even'>
								<label for="solvemedia_private_key">Private Key:</label>
								<input type='text' size='37' value='<?php echo $FAUCET['CAPTCHA']['captcha_config']['solvemedia_private_key'] ?: ''; ?>' name='solvemedia_private_key' />
								</p>
								
								<p class='odd'>
								<label for="solvemedia_challenge_key">Challenge Key:</label>
								<input type='text' size='37' value='<?php echo $FAUCET['CAPTCHA']['captcha_config']['solvemedia_challenge_key'] ?: ''; ?>' name='solvemedia_challenge_key' />
								</p>
								
								<p class='odd'>
								<label for="solvemedia_hash_key">Hassh Key:</label>
								<input type='text' size='37' value='<?php echo $FAUCET['CAPTCHA']['captcha_config']['solvemedia_hash_key'] ?: ''; ?>' name='solvemedia_hash_key' />
								</p>
								
								<p>
								This defines what to check for when a user enters a wallet address in order to decide whether or not to award payout to this user. <br />
								<br /><span class="instructions">
								"ip_address": checks the user IP address in the payout history.<br />
								"wallet_address": checks the user wallet address in the payout history.<br />
								"both": check both the IP and wallet address in the payout history.
								</span>
								</p>
								<p class='odd'><label for='user_check'>Anti-Abuse Check:</label> 
								<select name="user_check" id="user_check">
									<?php foreach($FAUCET['CHECK']['options'] as $option) { ?>
									<option value="<?php echo $option; ?>" <?php echo $option == $FAUCET['CHECK']['current'] ? 'selected="selected"' : '';?>><?php echo $option; ?></option>
									<?php } ?>
								</select>
								</p>

								<p class='even'>
								Enabling the following option will prevent abuse from proxy's and blacklisted IP addresses such as Tor exit nodes.
								<br /><br />
								<label><input type="checkbox" value='true' name='use_spammerslapper' <?php echo $FAUCET['use_spammerslapper'] ? 'checked="checked"' : ''; ?>/> Use SpammerSlapper</label></p>

								<p class='odd'>If using SpammerSlapper, setup the API key below</p>
								<p class='even'>
								<label for="spammerslapper_key">API Key:</label>
								<input type='text' size='37' value='<?php echo $FAUCET['spammerslapper_key'] ?: ''; ?>' name='spammerslapper_key' />
								</p>

							</div>
							
							<h4>Site Settings:</h4>
							<div id='basic-settings' class='installer subsection settings-holder'>
								<p class='odd'><label for='template'>Select Theme:</label> 
								<select name="template" id="template">
									<?php foreach($FAUCET['TEMPLATE']['options'] as $option) { ?>
									<option value="<?php echo $option; ?>" <?php echo $option == $FAUCET['TEMPLATE']['current'] ? 'selected="selected"' : '';?>><?php echo $option; ?></option>
									<?php } ?>
								</select>
								</p>
								
								
								
								<p class='even'><label><input type="checkbox" value='true' name='use_promo_codes' <?php echo $FAUCET['PROMO'] ? 'checked="checked"' : ''; ?>/> Enable Promo Codes</label></p>
								
								<p>Usually the language will be auto-detected.  
								You can specify a default language if auto dectection fails. </p>
								<p class='odd'>Default Site Language: 
								<select name="lang" id="lang">
									<?php foreach($FAUCET['LANG']['options'] as $option) { ?>
									<option value="<?php echo $option; ?>" <?php echo $option == $FAUCET['LANG']['current'] ? 'selected="selected"' : '';?>><?php echo $option; ?></option>
									<?php } ?>
								</select>
								</p>								
							</div>
						</div>
						
						<fieldset class="install-button">
							<button id="configure-btn" class="primary_lg" >Next</button>
						</fieldset>
						
						</form>