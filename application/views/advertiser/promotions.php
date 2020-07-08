<?php  defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/advertiser/advertiserDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
 <div class="content-wrapper content-space">
    <div class="content-header justify-content-between row mb-4">
        <div class="content-header-left col-md-auto">
            <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
            <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Promotions', $siteLangId);?></h2>
        </div>
        <div class="content-header-right col-auto">
            <div class="">
                <a href="javascript:void(0)" onClick="promotionForm()" class="btn btn--primary"><?php echo Labels::getLabel('LBL_Add_Promotion', $siteLangId);?></a>
                <a href="javascript:void(0)" onClick="reloadList()" class="btn btn--primary-border"><?php echo Labels::getLabel('LBL_My_promotions', $siteLangId);?></a>
            </div>
        </div>
    </div>
    <div class="content-body">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="cards">
                    <div class="cards-header p-4">
                        <!-- <h5 class="cards-title "><?php echo Labels::getLabel('LBL_Promotions', $siteLangId);?></h5> -->
                        <p class="note"><?php echo Labels::getLabel('MSG_Minimum_balance_Required_For_Promotions', $siteLangId).' : '. CommonHelper::displaymoneyformat(FatApp::getConfig('CONF_PPC_MIN_WALLET_BALANCE'));?></p>
                    </div>
                    <div class="cards-content pl-4 pr-4 ">
                        <div id="promotionForm">
                            <div class="replaced formshowhide-js">
                                <?php
                                    $frmSearchPromotions->setFormTagAttribute('id', 'frmSearchPromotions');
                                    $frmSearchPromotions->setFormTagAttribute('class', 'form');
                                    $frmSearchPromotions->setFormTagAttribute('onsubmit', 'searchPromotions(this); return(false);');

                                    $frmSearchPromotions->developerTags['colClassPrefix'] = 'col-md-';
                                    $frmSearchPromotions->developerTags['fld_default_col'] = 4;
                                    $frmSearchPromotions->developerTags['noCaptionTag'] = true;

                                    $keywordFld = $frmSearchPromotions->getField('keyword');
                                    $keywordFld->setWrapperAttribute('class', 'col-lg-4');
                                    $keywordFld->developerTags['col'] = 4;
                                    $keywordFld->developerTags['noCaptionTag'] = true;

                                    $statusFld = $frmSearchPromotions->getField('active_promotion');
                                    $statusFld->setWrapperAttribute('class', 'col-lg-2');
                                    $statusFld->developerTags['col'] = 2;
                                    $statusFld->developerTags['noCaptionTag'] = true;

                                    $typeFld = $frmSearchPromotions->getField('type');
                                    $typeFld->setWrapperAttribute('class', 'col-lg-2');
                                    $typeFld->developerTags['col'] = 2;
                                    $typeFld->developerTags['noCaptionTag'] = true;

                                    $dateFromFld = $frmSearchPromotions->getField('date_from');
                                    $dateFromFld->setFieldTagAttribute('class', 'field--calender');
                                    $dateFromFld->setWrapperAttribute('class', 'col-lg-2');
                                    $dateFromFld->developerTags['col'] = 2;
                                    $dateFromFld->developerTags['noCaptionTag'] = true;

                                    $dateToFld = $frmSearchPromotions->getField('date_to');
                                    $dateToFld->setFieldTagAttribute('class', 'field--calender');
                                    $dateToFld->setWrapperAttribute('class', 'col-lg-2');
                                    $dateToFld->developerTags['col'] = 2;
                                    $dateToFld->developerTags['noCaptionTag'] = true;

                                    $submitBtnFld = $frmSearchPromotions->getField('btn_submit');
                                    $submitBtnFld->setFieldTagAttribute('class', 'btn--block btn btn--primary');
                                    $submitBtnFld->setWrapperAttribute('class', 'col-lg-2');
                                    $submitBtnFld->developerTags['col'] = 2;
                                    $submitBtnFld->developerTags['noCaptionTag'] = true;

                                    $cancelBtnFld = $frmSearchPromotions->getField('btn_clear');
                                    $cancelBtnFld->setFieldTagAttribute('class', 'btn btn--block btn--primary-border');
                                    $cancelBtnFld->setWrapperAttribute('class', 'col-lg-2');
                                    $cancelBtnFld->developerTags['col'] = 2;
                                    $cancelBtnFld->developerTags['noCaptionTag'] = true;
                                    echo $frmSearchPromotions->getFormHtml();
                                ?>
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
                        <div id="listing">
                            <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 </div>
</main>
