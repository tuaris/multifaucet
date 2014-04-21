<form action='?step=2' method='post'>
						<?php if(@$error == "2") { ?>
						<p class='error-message config-error'>Configuration directory (<?php echo APPLICATION_CONFDIR; ?>) not writable, check file permissions</p>
						<?php }	?>
						<p>Welcome to the <strong><?php echo APPLICATION_NAME; ?></strong>, loosely derived from <em id="original">Simple Faucet script by Dogenes</em>.  To continue please read and accept the following license agreement.</p><br />
						<div id='license-agreement'>
							<p><?php echo APPLICATION_NAME; ?> <?php echo APPLICATION_VERSION; ?>. <br />
							<a href="<?php echo APPLICATION_WEBSITE; ?>" title="Click for Help" target="_blank"><?php echo APPLICATION_WEBSITE; ?></a></p>
							<pre id='copyright-notice' class='warning-message'>
							<?php print($LICENCE); ?>
							</pre>
							
						</div>
						<br /><br />
						<input type="hidden" name="license_accepted" value="true" />
						<fieldset class="install-button">
							<button id="agree-btn" class="primary_lg" >Accept License Agreement</button>
						</fieldset>
						
						</form>