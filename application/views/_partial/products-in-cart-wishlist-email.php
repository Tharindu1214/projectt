<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$countProducts = 1;
$str='<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;max-width:600px!important">
<tbody><tr>
	<td valign="top" style="padding:15px 0">';
   foreach($products as $product){
		$oldPrice = '';
		$discountedPrice = '<span style="display:block;width:30px;height:20px;border:3px solid #fff; padding:10px 5px;line-height:22px"></span>';
		if($product['special_price_found']){
			$oldPrice = CommonHelper::displayMoneyFormat($product['selprod_price']);
			$discountedPrice = CommonHelper::showProductDiscountedText($product, $siteLangId);
			$discountedPrice = '<span style="display:block;background-color:#ff3a59;color:#fff;text-align:center;border-radius:50%;border:3px solid #fff;font-family:Helvetica;width:30px;height:20px;font-size:13px;font-weight:700;padding:10px 5px;line-height:22px">'.$discountedPrice.'</span>';
		}
	$str .='<table align="left" border="0" cellpadding="0" cellspacing="0" width="165" style="min-height:305px; border-collapse:collapse;border:1px solid #cccccc">
		<tbody><tr>
			<td valign="top">'.$discountedPrice.'
				<table style="min-width:100%;border-collapse:collapse" width="100%" cellspacing="0" cellpadding="0" border="0">
					<tbody>
						<tr>
							<td style="padding:9px" valign="top">
								<table style="min-width:100%;border-collapse:collapse" width="100%" cellspacing="0" cellpadding="0" border="0" align="left">
									<tbody>
										<tr>
											<td style="padding-right:9px;padding-left:9px;padding-top:0;padding-bottom:0;text-align:center" valign="top">

												<img alt='.$product['selprod_title'].'" src="'.CommonHelper::generateFullUrl('image','product', array($product['product_id'], "SMALL", $product['selprod_id'], 0, $siteLangId),CONF_WEBROOT_URL).' style="max-width:200px;padding-bottom:0;display:inline!important;vertical-align:bottom;border:0;height:150px;outline:none;text-decoration:none" height="125" align="middle">

											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
				<table style="min-width:100%;border-collapse:collapse" width="100%" border="0" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td style="padding-top:9px" valign="top">
								<table style="max-width:100%;min-width:100%;border-collapse:collapse" width="100%" border="0" align="left" cellspacing="0" cellpadding="0">
									<tbody>
										<tr>
											<td style="padding-top:0;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#202020;font-family:Helvetica;font-size:16px;line-height:150%;text-align:left" valign="top">
												<div style="text-align:center">
													<a href="javascript:void()" style="color:#2baadf;font-weight:normal;font-size:14px;text-decoration:none">
														<span>'.$product['selprod_title'].'</span>

													</a>
													<br>
													<span style="font-size:14px">
														<span style="text-decoration:line-through;opacity:0.4">'.$oldPrice.'</span>
														<span style="color:#000000;font-size:15px;font-weight:700">'.CommonHelper::displayMoneyFormat($product['theprice']).'</span>
													</span>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
								
								
							</td>
						</tr>
					</tbody>
				</table>

			</td>
		</tr>
	</tbody></table>';
	if($countProducts % 3 == 0){
		$str .='</td></tr><tr><td valign="top" style="padding:15px 0">';
	}else{
		$str .='<table align="left" border="0" cellpadding="0" cellspacing="0" width="10" style="border-collapse:collapse">
			<tbody><tr><td valign="top">&nbsp;</td></tr>
		</tbody></table>';
	}
	$countProducts++;
   }
		   $str .= '</td>
		</tr>
	</tbody></table>';


echo $str;