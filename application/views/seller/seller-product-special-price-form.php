<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="popup__body">
	<h2><?php echo Labels::getLabel('LBL_Special_Price',$siteLangId);?></h2>
	<?php
	$frmSellerProductSpecialPrice->setFormTagAttribute('onsubmit','setUpSellerProductSpecialPrice(this); return(false);');
	$frmSellerProductSpecialPrice->setFormTagAttribute('class','form');
	$frmSellerProductSpecialPrice->developerTags['colClassPrefix'] = 'col-md-';
	$frmSellerProductSpecialPrice->developerTags['fld_default_col'] = 12;
	
	$startDateFld = $frmSellerProductSpecialPrice->getField('splprice_start_date');
	$startDateFld->setFieldTagAttribute('class','start_date_js');	
	
	$endDateFld = $frmSellerProductSpecialPrice->getField('splprice_end_date');
	$endDateFld->setFieldTagAttribute('class','end_date_js');	

	/* $splprice_end_dateFld = $frmSellerProductSpecialPrice->getField('splprice_end_date');
	$splprice_end_dateFld->htmlAfter */

	/* $frmSellerProductSpecialPrice->getField('splprice_price')->addFieldtagAttribute('placeholder', CommonHelper::getPlaceholderForAmtField($siteLangId)); */

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
<script>
$(document).ready(function(){
	setCurrDateFordatePicker();
});
</script>
