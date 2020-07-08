<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="row">
	<div class="col-sm-12"> 
		<h1><?php echo Labels::getLabel('LBL_Manage_Category_Requests',$adminLangId); ?></h1>
			<section class="section searchform_filter">
			<div class="sectionhead">
				<h4> <?php echo Labels::getLabel('LBL_Search...',$adminLangId); ?></h4>
			</div>
			<div class="sectionbody space togglewrap" style="display:none;">
				<?php 
					$frmSearch->setFormTagAttribute ( 'onsubmit', 'searchCategoryRequests(this); return(false);');
					$frmSearch->setFormTagAttribute ( 'class', 'web_form' );
					$frmSearch->developerTags['colClassPrefix'] = 'col-md-';					
					$frmSearch->developerTags['fld_default_col'] = 6;					
					echo  $frmSearch->getFormHtml();
				?>    
			</div>
		</section> 
	</div>
	<div class="col-sm-12"> 		
		<section class="section">
		<div class="sectionhead">
			<h4><?php echo Labels::getLabel('LBL_Category_Request_List',$adminLangId); ?> </h4>
			
		</div>
		<div class="sectionbody">
			<div class="tablewrap" >
				<div id="catListing"> <?php echo Labels::getLabel('LBL_Processing....',$adminLangId); ?></div>
			</div> 
		</div>
		</section>
	</div>		
</div>