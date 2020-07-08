<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<ul class="columlist links--vertical" id="coupon-plan">
<?php if(count($couponPlans)>0){
	$lis = '';
	foreach($couponPlans as $plan){
		$lis .= '<li><span class="left"><a href="javascript:void(0)" title="Remove" onClick="removeCouponPlan('.$coupon_id.','.$plan['spplan_id'].');"><i class="icon ion-close-round" data-prod-id="' . $plan['spplan_id'] . '"></i></a></span>';
		$lis .= '<span >' . DiscountCoupons::getPlanTitle($plan,$adminLangId).' ('.DiscountCoupons::getPlanTitle($plan,$adminLangId).')'.'<input type="hidden" value="'.$plan['spplan_id'].'"  name="plans[]"></span></li>';
	}
	echo $lis;
} ?>
</ul>