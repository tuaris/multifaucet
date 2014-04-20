			<ul id="process">
				<?php if(!$step || $step == 1) { ?>
				<li class="first active"><span>Step 1: License Agreement</span></li>
				<li class=""><span>Step 2</span></li>
				<li class=""><span>Step 3</span></li>
				<li class=""><span>Step 4</span></li>
				<li class="last"><span>Step 5</span></li>
				<?php } ?>
				
				<?php if($step == 2) { ?>
				<li class="first prevactive"><span>Step 1</span></li>
				<li class="active"><span>Step 2: Base Settings</span></li>
				<li class=""><span>Step 3</span></li>
				<li class=""><span>Step 4</span></li>
				<li class=""><span>Step 5</span></li>
				<?php } ?>
				
				<?php if($step == 3) { ?>
				<li class="first complete"><span>Step 1</span></li>
				<li class="prevactive"><span>Step 2</span></li>
				<li class="active"><span>Step 3: <?php echo APPLICATION_NAME; ?> Settings</span></li>
				<li class=""><span>Step 4</span></li>
				<li class=""><span>Step 5</span></li>
				<?php } ?>
				
				<?php if($step == 4) { ?>
				<li class="first complete"><span>Step 1</span></li>
				<li class="complete"><span>Step 2</span></li>
				<li class="prevactive"><span>Step 3</span></li>
				<li class="active"><span>Step 4: Base Install</span></li>
				<li class=""><span>Step 5</span></li>
				<?php } ?>
								
				<?php if($step == 5) { ?>
				<li class="first complete"><span>Step 1</span></li>
				<li class="complete"><span>Step 2</span></li>
				<li class="complete"><span>Step 3</span></li>
				<li class="prevactive"><span>Step 4</span></li>
				<li class="active"><span>Step 5: <?php echo APPLICATION_NAME; ?> Wallet</span></li>
				<li class=""><span>Step 6</span></li>
				<?php } ?>

				<?php if($step == 6) { ?>
				<li class="first complete"><span>Step 1</span></li>
				<li class="complete"><span>Step 2</span></li>
				<li class="complete"><span>Step 3</span></li>
				<li class="complete"><span>Step 4</span></li>
				<li class="prevactive"><span>Step 5</span></li>
				<li class="active"><span>Step 6: <?php echo APPLICATION_NAME; ?> Installed</span></li>
				<?php } ?>
			</ul>