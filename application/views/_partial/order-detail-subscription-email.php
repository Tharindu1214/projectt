<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$str='<table cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #ddd; border-collapse:collapse;">
	<tr>
		<td style="padding:10px;font-size:13px;border:1px solid #ddd;color:#333;font-weight:bold" width="200">'.Labels::getLabel('LBL_Subscription_Name', $siteLangId).'</td>
		<td style="padding:10px;font-size:13px;color:#333;border:1px solid #ddd" width="620">'.OrderSubscription::getSubscriptionTitle($orderDetail, $siteLangId).'</td>
	</tr>';
	$str .= 
	'<tr>
		<td style="padding:10px;font-size:13px;border:1px solid #ddd;color:#333;font-weight:bold" width="200">'.Labels::getLabel('LBL_Subscription_Period',$siteLangId).'</td>
		<td style="padding:10px;font-size:13px;color:#333;border:1px solid #ddd" width="620">'.FatDate::format($orderDetail['ossubs_from_date']). " - ".FatDate::format($orderDetail['ossubs_till_date']).'</td>
	</tr>';	
	$str .= 
	'<tr>
		<td style="padding:10px;font-size:13px;border:1px solid #ddd;color:#333;font-weight:bold" width="200">'.Labels::getLabel('LBL_Subscription_Amount',$siteLangId).'</td>
		<td style="padding:10px;font-size:13px;color:#333;border:1px solid #ddd" width="620">'.CommonHelper::displayMoneyFormat($orderDetail['ossubs_price']).'</td>
	</tr>';	
	$str .= 
	'<tr>
		<td style="padding:10px;font-size:13px;border:1px solid #ddd;color:#333;font-weight:bold" width="200">'.Labels::getLabel('LBL_Product_Upload_Limit',$siteLangId).'</td>
		<td style="padding:10px;font-size:13px;color:#333;border:1px solid #ddd" width="620">'.$orderDetail['ossubs_products_allowed'].'</td>
	</tr>';	
	$str .= '</table>';
echo $str;