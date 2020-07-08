<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<section class="section-checkout is-completed">
	<div class="selected-panel">
		<div class="selected-panel-type"><?php echo Labels::getLabel('LBL_Shipping_Summary',$siteLangId); ?></div>
		<div class="selected-panel-data">
			<?php 
			if( count($products) ){
			foreach( $products as $product ){?>
			<p><?php echo $product['selprod_title']; ?> - <span class="shipping-price"><?php 
			if($product['shop_eligible_for_free_shipping'] && $product['psbs_user_id'] > 0){
				echo '<strike>'.CommonHelper::displayMoneyFormat($product['shipping_cost']).'</strike> '.CommonHelper::displayMoneyFormat(0);
			}else{
			echo CommonHelper::displayMoneyFormat($product['shipping_cost']);} ?></span></p>
			<?php } 
			} ?>
		</div>
		<div class="selected-panel-action"><a href="javascript:void(0);" onclick="changeShipping()"; class="btn btn--primary btn--sm ripplelink"><?php echo Labels::getLabel('LBL_Change_Shipping',$siteLangId); ?></a></div>
	</div>
</section>