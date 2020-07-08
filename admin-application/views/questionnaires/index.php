<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row">
	<div class="col-sm-12"> 
		<h1><?php echo Labels::getLabel('LBL_Manage_Questionnaires',$adminLangId); ?> </h1>
			<section class="section searchform_filter">
				<div class="sectionhead">
					<h4><?php echo Labels::getLabel('LBL_Search...',$adminLangId); ?></h4>
				</div>
				<div class="sectionbody space togglewrap" style="display:none;">
					<?php 
						$frmSearch->setFormTagAttribute ( 'onsubmit', 'searchQuestionnaires(this); return(false);');
						$frmSearch->setFormTagAttribute ( 'class', 'web_form' );					
						$frmSearch->developerTags['colClassPrefix'] = 'col-md-';							
						$frmSearch->developerTags['fld_default_col'] = 6;
						
						$fld_keyword = $frmSearch->getField('keyword');
						$fld_keyword->addFieldTagAttribute('class', 'search-input');

						$btn_clear = $frmSearch->getField('btn_clear');
						$btn_clear->addFieldTagAttribute('onclick', 'clearSearch()');
						echo  $frmSearch->getFormHtml();
					?>    
				</div>
			</section> 
	</div>
	<div class="col-sm-12"> 		
		<section class="section">
		<div class="sectionhead">
			<h4><?php echo Labels::getLabel('LBL_Questionnaires_List',$adminLangId); ?></h4>
			<?php if($canEdit){ ?>
			<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="questionnaireForm(0)";><?php echo Labels::getLabel('LBL_Add_New',$adminLangId); ?></a>
			<?php } ?>
		</div>
		<div class="sectionbody">
			<div class="tablewrap" >
				<div id="questionnaireListing"> <?php echo Labels::getLabel('LBL_Processing',$adminLangId); ?></div>
			</div> 
		</div>
		</section>
	</div>		
</div>