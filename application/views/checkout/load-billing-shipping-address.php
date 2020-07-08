<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<?php  if(isset($defaultAddress) && $defaultAddress) { ?>
	<section class="section-checkout is-completed" id="addressDivFooter">
			<div class="selected-panel">
			<?php if($hasPhysicalProduct){ ?>
			  <div class="selected-panel-type"><?php echo Labels::getLabel('LBL_Billing/Delivery_Address', $siteLangId)?></div>
			<?php }else{
				
			?>
			<div class="selected-panel-type"><?php echo Labels::getLabel('LBL_Billing_Address', $siteLangId)?></div>
			<?php } ?>
			<div class="selected-panel-data"><?php echo $defaultAddress['ua_name']; ?><br>
			<?php echo $defaultAddress['ua_address1'];?>, <?php /* echo (strlen($defaultAddress['ua_zip'])>0) ? Labels::getLabel('LBL_Zip:', $siteLangId).$defaultAddress['ua_zip'].'<br>':''; */ ?>
			<?php echo (strlen($defaultAddress['ua_phone'])>0) ? Labels::getLabel('LBL_Phone:', $siteLangId).$defaultAddress['ua_phone'].'<br>':'';?></div>
			<div class="selected-panel-action"><a href="javascript:void(0)" onClick="showAddressList()" class="btn btn--primary btn--sm ripplelink"><?php echo Labels::getLabel('LBL_Change_Address', $siteLangId); ?></a></div>
		</div>
	</section>
<?php  } ?>