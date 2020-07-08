<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<ul class="columlist links--vertical" id="coupon-product">
<?php if(count($couponProducts)>0){
	$lis = '';
	foreach($couponProducts as $product){
		$lis .= '<li><span class="left"><a href="javascript:void(0)" title="Remove" onClick="removeCouponProduct('.$coupon_id.','.$product['product_id'].');"><i class="icon ion-close-round" data-prod-id="' . $product['product_id'] . '"></i></a></span>';
		$lis .= '<span>' . $product['product_name'].' ('.$product['product_identifier'].')'.'<input type="hidden" value="'.$product['product_id'].'"  name="products[]"></span></li>';
	}
	echo $lis;
} ?>
</ul>