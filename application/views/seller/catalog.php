<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
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
                        <div class="cards-content pt-3 pl-4 pr-4 pb-0">
                            <div class="replaced">
                                <?php
                                $frmSearchCatalogProduct->setFormTagAttribute('id', 'frmSearchCatalogProduct');
                                $frmSearchCatalogProduct->setFormTagAttribute('class', 'form');
                                $frmSearchCatalogProduct->setFormTagAttribute('onsubmit', 'searchCatalogProducts(this); return(false);');
                                $frmSearchCatalogProduct->getField('keyword')->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search_by_keyword/EAN/ISBN/UPC_code', $siteLangId));
                                $frmSearchCatalogProduct->developerTags['colClassPrefix'] = 'col-md-';
                                $frmSearchCatalogProduct->developerTags['fld_default_col'] = 12;

                                $keywordFld = $frmSearchCatalogProduct->getField('keyword');
                                $keywordFld->setFieldTagAttribute('id', 'tour-step-3');
                                $keywordFld->setWrapperAttribute('class', 'col-lg-4');
                                $keywordFld->developerTags['col'] = 4;

                                if (FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT')) {
                                    $dateFromFld = $frmSearchCatalogProduct->getField('type');
                                    $dateFromFld->setFieldTagAttribute('class', '');
                                    $dateFromFld->setWrapperAttribute('class', 'col-lg-2');
                                    $dateFromFld->developerTags['col'] = 2;
                                }
                                $typeFld = $frmSearchCatalogProduct->getField('product_type');
                                $typeFld->setWrapperAttribute('class', 'col-lg-2');
                                $typeFld->developerTags['col'] = 2;

                                $submitFld = $frmSearchCatalogProduct->getField('btn_submit');
                                $submitFld->setFieldTagAttribute('class', 'btn--block btn btn--primary');
                                $submitFld->setWrapperAttribute('class', 'col-lg-2');
                                $submitFld->developerTags['col'] = 2;

                                $fldClear= $frmSearchCatalogProduct->getField('btn_clear');
                                $fldClear->setFieldTagAttribute('onclick', 'clearSearch()');
                                $fldClear->setFieldTagAttribute('class', 'btn--block btn btn--primary-border');
                                $fldClear->setWrapperAttribute('class', 'col-lg-2');
                                $fldClear->developerTags['col'] = 2;
                                    /* if( User::canAddCustomProductAvailableToAllSellers() ){
                                      $submitFld = $frmSearchCatalogProduct->getField('btn_submit');
                                      $submitFld->setFieldTagAttribute('class','btn--block');
                                      $submitFld->developerTags['col'] = 4;
                                    } */
                                echo $frmSearchCatalogProduct->getFormHtml();
                                ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="cards">
                        <div class="cards-content pt-2 pl-4 pr-4 pb-4">
                            <div id="listing"> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    $(document).ready(function(){
    <?php if (!$displayDefaultListing) { ?>
        searchCatalogProducts(document.frmSearchCatalogProduct);
    <?php } ?>
    });

    $(".btn-inline-js").click(function(){
        $(".box-slide-js").slideToggle();
    });
</script>
