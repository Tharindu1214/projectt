<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="popup__body">
	<h2><?php echo Labels::getLabel('LBL_Customize_Tax_Rates',$adminLangId);?></h2>
	<?php
	$frm->setFormTagAttribute('onsubmit','setUpTaxRates(this); return(false);');
	$frm->setFormTagAttribute('class','web_form form--horizontal');
	$frm->developerTags['colClassPrefix'] = 'col-md-';
	$frm->developerTags['fld_default_col'] = 12; 	
	echo $frm->getFormHtml(); ?>	
</div>