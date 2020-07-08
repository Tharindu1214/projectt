<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="section-head">
    <div class="section__heading">
        <h2><?php echo Labels::getLabel('LBL_Payment_Summary', $siteLangId); ?></h2>
    </div>
</div>
<?php $rewardPoints = UserRewardBreakup::rewardPointBalance(UserAuthentication::getLoggedUserId()); ?>
<div class="box box--white box--radius p-4">
    <section id="payment" class="section-checkout">
        <?php /*if ($rewardPoints > 0) { ?>
            <div class="section-head">
                <div class="section__heading">
                    <h6>
                        <?php echo Labels::getLabel('LBL_Reward_Point_in_your_account', $siteLangId); ?>
                        <strong>
                            <?php echo $rewardPoints; ?>
                        </strong>
                        (<?php echo CommonHelper::displayMoneyFormat(CommonHelper::convertRewardPointToCurrency(UserRewardBreakup::rewardPointBalance(UserAuthentication::getLoggedUserId()))); ?>)
                        <?php echo Labels::getLabel('LBL_You_can_use_upto_', $siteLangId); ?>
                        <strong><?php echo min(min($rewardPoints, CommonHelper::convertCurrencyToRewardPoint($cartSummary['cartTotal']-$cartSummary["cartDiscounts"]["coupon_discount_total"])), FatApp::getConfig('CONF_MAX_REWARD_POINT', FatUtility::VAR_INT, 0)); ?></strong>
                    </h6>
                </div>
            </div>
            <div class="align-items-center mb-4">
                <div class="">
                    <?php
                        $redeemRewardFrm->setFormTagAttribute('class', 'form form--secondary form--singlefield');
                        $redeemRewardFrm->setFormTagAttribute('onsubmit', 'useRewardPoints(this); return false;');
                        $redeemRewardFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
                        $redeemRewardFrm->developerTags['fld_default_col'] = 12;
                        echo $redeemRewardFrm->getFormTag();
                        echo $redeemRewardFrm->getFieldHtml('redeem_rewards');
                        echo $redeemRewardFrm->getFieldHtml('btn_submit');
                        echo $redeemRewardFrm->getExternalJs(); ?>
                        </form>
                        <div class="gap"></div>
                    <?php if (!empty($cartSummary['cartRewardPoints'])) { ?>
                        <div class="alert alert--success relative">
                            <a href="javascript:void(0)" class="close" onClick="removeRewardPoints()"></a>
                            <p><?php echo Labels::getLabel('LBL_Reward_Points', $siteLangId); ?> <strong><?php echo $cartSummary['cartRewardPoints']; ?></strong> <?php echo Labels::getLabel('LBL_Successfully_Used', $siteLangId); ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } */?>
        <div class="align-items-center mb-4">
            <?php if ($userWalletBalance > 0 && $cartSummary['orderNetAmount'] > 0) { ?>
                <div>
                    <div id="wallet" class="wallet">
                        <label class="checkbox brand" id="brand_95">
                            <input onChange="walletSelection(this)" type="checkbox" <?php echo ($cartSummary["cartWalletSelected"]) ? 'checked="checked"' : ''; ?> name="pay_from_wallet" id="pay_from_wallet" />
                            <i class="input-helper"></i>
                            <?php if ($cartSummary["cartWalletSelected"] && $userWalletBalance >= $cartSummary['orderNetAmount']) {
                                echo '<strong>'.Labels::getLabel('LBL_Sufficient_balance_in_your_wallet', $siteLangId).'</strong>'; //';
                            } else {
                                echo '<strong>'.Labels::getLabel('MSG_Use_My_Wallet_Credits', $siteLangId)?>: (<?php echo CommonHelper::displayMoneyFormat($userWalletBalance)?>)</strong>
                            <?php } ?>
                        </label>

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
                                                var action = $(frm).attr('action')
                                                fcom.updateWithAjax(fcom.makeUrl('SubscriptionCheckout', 'ConfirmOrder'), data, function(ans) {
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
            <?php } if ($subscriptionType == SellerPackages::PAID_TYPE) { ?>
                    <p class="note"><?php echo Labels::getLabel('LBL_Note_Please_Maintain_Wallet_Balance_for_further_auto_renewal_payments', $siteLangId); ?></p>
                    <div class="gap"></div>
            <?php } ?>
                <?php if ($cartSummary['orderNetAmount'] <= 0) { ?>
                    <div class="gap"></div>
                    <div id="wallet">
                        <h6><?php echo Labels::getLabel('LBL_Payment_to_be_made', $siteLangId); ?> <strong>
                            <?php
                            $btnSubmitFld = $confirmPaymentFrm->getField('btn_submit');
                            $btnSubmitFld->addFieldTagAttribute('class', 'btn btn--primary');

                            $confirmPaymentFrm->developerTags['colClassPrefix'] = 'col-md-';
                            $confirmPaymentFrm->developerTags['fld_default_col'] = 12;
                            echo $confirmPaymentFrm->getFormHtml(); ?>
                             <div class="gap"></div>
                             <script type="text/javascript">
                                 function confirmOrder(frm) {
                                     var data = fcom.frmData(frm);
                                     var action = $(frm).attr('action')
                                     fcom.updateWithAjax(fcom.makeUrl('SubscriptionCheckout', 'ConfirmOrder'), data, function(ans) {
                                         $(location).attr("href", action);
                                     });
                                 }
                             </script>
                    </div>
                <?php } ?>
        </div>
        <?php
        $gatewayCount=0;
        foreach ($paymentMethods as $key => $val) {
            if (in_array($val['pmethod_code'], $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_SUBSCRIPTION])) {
                continue;
            }
            $gatewayCount++;
        }
        if ($cartSummary['orderPaymentGatewayCharges']) { ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="payment_methods_list mb-4" <?php echo ($cartSummary['orderPaymentGatewayCharges'] <= 0) ? 'is--disabled' : ''; ?>>
                        <?php if ($cartSummary['orderPaymentGatewayCharges'] && 0 < $gatewayCount && 0 < count($paymentMethods)) { ?>
						<?php if ($paymentMethods) { ?>
								<ul id="payment_methods_tab" data-simplebar>
								   <?php $count=0;
									foreach ($paymentMethods as $key => $val) {
										if (in_array($val['pmethod_code'], $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_SUBSCRIPTION])) {
											continue;
										}
										$count++; ?>
										<li>
											<a href="<?php echo CommonHelper::generateUrl('SubscriptionCheckout', 'PaymentTab', array($orderInfo['order_id'], $val['pmethod_id'])); ?>">
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
							<?php } ?>
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
                    </div>
                </div>
            </div>
        <?php } ?>
    </section>
</div>

<?php if ($cartSummary['orderPaymentGatewayCharges']) { ?>
    <script type="text/javascript">
        var containerId = '#tabs-container';
        var tabsId = '#payment_methods_tab';
        $(document).ready(function() {
            if ($(tabsId + ' li a.is-active').length > 0) {
                loadTab($(tabsId + ' li a.is-active'));
            }
            $(tabsId + ' a').click(function() {
                console.log("First li clicked");
                if ($(this).hasClass('is-active')) {
                    return false;
                }
                $(tabsId + ' li a.is-active').removeClass('is-active');
                $('li').removeClass('is-active');
                $(this).parent().addClass('is-active');
                loadTab($(this));
                return false;
            });
            $(tabsId +" li:first-child a").click();
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
<?php }
