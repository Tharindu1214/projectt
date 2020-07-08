<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="popup__body">
	<h2><?php echo Labels::getLabel( 'LBL_Volume_Discount', $adminLangId);?></h2>
	<?php
	$frmSellerProductVolDiscount->setFormTagAttribute('onsubmit','setUpSellerProductVolumeDiscount(this); return(false);');
	$frmSellerProductVolDiscount->setFormTagAttribute('class','web_form form--horizontal');
	$frmSellerProductVolDiscount->developerTags['colClassPrefix'] = 'col-md-';
	$frmSellerProductVolDiscount->developerTags['fld_default_col'] = 12;

	$btnCancelFld = $frmSellerProductVolDiscount->getField('btn_cancel');
	$btnCancelFld->setFieldTagAttribute('onClick', 'sellerProductVolumeDiscounts(' . $selprod_id . ');');
	echo $frmSellerProductVolDiscount->getFormHtml(); ?>	
</div>
