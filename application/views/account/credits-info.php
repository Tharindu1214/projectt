<!-- wallet balance[ -->
<?php
$showTotalBalanceAvailableDiv = false;
$divCol = 12;
if ($userTotalWalletBalance != $userWalletBalance || ($promotionWalletToBeCharged) || ($withdrawlRequestAmount)) {
    $showTotalBalanceAvailableDiv = true;
    $divCol = 4;
} ?>
<div class="row">
    <?php if ($showTotalBalanceAvailableDiv) { ?>
        <div class="col-lg-8 col-md-12 mb-3 mb-md-0">
            <div class="balancebox border h-100 text-center rounded p-3">

                    <div class="credits-number">
                        <ul>
                            <?php if ($userTotalWalletBalance != $userWalletBalance) { ?>
                            <li>
                                <span class="total"><?php echo Labels::getLabel('LBL_Wallet_Balance', $siteLangId); ?>: </span>
                                <span class="total-numbers"><strong><?php echo CommonHelper::displayMoneyFormat($userTotalWalletBalance); ?></strong></span>
                                <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>

                                    <small>
                                        <?php echo Labels::getLabel('LBL_Approx.', $siteLangId); ?>
                                        <?php echo CommonHelper::displayMoneyFormat($userTotalWalletBalance, true, true); ?>
                                    </small>
                                <?php } ?>
                            </li>
                            <?php } ?>
                            <?php if ($promotionWalletToBeCharged || $withdrawlRequestAmount) { ?>
                            <li>
                                <?php if ($promotionWalletToBeCharged) { ?>
                                <span class="total"><?php echo Labels::getLabel('LBL_Pending_Promotions_Charges', $siteLangId); ?>:</span>
                                <span class="total-numbers"> <strong>
                                    <?php echo CommonHelper::displayMoneyFormat($promotionWalletToBeCharged); ?></strong></span>
                                    <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                                        <small>
                                            <?php echo Labels::getLabel('LBL_Approx.', $siteLangId);
                                            echo CommonHelper::displayMoneyFormat($promotionWalletToBeCharged, true, true); ?>
                                        </small>
                                    <?php } ?>
                                <?php } ?>
                                <?php if ($withdrawlRequestAmount) { ?>
                                    <span class="total"><?php echo Labels::getLabel('LBL_Pending_Withdrawl_Requests', $siteLangId); ?>:</span>
                                    <span class="total-numbers"> <strong>
                                    <?php echo CommonHelper::displayMoneyFormat($withdrawlRequestAmount); ?></strong></span>
                                    <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                                        <small><?php echo Labels::getLabel('LBL_Approx.', $siteLangId); ?> <?php echo CommonHelper::displayMoneyFormat($withdrawlRequestAmount, true, true); ?></small>
                                    <?php } ?>
                                <?php } ?>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>

            </div>
        </div>
    <?php } ?>
    <div class="col-lg-<?php echo $divCol; ?> col-md-<?php echo $divCol; ?> col-md-12">
        <div class="balancebox border h-100 rounded text-center p-3">
            <p><?php echo Labels::getLabel('LBL_Available_Balance', $siteLangId);?>: </p>
            <h2><strong>
                <?php echo CommonHelper::displayMoneyFormat($userWalletBalance);?></strong></h2>
                <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                    <small class="d-block"><?php echo Labels::getLabel('LBL_Approx.', $siteLangId); ?> <?php echo CommonHelper::displayMoneyFormat($userWalletBalance, true, true); ?></small>
                <?php } ?>

                <a href="javascript:void(0)" onClick="withdrawalReqForm()" class="btn btn--secondary btn--sm"><?php echo Labels::getLabel('LBL_Request_Withdrawal', $siteLangId); ?></a>
        </div>
    </div>
</div>
