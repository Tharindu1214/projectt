<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$frmOrderSrch->setFormTagAttribute('onSubmit','searchOrders(this); return false;');
$frmOrderSrch->setFormTagAttribute('class', 'form');

$keywordFld = $frmOrderSrch->getField('keyword');
$keywordFld->setWrapperAttribute('class','col-lg-4 col-sm-4 col-md-4 ');
$keywordFld->developerTags['col'] = 4;
/* $keywordFld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_Buyer_account_orders_listing_search_form_keyword_help_txt', $siteLangId).'</small>'; */

/* $statusFld = $frmOrderSrch->getField('status');
$statusFld->setWrapperAttribute('class','col-sm-6');
$statusFld->developerTags['col'] = 4; */

$dateFromFld = $frmOrderSrch->getField('date_from');
$dateFromFld->setFieldTagAttribute('class','field--calender');
$dateFromFld->setWrapperAttribute('class','col-lg-2 col-sm-2 col-md-2 ');
$dateFromFld->developerTags['col'] = 2;

$dateToFld = $frmOrderSrch->getField('date_to');
$dateToFld->setFieldTagAttribute('class','field--calender');
$dateToFld->setWrapperAttribute('class','col-lg-2 col-sm-2 col-md-2 ');
$dateToFld->developerTags['col'] = 2;

/* $priceFromFld = $frmOrderSrch->getField('price_from');
$priceFromFld->setWrapperAttribute('class','col-sm-6');
$priceFromFld->developerTags['col'] = 2;

$priceToFld = $frmOrderSrch->getField('price_to');
$priceToFld->setWrapperAttribute('class','col-sm-6');
$priceToFld->developerTags['col'] = 2; */

$submitBtnFld = $frmOrderSrch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class','btn--block');
$submitBtnFld->setWrapperAttribute('class','col-lg-2 col-sm-2 col-md-2 ');
$submitBtnFld->developerTags['col'] = 2;

$cancelBtnFld = $frmOrderSrch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class','btn btn--primary-border btn--block');
$cancelBtnFld->setWrapperAttribute('class','col-lg-2 col-sm-2 col-md-2 ');
$cancelBtnFld->developerTags['col'] = 2;
?> <?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?> <main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?> <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_My_Subscriptions',$siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="cards">
                        <div class="cards-header p-4">
                            <h5 class="cards-title"><?php echo Labels::getLabel('LBL_Search_Subscriptions',$siteLangId);?></h5> <?php if($currentActivePlan) {
                                            if(strtotime(date("Y-m-d"))>=strtotime('-3 day',strtotime($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])) ){
                                                if($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::PAID_TYPE && FatDate::diff(date("Y-m-d"),$currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])>0 ){
                                                    $message = sprintf(Labels::getLabel('MSG_Your_Subscription_is_going_to_expire_in_%s_day(s),Please_maintain_your_wallet_to_continue_your_subscription,_Amount_required_%s',$siteLangId),FatDate::diff(date("Y-m-d"),$currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date']),CommonHelper::displayMoneyFormat($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'price']));
                                                }else if($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::PAID_TYPE && FatDate::diff(date("Y-m-d"),$currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])==0 ){
                                                    $message = sprintf(Labels::getLabel('MSG_Your_Subscription_is_going_to_expire_today,_Please_maintain_your_wallet_to_continue_your_subscription,_Amount_required_%s',$siteLangId),CommonHelper::displayMoneyFormat($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'price']));
                                                }else if($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::PAID_TYPE && FatDate::diff(date("Y-m-d"),$currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])<0 && $autoRenew ){
                                                    $message = sprintf(Labels::getLabel('MSG_Your_Subscription_has_been_expired,Please_purchase_new_plan_or_maintain_your_wallet_to_continue_your_subscription,_Amount_required_%s',$siteLangId),CommonHelper::displayMoneyFormat($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'price']));
                                                }else if($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::PAID_TYPE && FatDate::diff(date("Y-m-d"),$currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])<0  && !$autoRenew){
                                                    $message = sprintf(Labels::getLabel('MSG_Your_Subscription_has_been_expired,Please_purchase_new_plan_or_add_%s_in_your_wallet_before_renewing_your_subscription',$siteLangId),CommonHelper::displayMoneyFormat($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'price']));
                                                }elseif($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::FREE_TYPE && FatDate::diff(date("Y-m-d"),$currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])>0 ){
                                                    $message = sprintf(Labels::getLabel('MSG_Your_Free_Subscription_is_going_to_expire_in_%s_day(s),Please_Purchase_new_Subscription_to_continue_services',$siteLangId),FatDate::diff(date("Y-m-d"),$currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date']));
                                                }elseif($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::FREE_TYPE && FatDate::diff(date("Y-m-d"),$currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])==0 ){
                                                    $message = sprintf(Labels::getLabel('MSG_Your_Free_Subscription_is_going_to_expire_today,_Please_Purchase_new_Subscription_to_continue_services',$siteLangId));
                                                }elseif($currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'type']==SellerPackages::FREE_TYPE && FatDate::diff(date("Y-m-d"),$currentActivePlan[OrderSubscription::DB_TBL_PREFIX.'till_date'])<0 ){
                                                    $message = Labels::getLabel('MSG_Your_Free_Subscription_has_been_expired,Please_Purchase_new_Subscription_to_continue_services',$siteLangId);
                                                }
                                            ?> <?php
                                }
                                        }
                                    ?> <div class="auto-renew">
                                <p><?php echo Labels::getLabel('LBL_AutoRenew_Subscription', $siteLangId); ?></p>
                                    <?php
                                     $active = "";
                                     if ($autoRenew) {
                                        $active = 'checked';
                                     }
                                     $onOffArr = applicationConstants::getOnOffArr($siteLangId);
                                     ?>
                                     <label class="toggle-switch mb-0">
                                         <input <?php echo $active; ?> type="checkbox" onclick="toggleAutoRenewal()">
                                         <div class="slider round"></div>
                                     </label>
                            </div>
                        </div>
                        <?php if(isset($message)){ ?>
                            <p class="highlighted-note"> <?php  echo $message;?> </p>
                        <?php }?>
                        <div class="cards-content pl-4 pr-4 ">
                            <div class="replaced"> <?php echo $frmOrderSrch->getFormHtml(); ?> </div>
                            <span class="gap"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="cards">
                        <div class="cards-header p-4">
                        </div>
                        <div class="cards-content pl-4 pr-4 ">
                            <div id="ordersListing"><?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?></div>
                            <span class="gap"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
