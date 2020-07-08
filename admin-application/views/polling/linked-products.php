<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<ul class="columlist" id="linked-product">
<?php if(!empty($linkedProducts)){
	$lis= '';
	foreach($linkedProducts as $product){
		$lis .= '<li id="product' . $product['product_id'] . '"><span class="right"><a href="javascript:void(0)" title="Remove" onClick="removeLinkedProduct('.$polling_id.','.$product['product_id'].');"><i class="ion-ios-close" data-product-id="' . $product['product_id'] . '"></i></a></span>';
		$lis .= '<span class="left">' . $product['product_name'].'<input type="hidden" value="'.$product['product_id'].'"  name="product_option[]"></span></li>';
	}
	echo $lis;
} ?>
</ul>