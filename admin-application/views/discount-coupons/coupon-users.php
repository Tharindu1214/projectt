<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<ul class="columlist links--vertical" id="coupon-user">
<?php if(count($couponUsers)>0){
	$lis = '';
	foreach($couponUsers as $user){
		$lis .= '<li><span class="left"><a href="javascript:void(0)" title="Remove" onClick="removeCouponUser('.$coupon_id.','.$user['user_id'].');"><i class="icon ion-close-round" data-prod-id="' . $user['user_id'] . '"></i></a></span>';
		$lis .= '<span >' . $user['user_name'].' ('.$user['credential_username'].')'.'<input type="hidden" value="'.$user['user_id'].'"  name="products[]"></span></li>';
	}
	echo $lis;
} ?>
</ul>
