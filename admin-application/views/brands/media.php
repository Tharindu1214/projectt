<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$brandLogoFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$brandLogoFrm->developerTags['colClassPrefix'] = 'col-md-';
$brandLogoFrm->developerTags['fld_default_col'] = 12;
$logoFld = $brandLogoFrm->getField('logo');
$logoFld->addFieldTagAttribute('class', 'btn btn--primary btn--sm');
$idFld = $brandLogoFrm->getField('brand_id');
$idFld->addFieldTagAttribute('id', 'id-js');
$logoLangFld = $brandLogoFrm->getField('lang_id');
$logoLangFld->addFieldTagAttribute('class', 'logo-language-js');
$logoPreferredDimensions = '<small class="text--small">'.sprintf(Labels::getLabel('LBL_Preferred_Dimensions', $adminLangId), '500*500').'</small>';
$htmlAfterField = $logoPreferredDimensions;
$htmlAfterField .= '<div id="logo-listing"></div>';
$logoFld->htmlAfterField = $htmlAfterField;

$brandImageFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$brandImageFrm->developerTags['colClassPrefix'] = 'col-md-';
$brandImageFrm->developerTags['fld_default_col'] = 12;
$imageFld = $brandImageFrm->getField('image');
$imageFld->addFieldTagAttribute('class', 'btn btn--primary btn--sm');
$idFld = $brandImageFrm->getField('brand_id');
$idFld->addFieldTagAttribute('id', 'id-js');
$imageLangFld = $brandImageFrm->getField('lang_id');
$imageLangFld->addFieldTagAttribute('class', 'image-language-js');
$screenFld = $brandImageFrm->getField('slide_screen');
$screenFld->addFieldTagAttribute('class', 'prefDimensions-js');

$htmlAfterField = '<div style="margin-top:15px;" class="preferredDimensions-js">'.sprintf(Labels::getLabel('LBL_Preferred_Dimensions_%s',$adminLangId),'2000 x 500').'</div>';
$htmlAfterField .= '<div id="image-listing"></div>';
$imageFld->htmlAfterField = $htmlAfterField;

/*$ImagePreferredDimensions = '<small class="text--small">'.sprintf(Labels::getLabel('LBL_Preferred_Dimensions', $adminLangId), '2000*500').'<br/>'. Labels::getLabel('LBL_This_image_will_be_displayed_for_homepage_brands_collection', $adminLangId) .'</small>';
$htmlAfterField = $ImagePreferredDimensions;
$htmlAfterField .= '<div id="image-listing"></div>';
$imageFld->htmlAfterField = $htmlAfterField;*/
?><section class="section">
    <div class="sectionhead">

        <h4><?php echo Labels::getLabel('LBL_Product_Brand_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0)" onclick="brandForm(<?php echo $brand_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
                        <?php $inactive = ($brand_id == 0) ? 'fat-inactive' : '';
                        foreach ($languages as $langId => $langName) { ?>
                        <li class="<?php echo $inactive;?>"><a href="javascript:void(0);"
                            <?php if ($brand_id > 0) { ?>
                                onclick="brandLangForm(<?php echo $brand_id ?>, <?php echo $langId;?>);"
                            <?php }?>>
                            <?php echo labels::getLabel("LBL_".$langName, $adminLangId);?></a></li>
                        <?php } ?>
                        <li><a class="active" href="javascript:void(0)" onclick="brandMediaForm(<?php echo $brand_id ?>);"><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <section class="">
                                <?php echo $brandLogoFrm->getFormHtml(); ?>
                            </section>
                            <section class="">
                                <?php echo $brandImageFrm->getFormHtml(); ?>
                            </section>
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
