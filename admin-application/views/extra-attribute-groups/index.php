<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<div class="row">
	<div class="col-sm-12"> 
		<h1><?php echo Labels::getLabel('LBL_Manage_Extra_Attribute_Groups',$adminLangId); ?></h1>
			<section class="section">
			<div class="sectionhead">
				<h4> <?php echo Labels::getLabel('LBL_search',$adminLangId); ?></h4>
			</div>
			<div class="sectionbody space togglewrap" style="display:none;">
				<?php 
					$frmSearch->setFormTagAttribute ( 'onsubmit', 'searchExtraAttributeGroups(this); return(false);');
					$frmSearch->setFormTagAttribute ( 'class', 'web_form' );					
					$frmSearch->developerTags['colClassPrefix'] = 'col-md-';							
					$frmSearch->developerTags['fld_default_col'] = 6;
					
					$fld_keyword = $frmSearch->getField('keyword');
					$fld_keyword->addFieldTagAttribute('class', 'search-input');
					
					$btn_clear = $frmSearch->getField('btn_clear');
					$btn_clear->addFieldTagAttribute('onclick', 'clearSearch()');
					echo $frmSearch->getFormHtml();
				?>    
			</div>
		</section> 
	</div>
	<div class="col-sm-12"> 		
		<section class="section">
		<div class="sectionhead">
			<h4><?php echo Labels::getLabel('LBL_Extra_Attribute_Group_List',$adminLangId); ?> </h4>
			<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="extraAttributeGroupForm(0)";><?php echo Labels::getLabel('LBL_Add_Extra_Attribute_Group',$adminLangId); ?></a>
		</div>
		<div class="sectionbody">
			<div class="tablewrap" >
				<div id="listing"> <?php echo Labels::getLabel('LBL_Processing',$adminLangId); ?></div>
			</div> 
		</div>
		</section>
	</div>		
</div>