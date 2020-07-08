<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<div class="section-head">
    <div class="section__heading">
        <h2><?php echo Labels::getLabel('LBL_Payment_Summary', $siteLangId); ?></h2>
    </div>
</div>
<?php $rewardPoints = UserRewardBreakup::rewardPointBalance(UserAuthentication::getLoggedUserId()); ?>
<div class="box box--white box--radius p-4">
    <section id="payment" class="section-checkout">
        <div class="row align-items-center mb-4">
            <?php if (empty($cartSummary['cartRewardPoints'])) { ?>
                <?php if ($rewardPoints > 0) { ?>
                <div class="col-md-6 mb-3 mb-md-0">
                    <?php
                    $redeemRewardFrm->setFormTagAttribute('class', 'form form--secondary form--singlefield');
                    $redeemRewardFrm->setFormTagAttribute('onsubmit', 'useRewardPoints(this); return false;');
                    $redeemRewardFrm->setJsErrorDisplay('afterfield');
                    echo $redeemRewardFrm->getFormTag();
                    echo $redeemRewardFrm->getFieldHtml('redeem_rewards');
                    echo $redeemRewardFrm->getFieldHtml('btn_submit');
                    echo $redeemRewardFrm->getExternalJs(); ?>
                    </form>
                    <p class="note">
                    <strong><?php
                    $canBeUsed = min(min($rewardPoints, CommonHelper::convertCurrencyToRewardPoint($cartSummary['cartTotal']-$cartSummary["cartDiscounts"]["coupon_discount_total"])), FatApp::getConfig('CONF_MAX_REWARD_POINT', FatUtility::VAR_INT, 0));
                    echo $canBeUsed; ?></strong>
                    <?php echo Labels::getLabel('LBL_of', $siteLangId); ?>
                    <strong>
                        <?php echo $rewardPoints; ?>
                    </strong>
                    <?php echo Labels::getLabel('LBL_reward_points_available_for_this_order', $siteLangId); ?>
                    (<?php echo CommonHelper::displayMoneyFormat(CommonHelper::convertRewardPointToCurrency($canBeUsed)); ?>)
                    </p>
                </div>
                <?php } ?>
            <?php } else { ?>
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="alert alert--success">
                    <a href="javascript:void(0)" class="close" onClick="removeRewardPoints()" title="<?php echo Labels::getLabel('LBL_Remove_Reward_Points', $siteLangId); ?>"></a>
                    <p><?php echo Labels::getLabel('LBL_Reward_Points', $siteLangId); ?> <strong><?php echo $cartSummary['cartRewardPoints']; ?> (<?php echo CommonHelper::displayMoneyFormat(CommonHelper::convertRewardPointToCurrency($cartSummary['cartRewardPoints'])); ?>)</strong> <?php echo Labels::getLabel('LBL_Successfully_Used', $siteLangId); ?></p>
                </div>
            </div>
            <?php } ?>
            <div class="col-md-6 mb-3">
                <?php if ($userWalletBalance > 0 && $cartSummary['orderNetAmount'] > 0) { ?>
                    <label class="checkbox brand" id="brand_95">
                        <input onChange="walletSelection(this)" type="checkbox" <?php echo ($cartSummary["cartWalletSelected"]) ? 'checked="checked"' : ''; ?> name="pay_from_wallet" id="pay_from_wallet" />
                        <i class="input-helper"></i>
                        <?php if ($cartSummary["cartWalletSelected"]) {
                            echo ''.Labels::getLabel('MSG_Applied_Wallet_Credits', $siteLangId)?>: <?php echo CommonHelper::displayMoneyFormat($cartSummary["WalletAmountCharge"]);
                        } else {
                            echo ''.Labels::getLabel('MSG_Apply_Wallet_Credits', $siteLangId)?>: <?php echo CommonHelper::displayMoneyFormat($userWalletBalance)?>
                        <?php } ?>
                    </label>
                <?php }?>
            </div>
        </div>
        <div class="align-items-center mb-4">
            <?php if ($userWalletBalance > 0 && $cartSummary['orderNetAmount'] > 0) { ?>
                <div>
                    <div id="wallet" class="wallet">
                        <?php if ($cartSummary["cartWalletSelected"]) { ?>
                            <div class="listing--grids">
                                <ul>
                                    <li>
                                        <div class="boxwhite">
                                            <p><?php echo Labels::getLabel('LBL_Payment_to_be_made', $siteLangId); ?></p>
                                            <h5><?php echo CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount']); ?></h5>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="boxwhite">
                                            <p><?php echo Labels::getLabel('LBL_Amount_in_your_wallet', $siteLangId); ?></p>
                                            <h5><?php echo CommonHelper::displayMoneyFormat($userWalletBalance); ?></h5>
                                        </div>
                                        <p class="note">
                                            <i>
                                                <?php echo Labels::getLabel('LBL_Remaining_wallet_balance', $siteLangId);
                                                $remainingWalletBalance = ($userWalletBalance - $cartSummary['orderNetAmount']);
                                                $remainingWalletBalance = ($remainingWalletBalance < 0) ? 0 : $remainingWalletBalance;
                                                echo CommonHelper::displayMoneyFormat($remainingWalletBalance); ?>
                                            </i>
                                        </p>
                                    </li>
                                    <?php /* if( $userWalletBalance < $cartSummary['orderNetAmount'] ){ ?> <li>
                                        <div class="boxwhite">
                                            <p>Select an Option to pay balance</p>
                                            <h5><?php echo CommonHelper::displayMoneyFormat($cartSummary['orderPaymentGatewayCharges']); ?></h5>
                                        </div>
                                    </li> <?php } */ ?>
                                    <?php if ($userWalletBalance >= $cartSummary['orderNetAmount']) { ?>
                                        <li>
                                            <?php $btnSubmitFld = $WalletPaymentForm->getField('btn_submit');
                                            $btnSubmitFld->addFieldTagAttribute('class', 'btn btn--primary-border');

                                            $WalletPaymentForm->developerTags['colClassPrefix'] = 'col-md-';
                                            $WalletPaymentForm->developerTags['fld_default_col'] = 12;
                                            echo $WalletPaymentForm->getFormHtml(); ?>
                                        </li>
                                        <script type="text/javascript">
                                            function confirmOrder(frm) {
                                                var data = fcom.frmData(frm);
                                                var action = $(frm).attr('action');
                                                fcom.updateWithAjax(fcom.makeUrl('Checkout', 'ConfirmOrder'), data, function(ans) {
                                                    $(location).attr("href", action);
                                                });
                                            }
                                        </script>
                                    <?php } ?>
                                </ul>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <?php if ($cartSummary['orderNetAmount'] <= 0) { ?>
                <div class="gap"></div>
                <div id="wallet">
                    <h6><?php echo Labels::getLabel('LBL_Payment_to_be_made', $siteLangId); ?> <strong><?php echo CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount']); ?></strong></h6> <?php
                    $btnSubmitFld = $confirmForm->getField('btn_submit');
                    $btnSubmitFld->addFieldTagAttribute('class', 'btn btn--primary btn--sm');

                    $confirmForm->developerTags['colClassPrefix'] = 'col-md-';
                    $confirmForm->developerTags['fld_default_col'] = 12;
                    echo $confirmForm->getFormHtml(); ?> <div class="gap"></div>
                </div>
            <?php } ?>
        </div>
        <?php
        $gatewayCount=0;
        foreach ($paymentMethods as $key => $val) {
            if (in_array($val['pmethod_code'], $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_PRODUCT])) {
                continue;
            }
            $gatewayCount++;
        }
        if ($cartSummary['orderPaymentGatewayCharges']) { ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="payment_methods_list" <?php echo ($cartSummary['orderPaymentGatewayCharges'] <= 0) ? 'is--disabled' : ''; ?>>
                        <?php if ($cartSummary['orderPaymentGatewayCharges'] && 0 < $gatewayCount && 0 < count($paymentMethods)) { ?>
                            <ul id="payment_methods_tab" class="" data-simplebar>
                                <?php $count=0;
                                foreach ($paymentMethods as $key => $val){
									if($val['pmethod_code'] == "CashOnDelivery" && $codStatus == 0)
										 continue;
									
                                    if (in_array($val['pmethod_code'], $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_PRODUCT])) {
                                        continue;
                                    }
                                    $count++; 
									
									?>
									
                                    <li>
                                        <a href="<?php echo CommonHelper::generateUrl('Checkout', 'PaymentTab', array($orderInfo['order_id'], $val['pmethod_id'])); ?>">
                                            <div class="payment-box">
                                                <i class="payment-icn">
                                                    <img src="<?php echo CommonHelper::generateUrl('Image', 'paymentMethod', array($val['pmethod_id'],'SMALL')); ?>" alt="">
                                                </i>
                                                <span><?php echo $val['pmethod_name']; ?></span>
                                            </div>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } else {
                            echo Labels::getLabel("LBL_Payment_method_is_not_available._Please_contact_your_administrator.", $siteLangId);
                        } ?>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="payment-from">
                        <div class="you-pay">
                            <?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?> :
                            <?php echo CommonHelper::displayMoneyFormat($cartSummary['orderPaymentGatewayCharges']); ?>
                            <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                            <p><?php echo CommonHelper::currencyDisclaimer($siteLangId, $cartSummary['orderPaymentGatewayCharges']); ?></p>
                            <?php } ?>
                        </div>
                        <div class="gap"></div>
                        <div id="tabs-container"></div>
                <?php } ?>
            </section>
        </div>
                </div>
            </div>

<?php if ($cartSummary['orderPaymentGatewayCharges']) { ?>
    <script type="text/javascript">
        var containerId = '#tabs-container';
        var tabsId = '#payment_methods_tab';
        $(document).ready(function() {
            if ($(tabsId + ' LI A.is-active').length > 0) {
                loadTab($(tabsId + ' LI A.is-active'));
            }
            $(tabsId + ' A').click(function() {
                if ($(this).hasClass('is-active')) {
                    return false;
                }
                $(tabsId + ' LI A.is-active').removeClass('is-active');
                $('li').removeClass('is-active');
                $(this).parent().addClass('is-active');
                loadTab($(this));
                return false;
            });
        });

        function loadTab(tabObj) {
            if (!tabObj || !tabObj.length) {
                return;
            }
            $(containerId).html(fcom.getLoader());
            fcom.ajax(tabObj.attr('href'), '', function(response) {
                $(containerId).html(response);
            });
        }
    </script>
<?php } ?>
