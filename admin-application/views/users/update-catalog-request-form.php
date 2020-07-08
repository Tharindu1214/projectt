<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('id', 'catalogRequestForm');
$frm->setFormTagAttribute('onsubmit', 'updateCatalogRequest(this); return(false);');
$frm->setValidatorJsObjectName('catalogRequestFormValidator');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;	
$fld = $frm->getField('status');
$fld->setFieldTagAttribute('onChange','showHideCommentBox(this.value)');

$fldBl = $frm->getField('comments');
$fldBl->htmlBeforeField = '<span id="div_comments_box" class="hide">'. Labels::getLabel('LBL_Reason_for_Cancellation',$adminLangId);
$fldBl->htmlAfterField = '</span>';
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Update_Status',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
		<div class="tabs_nav_container responsive flat">
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php echo $frm->getFormHtml(); ?>
				</div>
			</div>						
		</div>
	</div>						
</section>	