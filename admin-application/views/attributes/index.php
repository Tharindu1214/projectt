<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<div class="row">
	<div class="col-sm-12"> 
		<h1><?php echo Labels::getLabel('LBL_Manage_Attributes_Group',$adminLangId); ?> </h1>
			<section class="section searchform_filter">
			<div class="sectionhead">
				<h4> <?php echo Labels::getLabel('LBL_Search...',$adminLangId); ?></h4>
			</div>
			<div class="sectionbody space togglewrap" style="display:none;">
				<?php 
					$search->setFormTagAttribute ( 'onsubmit', 'searchListing(this); return(false);');
					$search->setFormTagAttribute ( 'class', 'web_form' );					
					$search->developerTags['colClassPrefix'] = 'col-md-';
					$search->developerTags['fld_default_col'] = 6;
					echo  $search->getFormHtml();
				?>    
			</div>
		</section> 
	</div>
	<div class="col-sm-12"> 		
		<section class="section">
		<div class="sectionhead">
			<h4><?php echo Labels::getLabel('LBL_Attribute_Group_List',$adminLangId); ?> </h4>
			<?php if($canEdit){ ?>
			<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="addForm(0)";><?php echo Labels::getLabel('LBL_Add_New',$adminLangId); ?></a>
			<?php } ?>
		</div>
		<div class="sectionbody">
			<div class="tablewrap" >
				<div id="listing"> <?php echo Labels::getLabel('LBL_Processing...',$adminLangId); ?></div>
			</div> 
		</div>
		</section>
	</div>		
</div>