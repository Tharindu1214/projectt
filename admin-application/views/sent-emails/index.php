<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<div class="row">
	<div class="col-sm-12"> 
		<h1><?php echo Labels::getLabel('LBL_Sent_Emails_List',$adminLangId); ?> </h1>
	</div>
	<div class="col-sm-12">  		
		<section class="section">
		<div class="sectionhead">
			<h4><?php echo Labels::getLabel('LBL_Sent_Emails_List',$adminLangId); ?> </h4>
		</div>
		<div class="sectionbody">
			<div class="tablewrap" >
				<div id="emails-list"> <?php echo Labels::getLabel('LBL_Processing...',$adminLangId); ?></div>
			</div> 
		</div>
		</section>
	</div>		
</div>



