<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="container container-fluid">
	<div class="section">
		<div class="sectionhead">
			<h4>General Settings</h4> 
		</div>
		<div class="row">
			<div class="col-sm-12">					
				<div class="tabs_nav_container responsive flat">
					<ul class="tabs_nav">
						<li><a class="active" href="javascript:void(0);">General</a></li>
						<li><a href="javascript:void(0);">Local</a></li>
						<li><a href="javascript:void(0);">SEO</a></li>
						<li><a href="javascript:void(0);">Options</a></li>
						<li><a href="javascript:void(0);">Withdrawal</a></li>
						<li><a href="javascript:void(0);">Live Chat</a></li>
						<li><a href="javascript:void(0);">Third Party APIs</a></li>
						<li><a href="javascript:void(0);">Email</a></li>
						<li><a href="javascript:void(0);">Server</a></li>
						<li><a href="javascript:void(0);">Sharing</a></li>
						<li><a href="javascript:void(0);">Referral</a></li>
					</ul> 
					  <div class="tabs_panel_wrap">
						<div class="tabs_panel">
						
							<!-- inner[ -->
							<div class="tabs_nav_container responsive boxbased vertical">
                            
                            <ul class="tabs_nav">
                                <li><a class="active" href="javascript:void(0);" rel="tabs_000">Common</a></li>
                                <?php foreach($languages as $langId=>$langName){?>
										<li><a href="javascript:void(0);" rel="tabs_00<?php echo $langId; ?>"><?php echo $langName;?></a></li>
									<?php } ?>
                            </ul>
                            
                             <div class="tabs_panel_wrap">
								<span class="togglehead active" rel="tabs_000">Home</span>
								<div id="tabs_000" class="tabs_panel">
									<h4>Language Independents</h4>
									<p>Language independents will go here... </p>
								</div>
								<?php 
									foreach($languages as $langId=>$langName){?>
										<span class="togglehead active" rel="tabs_00<?php echo $langId; ?>">Home</span>
										<div id="tabs_00<?php echo $langId; ?>" class="tabs_panel">
											<h4><?php echo $langName; ?></h4>
											<p><?php echo $langName; ?></p>
										</div>
									<?php }
								?>
                              </div>      
                         
                       </div> 
							<!-- ]-->
							
						</div>
					  </div>
				</div>
			</div>
		</div>
	</div>
</div>

