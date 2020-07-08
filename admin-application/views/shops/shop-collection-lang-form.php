<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (isset($scollection_id) && $scollection_id > 0) {
    $scollection_id = $scollection_id;
} else {
    $scollection_id = 0;
}?>
<ul class="tabs_nav tabs_nav--internal">
    <li>
        <a onclick="getShopCollectionGeneralForm(<?php echo $shop_id; ?>, <?php echo $scollection_id; ?>);" href="javascript:void(0)">
            <?php echo Labels::getLabel('TXT_GENERAL_lang', $adminLangId);?>
        </a>
    </li>
    <?php foreach ($language as $lang_id => $langName) {?>
    <li>
        <a class="<?php echo ($langId == $lang_id)?'active':''?>" href="javascript:void(0)" onClick="editShopCollectionLangForm(<?php echo $shop_id;?>, <?php echo $scollection_id ?>, <?php echo $lang_id;?>)">
            <?php echo Labels::getLabel('LBL_'.$langName, $adminLangId);?>
        </a>
    </li>
    <?php } ?>
    <li>
        <a onclick="sellerCollectionProducts(<?php echo $scollection_id ?>,<?php echo $shop_id; ?>)" href="javascript:void(0);">
            <?php echo Labels::getLabel('TXT_LINK', $adminLangId);?>
        </a>
    </li>
    <li> 
        <a onclick="collectionMediaForm(<?php echo $shop_id; ?>,<?php echo $scollection_id ?>)" href="javascript:void(0);"> <?php echo Labels::getLabel('TXT_MEDIA', $adminLangId);?> </a>
    </li>
</ul>
<div class="tabs_panel_wrap">
    <div class="form__subcontent">
        <?php
            $shopColLangFrm->setFormTagAttribute('class', 'form form_horizontal web_form layout--'.$formLayout);
            $shopColLangFrm->setFormTagAttribute('onsubmit', 'setupShopCollectionlangForm(this); return(false);');
             $shopColLangFrm->developerTags['colClassPrefix'] = 'col-md-';
            $shopColLangFrm->developerTags['fld_default_col'] = 12;
            echo $shopColLangFrm->getFormHtml(); ?>
    </div>
</div>
