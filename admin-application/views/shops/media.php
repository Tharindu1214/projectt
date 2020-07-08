<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$shopLogoFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$shopLogoFrm->developerTags['colClassPrefix'] = 'col-md-';
$shopLogoFrm->developerTags['fld_default_col'] = 12;
$fld = $shopLogoFrm->getField('shop_logo');
$fld->addFieldTagAttribute('class', 'btn btn--primary btn--sm');
$langFld = $shopLogoFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'logo-language-js');

$preferredDimensionsStr = '<span class="gap"></span><small class="text--small">'. sprintf(Labels::getLabel('MSG_Upload_shop_logo_text', $adminLangId), '150*150'). '</small>';

$htmlAfterField = $preferredDimensionsStr;
$htmlAfterField .= '<div id="logo-image-listing"></div>';
$fld->htmlAfterField = $htmlAfterField;

$shopBannerFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$shopBannerFrm->developerTags['colClassPrefix'] = 'col-md-';
$shopBannerFrm->developerTags['fld_default_col'] = 12;
$fld1 = $shopBannerFrm->getField('shop_banner');
$fld1->addFieldTagAttribute('class', 'btn btn--primary btn--sm');
$langFld = $shopBannerFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'banner-language-js');
$screenFld = $shopBannerFrm->getField('slide_screen');
$screenFld->addFieldTagAttribute('class', 'prefDimensions-js');

$htmlAfterField = '<div style="margin-top:15px;" class="preferredDimensions-js">'.sprintf(Labels::getLabel('LBL_Preferred_Dimensions_%s',$adminLangId),'2000 x 500').'</div>';
$htmlAfterField .= '<div id="banner-image-listing"></div>';
$fld1->htmlAfterField = $htmlAfterField;
/*$bannerSize = applicationConstants::getShopBannerSize();
$shopLayout= ($shopDetails['shop_ltemplate_id'])?$shopDetails['shop_ltemplate_id']:SHOP::TEMPLATE_ONE;
$preferredDimensionsStr = '<span class="gap"></span><small class="text--small">'. sprintf(Labels::getLabel('MSG_Upload_shop_banner_text', $adminLangId), $bannerSize[$shopLayout]). '</small>';

$htmlAfterField = $preferredDimensionsStr;
$htmlAfterField .= '<div id="banner-image-listing"></div>';
$fld1->htmlAfterField = $htmlAfterField;*/

/*$shopBackgroundImageFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$shopBackgroundImageFrm->developerTags['colClassPrefix'] = 'col-md-';
$shopBackgroundImageFrm->developerTags['fld_default_col'] = 12;
$fld1 = $shopBackgroundImageFrm->getField('shop_background_image');
$fld1->addFieldTagAttribute('class', 'btn btn--primary btn--sm');
$langFld = $shopBackgroundImageFrm->getField('lang_id');
$preferredDimensionsStr = '<span class="gap"></span><small class="text--small">'. Labels::getLabel('MSG_Upload_shop_background_text', $adminLangId). '</small>';
$htmlAfterField = $preferredDimensionsStr;
$htmlAfterField .= '<div id="bg-image-listing"></div>';
$fld1->htmlAfterField = $htmlAfterField; */ ?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Shop_Media_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li>
                            <a href="javascript:void(0)" onclick="shopForm(<?php echo $shop_id ?>);">
                                <?php echo Labels::getLabel('LBL_General', $adminLangId); ?>
                            </a>
                        </li>
                        <?php $inactive=($shop_id==0)?'fat-inactive':'';
                        foreach ($languages as $langId => $langName) { ?>
                            <li class="<?php echo $inactive;?>"><a href="javascript:void(0);"
                            <?php if ($shop_id > 0) { ?>
                                onclick="addShopLangForm(<?php echo $shop_id ?>, <?php echo $langId;?>);"
                            <?php }?>><?php echo Labels::getLabel('LBL_'.$langName, $adminLangId);?></a></li>
                        <?php } ?>
                        <?php /* <li><a href="javascript:void(0);"
                            <?php if ($shop_id > 0) { ?>
                                onclick="shopTemplates(<?php echo $shop_id ?>);"
                            <?php }?>><?php echo Labels::getLabel('LBL_Templates', $adminLangId); ?></a></li> */ ?>
                        <li><a class="active" href="javascript:void(0);"
                            <?php if ($shop_id > 0) { ?>
                                onclick="shopMediaForm(<?php echo $shop_id ?>);"
                            <?php }?>><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                        <li><a href="javascript:void(0);"
                            <?php if ($shop_id > 0) { ?>
                                onclick="shopCollections(<?php echo $shop_id ?>);"
                            <?php }?>><?php echo Labels::getLabel('LBL_Collection', $adminLangId); ?></a></li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <?php  echo $shopLogoFrm->getFormHtml();?>
                            <?php echo $shopBannerFrm->getFormHtml();?>
                            <?php /*echo $shopBackgroundImageFrm->getFormHtml();*/ ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>
<script>
    $(document).on('change','.prefDimensions-js',function(){
        var screenDesktop = <?php echo applicationConstants::SCREEN_DESKTOP ?>;
        var screenIpad = <?php echo applicationConstants::SCREEN_IPAD ?>;

        if($(this).val() == screenDesktop)
        {
            $('.preferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, '2000 x 500'));
        }
        else if($(this).val() == screenIpad)
        {
            $('.preferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, '1024 x 360'));
        }
        else{
            $('.preferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, '640 x 360'));
        }
    });
</script>
