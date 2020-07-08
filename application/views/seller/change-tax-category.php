<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="popup__body">
	<h2><?php echo Labels::getLabel('LBL_Customize_Tax_Rates',$siteLangId);?></h2>
	<?php
	$frm->setFormTagAttribute('onsubmit','setUpTaxCategory(this); return(false);');
	$frm->setFormTagAttribute('class','form');
	$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
	$frm->developerTags['fld_default_col'] = 12; 	
	echo $frm->getFormHtml(); ?>
</div>
