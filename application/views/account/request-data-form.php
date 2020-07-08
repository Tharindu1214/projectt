<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
	$frm->setFormTagAttribute('class', 'form form--horizontal');
	$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
	$frm->developerTags['fld_default_col'] = 12;
	$frm->setFormTagAttribute('onsubmit', 'setupRequestData(this); return(false);');
?>
<div class="cols--group">
	<div class="box__head">
		<h4><?php echo Labels::getLabel('LBL_Request_data',$siteLangId); ?></h4>
		<div class="note-messages"><?php echo Labels::getLabel('LBL_Request_system_owner_to_get_your_account_information',$siteLangId); ?></div>
		<div class="gap"></div>
	</div>
	<div class="box__body">
		<div class="form__subcontent">
			<?php
			$btnFld = $frm->getField('btn_submit');
			if(!empty($gdprPolicyLinkHref)){
				$btnFld->htmlBeforeField = str_replace( "{clickhere}" ,'<a target="_blank" href="'.$gdprPolicyLinkHref.'">'.Labels::getLabel('LBL_Click_Here',$siteLangId).'</a>', Labels::getLabel('LBL_{clickhere}_to_read_the_policies_of_GDPR',$siteLangId)).'<br/><br/>';
			}
			echo $frm->getFormHtml();
			?>
		</div>
	</div>
</div>