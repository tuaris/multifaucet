<form action='?step=4' method='post' id="install-form">
				<?php if($error == "1") { ?>
				<p class='error-message wallet-error'>Please, check Wallet information</p>
				<?php }	?>
				<?php if($error == "2") { ?>
				<p class='error-message wallet-error'>Could not write configuration, check file permissions</p>
				<?php }	?>
				<div id='database-settings' class='installer settings-holder'>
					<p>Wallet Type: 
					<label><input type="radio" name="wallet_type" value="hot" <?php echo @$WALLET['PAYMENT_GW_RPC_HOST'] ? 'checked="checked"' : ''; ?> />Hot</label>
					<label><input type="radio" name="wallet_type" value="cold" <?php echo @$WALLET['PAYMENT_GW_RPC_HOST'] ? '' : 'checked="checked"'; ?>/>Cold</label>
					</p>
					<p>Specify wallet information.  If using a hot wallet the cold wallet data file may be left blank.
					If using a cold wallet, the RPC server should be left blank.</p>
					<p>An RPC username and password is required regaldless.</p>
					<p class='odd'><input type='text' size='37' value='<?php echo @$WALLET['PAYMENT_GW_RPC_HOST'] ?: '1. RPC Server (default - localhost)'; ?>' onfocus='if (this.value=="1. RPC Server (default - localhost)") { this.value=""} ' onblur='if (this.value=="") { this.value="1. RPC Server (default - localhost)"} ' name='rpcserver'></p>							
					<p class='even'><input type='text' size='37' value='<?php echo @$WALLET['PAYMENT_GW_RPC_USER'] ?: '2. RPC Username.'; ?>' name='rpcuser' onfocus='if (this.value=="2. RPC Username.") { this.value=""} ' onblur='if (this.value=="") { this.value="2. RPC Username."} '></p>
					<p class='odd'><input type='text' size='37' value='<?php echo @$WALLET['PAYMENT_GW_RPC_PASS'] ?: '3. RPC Password.'; ?>' name='rpcpass' onfocus='if (this.value=="3. RPC Password.") { this.value=""} ' onblur='if (this.value=="") { this.value="3. RPC Password."} '></p>
					<p class='even'><input type='text' size='37' value='<?php echo @$WALLET['PAYMENT_GW_RPC_PORT'] ?: '4. RPC Port'; ?>' name='rpcport' onfocus='if (this.value=="4. RPC Port") { this.value=""} ' onblur='if (this.value=="") { this.value="4. RPC Port"} '></p>
					<p>The address starting letter/number is usually found in <strong>src/base58.h</strong>: LINE 280 PUBKEY_ADDRESS = XX.</p>
					<p class='odd'><input type='text' size='37' value='<?php echo @$WALLET['ADDRESS_VERSION'] ?: '5. Address Version'; ?>' name='addressV' onfocus='if (this.value=="5. Address Version") { this.value=""} ' onblur='if (this.value=="") { this.value="5. Address Version"} '></p>
					<p>The datafile should be stored outside the web directory.  It may be specified as an absolute or relative path.</p>
					<p class='even'><input type='text' size='37' value='<?php echo @$WALLET['PAYMENT_GW_DATAFILE'] ?: '6. Cold Wallet Datafile'; ?>' name='coldwallet_file' onfocus='if (this.value=="6. Cold Wallet Datafile") { this.value=""} ' onblur='if (this.value=="") { this.value="6. Cold Wallet Datafile"} '></p>
					<p>Encryption only applies to hot wallet.</p>
					<p class='even'><input type='text' size='37' value='<?php echo @$WALLET['PAYMENT_GW_ENCR'] ?: '7. If wallet encrypted, Enter PASS'; ?>' name='hotwallet_encrypt' onfocus='if (this.value=="7. If wallet encrypted, Enter PASS") { this.value=""} ' onblur='if (this.value=="") { this.value="7. If wallet encrypted, Enter PASS"} '></p>
				</div>
				
				<fieldset class="install-button">
					<button id="installdb-btn" class="primary_lg" >Next</button>
				</fieldset>
			</form>