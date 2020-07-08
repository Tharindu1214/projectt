<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$imagesFrm->setFormTagAttribute('class', 'form');
$imagesFrm->setFormTagAttribute('id', 'frmCustomCatalogProductImage');
$imagesFrm->developerTags['colClassPrefix'] = 'col-md-';
$imagesFrm->developerTags['fld_default_col'] = 6;
$optionFld = $imagesFrm->getField('option_id');
$optionFld->addFieldTagAttribute('class', 'option-js');
$langFld = $imagesFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'language-js');
$img_fld = $imagesFrm->getField('prod_image');
/*$img_fld->developerTags['col'] = 12;*/
$img_fld->setFieldTagAttribute('onchange', 'setupCustomCatalogProductImages(); return false;'); ?>
<?php if(!isset($displayLinkNavigation) || (isset($displayLinkNavigation) && ($displayLinkNavigation))) { ?>
<div class="tabs tabs--small tabs--scroll clearfix align-items-center">
    <?php require_once(CONF_THEME_PATH.'_partial/seller/customCatalogProductNavigationLinks.php'); ?>
</div>
<div class="cards">
<?php } ?>
    <div class="cards-content pt-3 pl-4 pr-4 ">
        <div class="row">
            <div class="col-md-12">
                <?php
                echo $imagesFrm->getFormHtml(); ?>
                <div class="col-lg-12 col-md-12">
                    <div id="imageupload_div"></div>
                </div>
            </div>
        </div>
    </div>
<?php if(!isset($displayLinkNavigation) || (isset($displayLinkNavigation) && ($displayLinkNavigation))) { ?>
</div>
<?php } ?>
