<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<ul class="columlist links--vertical" id="coupon-category">
<?php if(count($couponCategories)>0){
	$lis = '';
	foreach($couponCategories as $category){
		$lis .= '<li><span class="left"><a href="javascript:void(0)" title="Remove" onClick="removeCouponCategory('.$coupon_id.','.$category['prodcat_id'].');"><i class="icon ion-close-round" data-cat-id="' . $category['prodcat_id'] . '"></i></a></span>';
		$lis .= '<span >' . $category['prodcat_name'].' ('.$category['prodcat_identifier'].')'.'<input type="hidden" value="'.$category['prodcat_id'].'"  name="product_cat[]"></span></li>';
	}
	echo $lis;
} ?>
</ul>