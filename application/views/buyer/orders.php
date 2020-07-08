<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$frmOrderSrch->setFormTagAttribute('onSubmit', 'searchOrders(this); return false;');
$frmOrderSrch->setFormTagAttribute('class', 'form');
$frmOrderSrch->developerTags['colClassPrefix'] = 'col-md-';
$frmOrderSrch->developerTags['fld_default_col'] = 12;

$keywordFld = $frmOrderSrch->getField('keyword');
$keywordFld->setWrapperAttribute('class', 'col-lg-4');
$keywordFld->developerTags['col'] = 4;
$keywordFld->developerTags['noCaptionTag'] = true;
/* $keywordFld->htmlAfterField = '<small class="text--small">'.Labels::getLabel('LBL_Buyer_account_orders_listing_search_form_keyword_help_txt', $siteLangId).'</small>'; */

$statusFld = $frmOrderSrch->getField('status');
$statusFld->setWrapperAttribute('class', 'col-lg-4');
$statusFld->developerTags['col'] = 4;
$statusFld->developerTags['noCaptionTag'] = true;

$dateFromFld = $frmOrderSrch->getField('date_from');
$dateFromFld->setFieldTagAttribute('class', 'field--calender');
$dateFromFld->setWrapperAttribute('class', 'col-lg-2');
$dateFromFld->developerTags['col'] = 2;
$dateFromFld->developerTags['noCaptionTag'] = true;

$dateToFld = $frmOrderSrch->getField('date_to');
$dateToFld->setFieldTagAttribute('class', 'field--calender');
$dateToFld->setWrapperAttribute('class', 'col-lg-2');
$dateToFld->developerTags['col'] = 2;
$dateToFld->developerTags['noCaptionTag'] = true;

$priceFromFld = $frmOrderSrch->getField('price_from');
$priceFromFld->setWrapperAttribute('class', 'col-lg-2');
$priceFromFld->developerTags['col'] = 2;
$priceFromFld->developerTags['noCaptionTag'] = true;

$priceToFld = $frmOrderSrch->getField('price_to');
$priceToFld->setWrapperAttribute('class', 'col-lg-2');
$priceToFld->developerTags['col'] = 2;
$priceToFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frmOrderSrch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn--block btn btn--primary');
$submitBtnFld->setWrapperAttribute('class', 'col-lg-2');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmOrderSrch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn--block btn btn--primary-border');
$cancelBtnFld->setWrapperAttribute('class', 'col-lg-2');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?> <?php $this->includeTemplate('_partial/buyerDashboardNavigation.php'); ?> <main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header justify-content-between row mb-4">
            <div class="content-header-left col-md-auto">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"> <?php echo Labels::getLabel('LBL_Order_History', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="cards">
                        <div class="cards-content pt-4 pl-4 pr-4 pb-0">
                            <div class="replaced">
                                <?php echo $frmOrderSrch->getFormHtml(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="cards">
                        <div class="cards-content pt-2 pl-4 pr-4 ">
                            <div id="ordersListing"></div>
                            <span class="gap"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
