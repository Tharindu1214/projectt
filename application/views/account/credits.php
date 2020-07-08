<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmSrch->setFormTagAttribute('onSubmit', 'searchCredits(this); return false;');
$frmSrch->setFormTagAttribute('class', 'form');
$frmSrch->developerTags['colClassPrefix'] = 'col-md-';
$frmSrch->developerTags['fld_default_col'] = 12;

$keyFld = $frmSrch->getField('keyword');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-6');
$keyFld->developerTags['col'] = 6;
$keyFld->developerTags['noCaptionTag'] = true;

$keyFld = $frmSrch->getField('debit_credit_type');
$keyFld->setWrapperAttribute('class', 'col-lg-6');
$keyFld->developerTags['col'] = 6;
$keyFld->developerTags['noCaptionTag'] = true;

$keyFld = $frmSrch->getField('date_from');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_From_Date', $siteLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-4');
$keyFld->developerTags['col'] = 4;
$keyFld->developerTags['noCaptionTag'] = true;

$keyFld = $frmSrch->getField('date_to');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_To_Date', $siteLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-4');
$keyFld->developerTags['col'] = 4;
$keyFld->developerTags['noCaptionTag'] = true;

/* $keyFld = $frmSrch->getField('date_order');
$keyFld->setWrapperAttribute('class','col-lg-6');
$keyFld->developerTags['col'] = 6; */

$submitBtnFld = $frmSrch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn--block');
$submitBtnFld->setWrapperAttribute('class', 'col-lg-2');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmSrch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn--block');
$cancelBtnFld->setWrapperAttribute('class', 'col-lg-2');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?> <?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?> <main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?> <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_My_Credits', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="cards">
                        <div id="withdrawalReqForm"></div>
                        <?php if ($codMinWalletBalance > -1) { ?>
                        <div class="cards-header p-4 pb-0">
                            <p class="note"><?php echo Labels::getLabel('MSG_Minimum_balance_Required_For_COD', $siteLangId).' : '. CommonHelper::displaymoneyformat($codMinWalletBalance);?></p>
                        </div>
                        <?php } ?>
                        <div class="cards-content pt-4 pl-4 pr-4">
                            <div id="credits-info"></div>
                            <div class="gap"></div>
                            <?php //echo $balanceTotalBlocksDisplayed;?>
                            <?php $srchFormDivWidth = $canAddMoneyToWallet ? '8' : '12'; ?>
                            <div class="row">
                                <div class="col-lg-<?php echo $srchFormDivWidth; ?> col-md-<?php echo $srchFormDivWidth; ?> col-md-12">
                                    <div class="replaced">
                                        <h5 class="cards-title mb-2"><?php echo Labels::getLabel('LBL_Search_Transactions', $siteLangId);?></h5>
                                        <?php
                                        $submitFld = $frmSrch->getField('btn_submit');
                                        $submitFld->setFieldTagAttribute('class', 'btn--block btn btn--primary');

                                        $fldClear= $frmSrch->getField('btn_clear');
                                        $fldClear->setFieldTagAttribute('class', 'btn--block btn btn--primary-border');
                                        echo $frmSrch->getFormHtml();
                                        ?>
                                    </div>
                                </div>
                                <?php if ($canAddMoneyToWallet) { ?>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="replaced amount-added-box">
                                            <h5 class="cards-title mb-2">
                                            <?php echo Labels::getLabel('LBL_Enter_amount_to_be_Added'.'_['.CommonHelper::getDefaultCurrencySymbol().']', $siteLangId); ?></h5>
                                            <div id="rechargeWalletDiv" class="cellright nopadding--bottom">
                                                <?php
                                                $frmRechargeWallet->setFormTagAttribute('onSubmit', 'setUpWalletRecharge(this); return false;');
                                                $frmRechargeWallet->setFormTagAttribute('class', 'form');
                                                $frmRechargeWallet->developerTags['colClassPrefix'] = 'col-md-';
                                                $frmRechargeWallet->developerTags['fld_default_col'] = 12;
                                                $frmRechargeWallet->setRequiredStarPosition(Form::FORM_REQUIRED_STAR_WITH_NONE);

                                                $amountFld = $frmRechargeWallet->getField('amount');
                                                $amountFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Enter_amount_to_be_Added', $siteLangId));
                                                $amountFld->developerTags['noCaptionTag'] = true;
                                                $buttonFld = $frmRechargeWallet->getField('btn_submit');
                                                $buttonFld->setFieldTagAttribute('class', 'btn--block block-on-mobile');
                                                $buttonFld->developerTags['noCaptionTag'] = true;
                                                echo $frmRechargeWallet->getFormHtml(); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="gap"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="cards">

                        <div class="cards-content pt-2 pl-4 pr-4 ">
                            <div id="creditListing"><?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?></div>
                            <span class="gap"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
