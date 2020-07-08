<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$str='<table cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #ddd; border-collapse:collapse;">
	<tr>
	<td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Labels::getLabel('LBL_Product', $siteLangId).'</td>
	<td width="10%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;">'.Labels::getLabel('L_Qty', $siteLangId).'</td>
	<td width="15%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">'.Labels::getLabel('LBL_Price',$siteLangId).'</td>
	<td width="15%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">'.Labels::getLabel('LBL_Shipping',$siteLangId).'</td>
	<td width="15%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">'.Labels::getLabel('LBL_Volume/Loyalty_Discount',$siteLangId).
	'</td><td width="15%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">'.Labels::getLabel('LBL_Tax_Charges',$siteLangId).'</td>
	<td width="20%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">'.Labels::getLabel('LBL_Total',$siteLangId).'</td>
	</tr>';
	
	$opCustomerBuyingPrice = CommonHelper::orderProductAmount($orderProducts,'CART_TOTAL');
	$shippingPrice = CommonHelper::orderProductAmount($orderProducts,'SHIPPING');
	$volumeDiscount = CommonHelper::orderProductAmount($orderProducts,'VOLUME_DISCOUNT');
	$rewardPoints = CommonHelper::orderProductAmount($orderProducts,'REWARDPOINT');
	$discountTotal = CommonHelper::orderProductAmount($orderProducts,'DISCOUNT');	
	$taxCharged = CommonHelper::orderProductAmount($orderProducts,'TAX');
	$netAmount =  CommonHelper::orderProductAmount($orderProducts,'NETAMOUNT');
	
	$skuCodes = $orderProducts["op_selprod_sku"];
	$options = $orderProducts['op_selprod_options'];
	
	$total =  ($opCustomerBuyingPrice + $shippingPrice+ $taxCharged - abs($volumeDiscount));
	
	$prodOrBatchUrl = 'javascript:void(0)';
	/* if($orderProducts["op_is_batch"]){
		$prodOrBatchUrl  = CommonHelper::generateFullUrl('products','batch',array($orderProducts["op_selprod_id"]),"/");
	}else{
		$prodOrBatchUrl  = CommonHelper::generateFullUrl('products','view',array($orderProducts["op_selprod_id"]),"/");
	} */
	
	$str .= '<tr>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">
			<a href="'.$prodOrBatchUrl.'" style="font-size:13px; color:#333;">'.$orderProducts["op_product_name"].'</a><br/>'.Labels::getLabel('Lbl_Brand',$siteLangId).':'.$orderProducts["op_brand_name"].'<br/>'.Labels::getLabel('Lbl_Sold_By',$siteLangId).':'.$orderProducts["op_shop_name"].'<br/>'.$options.'
			</td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">'.$orderProducts['op_qty'].'</td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($orderProducts["op_unit_price"]).'</td>			
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($shippingPrice).'</td>	<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($volumeDiscount).'</td>	<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($taxCharged).'</td>			
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($total).'</td>
		</tr>';			
	
/* 	$str .= '<tr><td colspan="4" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('L_TOTAL', $siteLangId).'</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($total).'</td></tr>'; */
	
	$str .= '<tr><td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('L_CART_TOTAL_(_QTY_*_Product_price_)', $siteLangId).'</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($opCustomerBuyingPrice).'</td></tr>';
	
	if ( $shippingPrice > 0 ){
	$str.='<tr>
		<td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('LBL_SHIPPING',$siteLangId).'</td>
		<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($shippingPrice).'</td>
		</tr>';
	}
	  
	if ( $taxCharged > 0 ){
	$str.='<tr>
		<td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('LBL_Tax',$siteLangId).'</td>
		<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($taxCharged).'</td>
		</tr>';
	}
	if ( $discountTotal ){
	$str.='<tr>
		<td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('LBL_Discount',$siteLangId).'</td>
		<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($discountTotal).'</td>
		</tr>';
	}
	if ( $volumeDiscount ){
	$str.='<tr>
		<td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('LBL_Volume/Loyalty_Discount',$siteLangId).'</td>
		<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($volumeDiscount).'</td>
		</tr>';
	}if ( $rewardPoints ){
	$str.='<tr>
		<td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('LBL_Reward_Point_Discount',$siteLangId).'</td>
		<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($rewardPoints).'</td>
		</tr>';
	}
	  
   $str.= '<tr>
	<td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right"><strong>'.Labels::getLabel('LBL_ORDER_TOTAL',$siteLangId).'</strong></td>
	<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right"><strong>'.CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderProducts,'NETAMOUNT')).'</strong></td></tr>';
	
	$str .= '</table>';
echo $str;