<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $frmSearch->setFormTagAttribute('onSubmit', 'sellerProducts(0,1); return(false);');

    $frmSearch->setFormTagAttribute('class', 'form');
    $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
    $frmSearch->developerTags['fld_default_col'] = 12;

    $keyFld = $frmSearch->getField('keyword');
    $keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
    $keyFld->setWrapperAttribute('class', 'col-lg-6');
    $keyFld->developerTags['col'] = 6;
    $keyFld->developerTags['noCaptionTag'] = true;

    $submitBtnFld = $frmSearch->getField('btn_submit');
    $submitBtnFld->setFieldTagAttribute('class', 'btn--block');
    $submitBtnFld->setWrapperAttribute('class', 'col-lg-3');
    $submitBtnFld->developerTags['col'] = 3;
    $submitBtnFld->developerTags['noCaptionTag'] = true;

    $cancelBtnFld = $frmSearch->getField('btn_clear');
    $cancelBtnFld->setFieldTagAttribute('class', 'btn--block');
    $cancelBtnFld->setWrapperAttribute('class', 'col-lg-3');
    $cancelBtnFld->developerTags['col'] = 3;
    $cancelBtnFld->developerTags['noCaptionTag'] = true; ?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header">
            <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
            <?php $this->includeTemplate('_partial/productPagesTabs.php', array('siteLangId'=>$siteLangId,'controllerName'=>$controllerName,'action'=>$action), false); ?>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="cards">
                        <div class="cards-content pt-4 pl-4 pr-4 pb-0">
                            <div class="replaced">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <?php
                                        $submitFld = $frmSearch->getField('btn_submit');
                                        $submitFld->setFieldTagAttribute('class', 'btn--block btn btn--primary');

                                        $fldClear= $frmSearch->getField('btn_clear');
                                        $fldClear->setFieldTagAttribute('class', 'btn--block btn btn--primary-border');

                                        echo $frmSearch->getFormHtml();
                                        ?>
                                        <?php echo $frmSearch->getExternalJS();?>
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
                        <div class="cards-content pl-4 pr-4 pt-4">
                            <div class="row justify-content-between align-items-center">
                                <div class="col-auto">
                                </div>
                                <div class="col-auto">
                                    <div class="action">
                                        <a class="btn btn--primary btn--sm formActionBtn-js formActions-css" title="<?php echo Labels::getLabel('LBL_Activate', $siteLangId); ?>" onclick="toggleBulkStatues(1)" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Activate', $siteLangId); ?></a>
                                        <a class="btn btn--primary-border btn--sm formActionBtn-js formActions-css" title="<?php echo Labels::getLabel('LBL_Deactivate', $siteLangId); ?>" onclick="toggleBulkStatues(0)" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Deactivate', $siteLangId); ?></a>
                                        <a class="btn btn--primary btn--sm formActionBtn-js formActions-css" title="<?php echo Labels::getLabel('LBL_Delete', $siteLangId); ?>" onclick="deleteBulkSellerProducts()" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Delete', $siteLangId); ?></a>
                                        <a class="btn btn--primary-border btn--sm formActionBtn-js formActions-css" title="<?php echo Labels::getLabel('LBL_Add_Special_Price', $siteLangId); ?>" onclick="addSpecialPrice()" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Add_Special_Price', $siteLangId); ?></a>
                                        <div class="gap"></div>
                                        <a class="btn btn--primary-border btn--sm formActionBtn-js formActions-css" title="<?php echo Labels::getLabel('LBL_Add_Volume_Discount', $siteLangId); ?>" onclick="addVolumeDiscount()" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Add_Volume_Discount', $siteLangId); ?></a>
                                    </div>
                                </div>
                            </div>

                            <div id="listing">
                                <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?>
                            </div>
                            <span class="gap"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php echo FatUtility::createHiddenFormFromData(array('product_id'=>$product_id), array('name' => 'frmSearchSellerProducts'));?>
<script>
    jQuery(document).ready(function($) {
        $(".initTooltip").click(function(){
            $.facebox({ div: '#inventoryToolTip' }, 'catalog-bg');
        });
    });
</script>
