<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php /* <div>
	<?php if($couponsList){ ?>
		<div>
			<div class="heading3 align--center"><?php echo Labels::getLabel("LBL_Apply_Promo_Coupons", $siteLangId); ?></div>
				<ul class="coupon-offers">
				<?php $counter = 1; foreach( $couponsList as $coupon_id=>$coupon ){
				?>
				<li>
				<div class="coupon-code" onClick="triggerApplyCoupon('<?php echo $coupon['coupon_code']; ?>');" title="<?php echo Labels::getLabel("LBL_Click_to_apply_coupon",$siteLangId); ?>"><?php echo $coupon['coupon_code']; ?></div>
				<?php if( $coupon['coupon_description'] != '' ){ ?>				
				<p><?php echo $coupon['coupon_description'];?> </p>           
				<?php } ?>
				</li>
				<?php $counter++; } ?>
			</ul>
			<?php
			$PromoCouponsFrm->setFormTagAttribute('onsubmit','applyPromoCode(this); return false;');
			echo $PromoCouponsFrm->getFormHtml(); ?>
		</div>
	<?php } else {
		echo Labels::getLabel("LBL_No_Copons_offer_is_available_now.", $siteLangId);
	} ?>
</div> */
?>