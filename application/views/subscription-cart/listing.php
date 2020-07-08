<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<div class="tbl-heading"><?php echo Labels::getLabel('LBL_Shopping_Cart', $siteLangId); ?> </div>
<table class="table cart--full item-yk">
  <thead>
    <tr>
      <th><?php echo Labels::getLabel('LBL_Order_Particulars',$siteLangId); ?></th>
      <th><?php echo Labels::getLabel('LBL_Price',$siteLangId); ?></th>
      <th><?php echo Labels::getLabel('LBL_SubTotal',$siteLangId); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php 
	if( count($subscriptionArr) ){
		foreach( $subscriptionArr as $subscription ){
		
			?>
    <tr >
      <td>
        <div class="item__head">
          <div class="item__title"><a href="javascript:void(0)"><?php echo $subscription['spackage_name'] ?></a></div>
        </div>
        <div class="gap"></div>
        <a href="<?php echo CommonHelper::generateUrl('seller','packages');?>" class="btn btn--sm btn--gray ripplelink"><?php echo Labels::getLabel('LBL_Edit',$siteLangId); ?></a> <a href="javascript:void(0)" onclick="subscription.remove('<?php echo md5($subscription['key']); ?>')" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>" class="btn btn--sm btn--gray ripplelink"><?php echo Labels::getLabel('LBL_Remove',$siteLangId); ?></a></td>
      <td><div class="product_price product--price"><?php echo SellerPackagePlans::getPlanPriceWithPeriod($subscription,$subscription[SellerPackagePlans::DB_TBL_PREFIX.'price']);?>
          <?php if($subscription[SellerPackagePlans::DB_TBL_PREFIX.'trial_interval']>0){?>
          <span><?php echo SellerPackagePlans::getPlanTrialPeriod($subscription);?></span>
          <?php } ?>
        </div></td>
      <td><span class="hide--desktop mobile-thead"><?php echo Labels::getLabel('LBL_SubTotal',$siteLangId); ?></span>
        <div class="product_price"><?php echo CommonHelper::displayMoneyFormat($subscription[SellerPackagePlans::DB_TBL_PREFIX.'price']); ?></div></td>
    </tr>
    <?php
		
	  }
	}
	 ?>
  </tbody>
  <tfoot>
  </tfoot>
</table>
<div class="cart-footer">
  <div class="cartdetail__footer">
    <table>
      <tbody>
        <tr>
          <td class="text-left"><?php echo Labels::getLabel('LBL_Total', $siteLangId); ?></td>
          <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></td>
        </tr>
        <tr>
          <td class="text-left hightlighted"><?php echo Labels::getLabel('LBL_You_Pay', $siteLangId); ?></td>
          <td class="text-right hightlighted"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></td>
        </tr>
        <tr>
          <td colspan="2" class="text-right"><a href="<?php echo CommonHelper::generateUrl('SubscriptionCheckout'); ?>" class="btn btn--primary-border ripplelink"><?php echo Labels::getLabel('LBL_Proceed_to_Pay', $siteLangId); ?> </a></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>