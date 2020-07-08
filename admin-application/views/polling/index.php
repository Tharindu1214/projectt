<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row">
	<div class="col-sm-12"> 
		<h1><?php echo Labels::getLabel('LBL_Manage_Polling',$adminLangId); ?></h1>
		<section class="section searchform_filter">
			<div class="sectionbody space togglewrap" style="display:none;">
				<?php echo $frmSearch->getFormHtml(); ?>    
			</div>
		</section>
	</div>
	<div class="col-sm-12">  		
		<section class="section">
			<div class="sectionhead">
				<h4><?php echo Labels::getLabel('LBL_Polling_List',$adminLangId); ?></h4>		
				<a class="themebtn btn-default btn-sm" href="javascript:void(0)" onclick="pollingForm(0)"><?php echo Labels::getLabel('LBL_Add_New',$adminLangId); ?></a>			
			</div>
			<div class="sectionbody">
				<div class="tablewrap">
					<div id="listing"> <?php echo Labels::getLabel('LBL_Processing...',$adminLangId); ?></div>
				</div> 
			</div>
		</section>
	</div>		
</div>