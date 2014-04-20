<form action='?step=6' method='post' id="install-form">
						<?php if($error == "2") { ?>
						<p class='error-message faucet-error'>Could not finalize configuration, check file permissions</p>
						<?php }	?>
						<div id='configure-complete' class='installer settings-holder'>
							<p class='success-message'>Installation was successfull!  To change the configuration in the future
							you may re-run this installer or manually edit the configuration files.
							<br /><br />Please secure your installation by setting proper
							permissions to the following files in the config directory:</p>
							<div id="secure-files"><pre>
db.conf.php
wallet.conf.php
faucet.conf.php
							</pre>
							</div>
							<p>It's also recomended that you rename or delete the <strong>install.php</strong> file.</p>
						</div>
						<input type="hidden" name="install_complete" value="true" />
						<fieldset class="install-button">
							<button id="finish-btn" class="primary_lg" >Finish</button>
						</fieldset>						
						</form>