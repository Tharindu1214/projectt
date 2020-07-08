<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="popup__body">
	<h2><?php echo Labels::getLabel('LBL_Special_Price',$adminLangId);?></h2>
	<?php
	$frmSellerProductSpecialPrice->setFormTagAttribute('onsubmit','setUpSellerProductSpecialPrice(this); return(false);');
	$frmSellerProductSpecialPrice->setFormTagAttribute('class','web_form form--horizontal');
	$frmSellerProductSpecialPrice->developerTags['colClassPrefix'] = 'col-md-';
	$frmSellerProductSpecialPrice->developerTags['fld_default_col'] = 12;
	
	/* $splprice_end_dateFld = $frmSellerProductSpecialPrice->getField('splprice_end_date');
	$splprice_end_dateFld->htmlAfter */
	
	/* $frmSellerProductSpecialPrice->getField('splprice_price')->addFieldtagAttribute('placeholder', CommonHelper::getPlaceholderForAmtField($adminLangId)); */
	
	$splprice_display_list_priceFld = $frmSellerProductSpecialPrice->getField('splprice_display_list_price');
	$splprice_display_list_priceFld->developerTags['col'] = 4;
	
	$splprice_display_dis_valFld = $frmSellerProductSpecialPrice->getField('splprice_display_dis_val');
	$splprice_display_dis_valFld->developerTags['col'] = 4;
	
	$splprice_display_dis_typeFld = $frmSellerProductSpecialPrice->getField('splprice_display_dis_type');
	$splprice_display_dis_typeFld->developerTags['col'] = 4;
	

	$btnCancelFld = $frmSellerProductSpecialPrice->getField('btn_cancel');
	$btnCancelFld->setFieldTagAttribute('onClick', 'sellerProductSpecialPrices(' . $selprod_id . ');');
	echo $frmSellerProductSpecialPrice->getFormHtml(); ?>	
</div>
