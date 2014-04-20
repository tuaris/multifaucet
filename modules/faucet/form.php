					<form method="post" action="">
						<input name="cryptocoin_address" type="text" value="" placeholder="<?php print translate('enter_address'); ?>" />
						<?php if(get_setting('use_captcha')) { ?>
						<div id="captcha" class="<?php print get_setting('captcha'); ?>">
							<?php print($captcha); ?>
						</div>
						<?php } ?>
						
						<?php
						if (get_setting('use_captcha') && get_setting('captcha') == "simple-captcha") // only show captcha field for simple-captcha (reCAPTCHA has an own captcha input field)
							{
							?>
							<input name="captcha_code" type="text" value="" placeholder="<?php print translate('enter_captcha'); ?>" />
							<?php
							}
						?>
						<?php
						if (get_setting('use_promo_codes')) // show promo code field if promo codes are accepted
							{
							?>
							<input name="promo_code" type="text" value="" placeholder="<?php print translate('enter_pomo'); ?>" />
							<?php
							}
						?>
						<input name="cryptocoin_submit" type="submit" value="<?php print translate('submit_button_text'); ?>" />
					</form>
					<?php if (isset($error)){ ?>
					<p class="error"><?php print $error; ?></p>
					<?php } ?>
