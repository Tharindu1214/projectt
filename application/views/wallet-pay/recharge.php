<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php $gatewayCount=0; foreach ($paymentMethods as $key => $val) {
    if (in_array($val['pmethod_code'], $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_ADD_MONEY_TO_WALLET])) {
        continue;
    }
    $gatewayCount++;
} ?>
<section class="section bg-gray-dark">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="section-head  section--head--center">
                    <div class="section__heading">
                        <h2><?php echo Labels::getLabel('LBL_Add_Money_to_wallet', $siteLangId); ?></h2>
                    </div>
                </div>
                <div class="box box--white box--radius p-4">
                    <section id="payment" class="section-checkout">
                        <?php if ($orderInfo['order_net_amount']) { ?>
                        <div class="row">
                            <?php if ($gatewayCount > 0) { ?>
                            <div class="col-md-4 mb-4 mb-md-0 ">
                                <div class="payment_methods_list" <?php echo (count($paymentMethods) <= 0) ? 'is--disabled' : ''; ?>>
                                    <?php if ($paymentMethods) { ?>
                                    <ul id="payment_methods_tab" class="simplebar-horizontal" data-simplebar>
                                        <?php $count=0;
                                        foreach ($paymentMethods as $key => $val) {
                                            if (in_array($val['pmethod_code'], $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_ADD_MONEY_TO_WALLET])) {
                                                continue;
                                            }
                                            $count++; ?>
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
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="payment-here">
                                    <div class="you-pay">
                                        <?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?> : <?php echo CommonHelper::displayMoneyFormat($orderInfo['order_net_amount']); ?>
                                        <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) {?>
                                        <p><?php echo CommonHelper::currencyDisclaimer($siteLangId, $orderInfo['order_net_amount']);  ?></p>
                                        <?php } ?>
                                    </div>
                                    <div class="gap"></div>
                                    <!--<div class="heading4"><?php //echo Labels::getLabel('LBL_Pay_With_Credit_Card', $siteLangId);?></div>-->
                                    <div id="tabs-container"></div>
                                </div>
                            </div>
                            <?php } else {
                                echo Labels::getLabel("LBL_Payment_method_is_not_available._Please_contact_your_administrator.", $siteLangId);
                            } ?>
                        </div>
                        <?php } ?>
                    </section>
                </div>
            </div>
        </div>
    </div>
</section>
<?php if ($orderInfo['order_net_amount']) { ?>
<script type="text/javascript">
    var containerId = '#tabs-container';
    var tabsId = '#payment_methods_tab';
    $(document).ready(function() {
        if ($(tabsId + ' li a.is-active').length > 0) {
            loadTab($(tabsId + ' li A.is-active'));
        }
        $(tabsId + ' a').click(function() {
            if ($(this).hasClass('is-active')) {
                return false;
            }
            $(tabsId + ' li A.is-active').removeClass('is-active');
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
        //$(containerId).fadeOut('fast');
        fcom.ajax(tabObj.attr('href'), '', function(response) {
            $(containerId).html(response);
        });
        /* $(containerId).load( tabObj.attr('href'), function(){
            //$(containerId).fadeIn('fast');
        }); */
    }
</script>
<?php } ?>
