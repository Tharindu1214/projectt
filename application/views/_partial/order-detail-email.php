<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$str='<table cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #ddd; border-collapse:collapse;">
	<tr>
	<td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Labels::getLabel('LBL_Product', $siteLangId).'</td>
	<td width="10%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;">'.Labels::getLabel('L_Qty', $siteLangId).'</td>
	<td width="15%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">'.Labels::getLabel('LBL_Price',$siteLangId).'</td>
	<td width="15%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">'.Labels::getLabel('LBL_Shipping',$siteLangId).'</td>
	<td width="15%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">'.Labels::getLabel('LBL_Volume/Loyalty_Discount',$siteLangId).'</td>
	<td width="15%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">'.Labels::getLabel('LBL_Tax_Charges',$siteLangId).'</td>
	<td width="20%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">'.Labels::getLabel('LBL_Total',$siteLangId).'</td>
	</tr>';
	
	$taxCharged = 0 ;	
	$cartTotal = 0 ;	
	$total = 0 ;	
	$shippingTotal = 0 ;		
	$netAmount = 0;
	$discountTotal = 0;
	$volumeDiscountTotal = 0;
	$rewardPointDiscount = 0;
	foreach( $orderProducts as $key => $val ){
		
		$opCustomerBuyingPrice = CommonHelper::orderProductAmount($val,'CART_TOTAL');
		$shippingPrice = CommonHelper::orderProductAmount($val,'SHIPPING');
		$discountedPrice = CommonHelper::orderProductAmount($val,'DISCOUNT');
		$taxCharged = $taxCharged + CommonHelper::orderProductAmount($val,'TAX');
		$productTaxCharged = CommonHelper::orderProductAmount($val,'TAX');
		$netAmount = $netAmount + CommonHelper::orderProductAmount($val,'NETAMOUNT');
		$volumeDiscount=  CommonHelper::orderProductAmount($val,'VOLUME_DISCOUNT');
		$volumeDiscountTotal = $volumeDiscountTotal + abs(CommonHelper::orderProductAmount($val,'VOLUME_DISCOUNT'));
		$rewardPointDiscount = $rewardPointDiscount + abs(CommonHelper::orderProductAmount($val,'REWARDPOINT'));
	
		$skuCodes = $val["op_selprod_sku"];
		$options = $val['op_selprod_options'];
		
		$cartTotal = $cartTotal + $opCustomerBuyingPrice;
		$shippingTotal = $shippingTotal + $shippingPrice;
		$discountTotal = $discountTotal + abs($discountedPrice);
		$total =  $total + $opCustomerBuyingPrice + $shippingPrice;
		
		$prodOrBatchUrl = 'javascript:void(0)';
		/* if($val["op_is_batch"]){
			$prodOrBatchUrl  = CommonHelper::generateFullUrl('products','batch',array($val["op_selprod_id"]),"/");
		}else{
			$prodOrBatchUrl  = CommonHelper::generateFullUrl('products','view',array($val["op_selprod_id"]),"/");
		} */
		
		$str .= '<tr>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">
			<a href="'.$prodOrBatchUrl.'" style="font-size:13px; color:#333;">'.$val["op_product_name"].'</a><br/>'.Labels::getLabel('Lbl_Brand',$siteLangId).':'.$val["op_brand_name"].'<br/>'.Labels::getLabel('Lbl_Sold_By',$siteLangId).':'.$val["op_shop_name"].'<br/>'.$options.'
			</td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">'.$val['op_qty'].'</td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($val["op_unit_price"]).'</td>			
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($shippingPrice).'</td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($volumeDiscount).'</td>	
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($productTaxCharged).'</td>
					
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($opCustomerBuyingPrice + $shippingPrice +$productTaxCharged - abs($volumeDiscount)).'</td>
		</tr>';	
	}
	
	/* $str .= '<tr><td colspan="4" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('L_TOTAL', $siteLangId).'</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($total).'</td></tr>'; */
	
	$str .= '<tr><td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('L_CART_TOTAL_(_QTY_*_Product_price_)', $siteLangId).'</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($cartTotal).'</td></tr>';
	
	if ( $shippingTotal > 0 ){
	$str.='<tr>
		<td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('LBL_SHIPPING',$siteLangId).'</td>
		<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($shippingTotal).'</td>
		</tr>';
	}
	  
	if ( $taxCharged > 0 ){
	$str.='<tr>
		<td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('LBL_Tax',$siteLangId).'</td>
		<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.CommonHelper::displayMoneyFormat($taxCharged).'</td>
		</tr>';
	}
	
	if ( $discountTotal != 0 ){
	$str.='<tr>
		<td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('LBL_Discount',$siteLangId).'</td>
		<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">-'.CommonHelper::displayMoneyFormat($discountTotal).'</td>
		</tr>';
	}
	if ( $volumeDiscountTotal != 0 ){
	$str.='<tr>
		<td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('LBL_Volume/Loyalty_Discount',$siteLangId).'</td>
		<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">-'.CommonHelper::displayMoneyFormat($volumeDiscountTotal).'</td>
		</tr>';
	}
	
	if ( $rewardPointDiscount != 0 ){
	$str.='<tr>
		<td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Labels::getLabel('LBL_Reward_Point_Discount',$siteLangId).'</td>
		<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">-'.CommonHelper::displayMoneyFormat($rewardPointDiscount).'</td>
		</tr>';
	}
	
	$str.= '<tr>
	<td colspan="6" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right"><strong>'.Labels::getLabel('LBL_ORDER_TOTAL',$siteLangId).'</strong></td>
	<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right"><strong>'.CommonHelper::displayMoneyFormat($netAmount).'</strong></td></tr>';
	
	$billingInfo = $billingAddress['oua_name'].'<br>';
	if($billingAddress['oua_address1']!=''){
		$billingInfo.=$billingAddress['oua_address1'].'<br>';
	}
	
	if($billingAddress['oua_address2']!=''){
		$billingInfo.=$billingAddress['oua_address2'].'<br>';
	}
	
	if($billingAddress['oua_city']!=''){
		$billingInfo.=$billingAddress['oua_city'].',';
	}
	
	if($billingAddress['oua_zip']!=''){
		$billingInfo.=$billingAddress['oua_state'];
	}
	
	if($billingAddress['oua_zip']!=''){
		$billingInfo.= '-'.$billingAddress['oua_zip'];
	}
	
	if($billingAddress['oua_phone']!=''){
		$billingInfo.= '<br>'.$billingAddress['oua_phone'];
	}
	
	$str.='</table><br/><br/><table cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #ddd; border-collapse:collapse;"><tr><td style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;"  bgcolor="#f0f0f0"><strong>'.Labels::getLabel('LBL_Order_Billing_Details',$siteLangId).'</strong></td><td style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" bgcolor="#f0f0f0"><strong>'.Labels::getLabel('L_Order_Shipping_Details',$siteLangId).'</strong></td></tr><tr><td valign="top" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" >'.$billingInfo.'</td>';
	
	$shippingInfo = $shippingAddress['oua_name'].'<br>';
	if($shippingAddress['oua_address1']!=''){
		$shippingInfo.=$shippingAddress['oua_address1'].'<br>';
	}
	
	if($shippingAddress['oua_address2']!=''){
		$shippingInfo.=$shippingAddress['oua_address2'].'<br>';
	}
	
	if($shippingAddress['oua_city']!=''){
		$shippingInfo.=$shippingAddress['oua_city'].',';
	}
	
	if($shippingAddress['oua_zip']!=''){
		$shippingInfo.=$shippingAddress['oua_state'];
	}
	
	if($shippingAddress['oua_zip']!=''){
		$shippingInfo.= '-'.$shippingAddress['oua_zip'];
	}
	
	if($shippingAddress['oua_phone']!=''){
		$shippingInfo.= '<br>'.$shippingAddress['oua_phone'];
	}
	
	$str.='<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">'.$shippingInfo.'</td></tr></table>';
echo $str;