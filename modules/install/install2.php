<form action='?step=3' method='post' id="install-form">
				<?php if($error == "1") { ?>
				<p class='error-message database-error'>Please, check Database Connection information</p>
				<?php }	?>
				<?php if($error == "2") { ?>
				<p class='error-message database-error'>Could not write configuration, check file permissions</p>
				<?php }	?>
				<p>Enter your MySQL database information.  The database and user must already exist.</p>
				<div id='database-settings' class='installer settings-holder'>
					<p class='odd'><input type='text' size='37' value='<?php echo @$DB['DB_HOST'] ?: '1. Base Server (default - localhost)'; ?>' onfocus='if (this.value=="1. Base Server (default - localhost)") { this.value=""} ' onblur='if (this.value=="") { this.value="1. Base Server (default - localhost)"} ' name='baseserver'></p>							
					<p class='even'><input type='text' size='37' value='<?php echo @$DB['DB_USER'] ?: '2. Base Username.'; ?>' name='baseuser' onfocus='if (this.value=="2. Base Username.") { this.value=""} ' onblur='if (this.value=="") { this.value="2. Base Username."} '></p>
					<p class='odd'><input type='text' size='37' value='<?php echo @$DB['DB_PASS'] ?: '3. Base Password.'; ?>' name='basepass' onfocus='if (this.value=="3. Base Password.") { this.value=""} ' onblur='if (this.value=="") { this.value="3. Base Password."} '></p>
					<p class='even'><input type='text' size='37' value='<?php echo @$DB['DB_NAME'] ?: '4. Base Name (must be created)'; ?>' name='basename' onfocus='if (this.value=="4. Base Name (must be created)") { this.value=""} ' onblur='if (this.value=="") { this.value="4. Base Name (must be created)"} '></p>
					<p class='odd'><input type='text' size='37' value='<?php echo @$DB['TB_PRFX'] ?: '5. Table Prefix'; ?>' name='tableprefix' onfocus='if (this.value=="5. Table Prefix") { this.value=""} ' onblur='if (this.value=="") { this.value="5. Table Prefix"} '></p>
				</div>
				
				<fieldset class="install-button">
					<button id="installdb-btn" class="primary_lg" >Next</button>
				</fieldset>
			</form>