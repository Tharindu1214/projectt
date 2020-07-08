<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$mediaFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$mediaFrm->developerTags['colClassPrefix'] = 'col-md-';
$mediaFrm->developerTags['fld_default_col'] = 12;

$fld1 = $mediaFrm->getField('banner_image');
$fld1->addFieldTagAttribute('class', 'btn btn--primary btn--sm');

$langFld = $mediaFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'language-js');

$screenFld = $mediaFrm->getField('banner_screen');
$screenFld->addFieldTagAttribute('class', 'display-js');

if ($blocation_id == BannerLocation::HOME_PAGE_MIDDLE_BANNER) {
    $screenFld->setFieldTagAttribute('disabled', 'disabled');
}

$preferredDimensionsStr = '<span class="uploadimage--info" ></span>';

$htmlAfterField = $preferredDimensionsStr;
$htmlAfterField .= '<div id="image-listing"></div>';
$fld1->htmlAfterField = $htmlAfterField;
?> <section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Banner_Image', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0);" onclick="bannerForm(<?php echo $blocation_id;?>,<?php echo $banner_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
                        <?php if ($banner_id > 0) {
                            foreach ($languages as $langId => $langName) {
                                ?> <li><a href="javascript:void(0);"
                                            onclick="bannerLangForm(<?php echo $blocation_id; ?>,<?php echo $banner_id ?>, <?php echo $langId; ?>);"><?php echo Labels::getLabel('LBL_'.$langName, $adminLangId); ?></a></li> <?php
                            }
                        }
                        ?>
                        <li><a class="active" href="javascript:void(0)" onclick="mediaForm(<?php echo $blocation_id ?>,<?php echo $banner_id ?>);"><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel"> <?php echo $mediaFrm->getFormHtml(); ?> </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    $(document).on('change', '.display-js', function() {
        var deviceType = $(this).val();
        fcom.ajax(fcom.makeUrl('Banners', 'getBannerLocationDimensions', [<?php echo $blocation_id;?>, deviceType]), '', function(t) {
            var ans = $.parseJSON(t);            
            $('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, ans.bannerWidth + ' * ' + ans.bannerHeight));
        });
    });
    $("document").ready(function() {
        $(".display-js").trigger('change');
    });
</script>
