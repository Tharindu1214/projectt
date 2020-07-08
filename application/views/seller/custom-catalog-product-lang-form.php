<div class="tabs tabs--small tabs--scroll clearfix align-items-center">
    <?php require_once(CONF_THEME_PATH.'_partial/seller/customCatalogProductNavigationLinks.php'); ?>
</div>
<div class="cards">
    <div class="cards-content pt-3 pl-4 pr-4 ">
        <div class="row">
            <div class="col-md-12">
                <div class="">
                    <?php
                    //$customProductLangFrm->setFormTagAttribute('onsubmit','setUpCustomSellerProductLang(this); return(false);');
                    $customProductLangFrm->setFormTagAttribute('class', 'form form--horizontal layout--'.$formLayout);
                    $customProductLangFrm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-';
                    $customProductLangFrm->developerTags['fld_default_col'] = 4;

                    $fld = $customProductLangFrm->getField('product_description');
                    $fld->setWrapperAttribute('class', 'col-lg-8');
                    $fld->developerTags['col'] = 8;
                    echo $customProductLangFrm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
