<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
if( $products ){
	echo '<ul class="columlist">';
	foreach( $products as $product ){
		$productStr = '<span class="left">' . $product['product_name'];
		
		if( is_array($product['options']) && count($product['options']) ){
			$productStr .= ' (';
			$counter = 1;
			foreach($product['options'] as $op){
				$productStr .= $op['option_name'].': '.$op['optionvalue_name'];
				if( $counter != count($product['options']) ){
					$productStr .= ', ';
				}
				$counter++;
			}
			$productStr .= ' )';
		}
		$productStr .= '</span>';
		
		$productStr .= '<span class="right">';
		
		$productStr .= '<a href="javascript:void(0)" title="'.Labels::getLabel('LBL_Remove', $siteLangId).'" onclick="removeProductFromGroup('.$prodgroup_id.', '.$product['selprod_id'].');"> '.Labels::getLabel('LBL_Remove', $siteLangId).'</a>';
		
		if( !$product['ptg_is_main_product'] ){
		$productStr .= '<a href="javascript:void(0)" title="'.Labels::getLabel('LBL_Set_as_main_product', $siteLangId).'" onclick="setMainProductFromGroup('.$prodgroup_id.', '.$product['selprod_id'].');"> '.Labels::getLabel('LBL_Set_as_main_product', $siteLangId).'</a>';
		}
		
		$productStr .= '</span>';
		
		/* $setMainProductBtn = '';
		if( !$product['ptg_is_main_product'] ){
			$setMainProductBtn = '<a href="">Set as Main Product</a>';
		} */
		
		echo '<li>'.$productStr.'</li>';
	}
	echo '</ul>';
} else {
	echo Labels::getLabel('LBL_No_Products_found_under_this_batch', $siteLangId);
}