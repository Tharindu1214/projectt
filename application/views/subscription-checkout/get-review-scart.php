<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section-checkout is-completed">
    <div class="selected-panel">
        <div class="selected-panel-type"><?php echo Labels::getLabel('LBL_Review_Subscription_Details', $siteLangId); ?></div>
        <div class="selected-panel-data">
        <?php if (count($subscriptions)) {
                foreach ($subscriptions as $subscription) { ?>
            <p><?php echo $subscription['spackage_name'] ?>--<?php echo SellerPackagePlans::getPlanPriceWithPeriod($subscription, $subscription[SellerPackagePlans::DB_TBL_PREFIX.'price']);?>
                <?php  if ($subscription[SellerPackagePlans::DB_TBL_PREFIX.'trial_interval']>0) {?><span><?php echo SellerPackagePlans::getPlanTrialPeriod($subscription);?></span><?php } ?></p>
            <?php }
        } ?>
        </div>
        <div class="selected-panel-action"><a href="javascript:void(0)" class="btn btn--primary btn--sm ripplelink reviewOrder"><?php echo Labels::getLabel('LBL_Review_Subscription_Details', $siteLangId);?></a></div>
    </div>
</section>
