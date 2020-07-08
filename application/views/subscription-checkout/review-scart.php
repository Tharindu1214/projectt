<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="section-head">
    <div class="section__heading">
        <h2><?php echo Labels::getLabel('LBL_Review_Order', $siteLangId); ?></h2>
    </div>
</div>
<div class="box box--white box--radius p-4">
    <div class="review-wrapper">
        <div class="short-detail">
            <table class="table cart--full js-scrollable">
                <tbody>
                <?php if (count($subscriptions)) {
                    foreach ($subscriptions as $subscription) { ?>
                    <tr>
                        <td>
                            <div class="item__title"><a href="javascript:void(0)"><?php echo $subscription['spackage_name'] ?> --<?php echo SellerPackagePlans::getPlanPriceWithPeriod($subscription, $subscription[SellerPackagePlans::DB_TBL_PREFIX.'price']); ?>
                            <?php if ($subscription[SellerPackagePlans::DB_TBL_PREFIX.'trial_interval']>0) { ?>
                                <span><?php /* echo SellerPackagePlans::getPlanTrialPeriod($subscription); */?></span>
                            <?php } ?></a>
                            </div>
                        </td>
                        <td class="text-right" width="5%">
                            <a href="javascript:void(0)" onclick="subscription.remove('<?php echo md5($subscription['key']); ?>')" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>"
                                class="icons-wrapper"><i class="icn"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin"></use>
                                        </svg></i></a>
                        </td>
                    </tr>
                    <?php }
                } else {  ?>
                    <tr>
                        <td colspan="2" class="text-left">
                            <?php echo Labels::getLabel('LBL_Your_cart_is_empty', $siteLangId); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="cartdetail__footer">
            <table>
                <tr>
                    <td class="text-left"><?php echo Labels::getLabel('LBL_Sub_Total', $siteLangId);?></td>
                    <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($scartSummary['cartTotal']);?></td>
                </tr>
                <?php  if (!empty($scartSummary['cartAdjustableAmount'])) { ?>
                <tr>
                    <td class="text-left"><?php echo Labels::getLabel('LBL_Adjusted_Amount', $siteLangId); ?> </td>
                    <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($scartSummary['cartAdjustableAmount']); ?></td>
                </tr>
                <?php } ?>
                <?php if (!empty($scartSummary['cartDiscounts'])) { ?>
                <tr>
                    <td class="text-left"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></td>
                    <td class="text-right"><?php echo CommonHelper::displayMoneyFormat($scartSummary['cartDiscounts']['coupon_discount_total']); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <td class="text-left hightlighted"><?php echo Labels::getLabel('LBL_You_Pay', $siteLangId);?></td>
                    <td class="text-right hightlighted">
                        <?php echo $amount = CommonHelper::displayMoneyFormat($scartSummary['cartTotal']-$scartSummary['cartAdjustableAmount']-$scartSummary['cartDiscounts']['coupon_discount_total']);?></td>


                </tr>
            </table>
        </div>
    </div>
    <div class="row align-items-center justify-content-between mt-4">
        <div class="col"></div>
        <div class="col-auto">
            <?php if ($amount>0) {
                $paymentText=Labels::getLabel('LBL_Proceed_To_Pay', $siteLangId) ;
            } else {
                $paymentText=Labels::getLabel('LBL_Proceed_To_Confirm', $siteLangId) ;
            } ?>
            <a href="javascript:void(0)" class="btn btn--primary ripplelink block-on-mobile confirmReview"><?php echo $paymentText ;?></a>
        </div>
    </div>
</div>
