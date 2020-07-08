<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmSrch->setFormTagAttribute('onSubmit', 'searchProductsInventoryStockStatus(this); return false;');
$frmSrch->setFormTagAttribute('class', 'form');
$frmSrch->developerTags['colClassPrefix'] = 'col-md-';
$frmSrch->developerTags['fld_default_col'] = 12;

$keyFld = $frmSrch->getField('keyword');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-6');
$keyFld->developerTags['col'] = 6;
$keyFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frmSrch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn--block');
$submitBtnFld->setWrapperAttribute('class', 'col-lg-3');
$submitBtnFld->developerTags['col'] = 3;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmSrch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn--block');
$cancelBtnFld->setWrapperAttribute('class', 'col-lg-3');
$cancelBtnFld->developerTags['col'] = 3;
$cancelBtnFld->developerTags['noCaptionTag'] = true; ?> <?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?> <main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?> <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Products_Inventory_Stock_Status_Report', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="cards">

                        <div class="cards-content pl-4 pr-4 pt-4 pb-0">
                                <div class="replaced">
                                    <div class="row">
                                        <div class="col-lg-6"> <?php
                                            $submitFld = $frmSrch->getField('btn_submit');
                                            $submitFld->setFieldTagAttribute('class', 'btn--block btn btn--primary');

                                            $fldClear= $frmSrch->getField('btn_clear');
                                            $fldClear->setFieldTagAttribute('class', 'btn--block btn btn--primary-border');
                                            echo $frmSrch->getFormHtml(); ?>
                                        </div>
                                    </div>
                                </div>


                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="cards">

                        <div class="cards-content pt-2 pl-4 pr-4 ">
                            <div id="listingDiv"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                            <div class="gap"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
