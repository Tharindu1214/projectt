<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row">
	<div class="col-sm-12"> 
		<h1><?php echo Labels::getLabel('LBL_Product_Browsing_History',$adminLangId); ?></h1>	
		<section class="section searchform_filter">
			<div class="sectionhead">
				<h4> <?php echo Labels::getLabel('LBL_Search...',$adminLangId); ?></h4>
			</div>
			<div class="sectionbody space togglewrap" style="display:none;">
				<?php 
					$searchFrm->setFormTagAttribute ( 'onsubmit', 'searchBrowsingHistory(this); return(false);');
					$searchFrm->setFormTagAttribute ( 'class', 'web_form' );
					$searchFrm->developerTags['colClassPrefix'] = 'col-md-';					
					$searchFrm->developerTags['fld_default_col'] = 6;					
					echo  $searchFrm->getFormHtml();
				?>    
			</div>
		</section>		
	</div>
	<div class="col-sm-12"> 		
		<section class="section">
			<div class="sectionhead">
				<h4><?php echo Labels::getLabel('LBL_Product_Browsing_History',$adminLangId); ?></h4>			
			</div>
			<div class="sectionbody">
				<div class="tablewrap" >
					<div id="listing"> <?php echo Labels::getLabel('LBL_Processing...',$adminLangId); ?></div>
				</div> 
			</div>
		</section>
	</div>		
</div>