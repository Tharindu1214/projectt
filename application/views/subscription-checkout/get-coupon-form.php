<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<span class="gap"></span>	
<?php if(!empty($cartSummary['cartDiscounts']['coupon_code'])){ ?>
<div class="alert alert--success">
<a href="javascript:void(0)" class="close" onClick="removePromoCode()"></a>
<p><?php echo Labels::getLabel('LBL_Promo_Code',$siteLangId);?> <strong><?php echo $cartSummary['cartDiscounts']['coupon_code'];?></strong> <?php echo Labels::getLabel('LBL_Successfully_Applied',$siteLangId);?></p>
</div>
<?php }?>
<div id="coupons-modaal">
	<div class="heading3 align--center"><?php echo Labels::getLabel('LBL_Apply_Promo_Coupons',$siteLangId);?></div>
	<div class="gap"></div>
	<?php 
		$PromoCouponsFrm->setFormTagAttribute('class','form apply--coupon--form custom-form');
		$PromoCouponsFrm->setFormTagAttribute('onsubmit','applyPromoCode(this); return false;');
		$PromoCouponsFrm->getField('onsubmit','applyPromoCode(this); return false;');
		$PromoCouponsFrm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-6 col-sm-';
		$PromoCouponsFrm->developerTags['fld_default_col'] = 6;
		$PromoCouponsFrm->setJsErrorDisplay('afterfield');
		echo $PromoCouponsFrm->getFormTag();
		echo $PromoCouponsFrm->getFieldHtml('coupon_code'); 
		echo $PromoCouponsFrm->getFieldHtml('btn_submit');
		echo $PromoCouponsFrm->getExternalJs();
	?>
	</form>
</div>

<div class="gap"></div><div class="gap"></div>
<div>
	<?php if($couponsList){ ?>
	<div>
		<div class="heading3 align--center"><?php echo Labels::getLabel("LBL_Available_Coupons", $siteLangId); ?></div>
		<ul class="coupon-offers">
			<?php $counter = 1; foreach( $couponsList as $coupon_id=>$coupon ){ ?>
			<li>
				<div class="coupon-code" onClick="triggerApplyCoupon('<?php echo $coupon['coupon_code']; ?>');" title="<?php echo Labels::getLabel("LBL_Click_to_apply_coupon",$siteLangId); ?>"><?php echo $coupon['coupon_code']; ?></div>
				<?php if( $coupon['coupon_description'] != '' ){ ?>				
				<p><?php echo $coupon['coupon_description'];?> </p>           
				<?php } ?>
			</li>
			<?php $counter++; } ?>
		</ul>
	</div>
	<?php } else {
	echo Labels::getLabel("LBL_No_Copons_offer_is_available_now.", $siteLangId);
	} ?>
</div>