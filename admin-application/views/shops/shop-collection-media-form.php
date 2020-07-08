<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $frm->setFormTagAttribute('onsubmit', 'uploadCollectionImage(this); return(false);');
    $frm->developerTags['colClassPrefix'] = 'col-md-';
    $frm->developerTags['fld_default_col'] = 6;
    $fld = $frm->getField('collection_image');
    $fld->addFieldTagAttribute('class', 'btn btn--primary btn--sm');
    if (isset($scollection_id) && $scollection_id > 0) {
        $scollection_id = $scollection_id;
    } else {
        $scollection_id = 0;
    } ?>
<ul class="tabs_nav tabs_nav--internal">
    <li><a onclick="getShopCollectionGeneralForm(<?php echo $shop_id; ?>, <?php echo $scollection_id; ?>);" href="javascript:void(0)"><?php echo Labels::getLabel('TXT_GENERAL_media', $adminLangId);?></a></li>
    <?php foreach ($language as $lang_id => $langName) { ?>
    <li><a href="javascript:void(0)" onClick="editShopCollectionLangForm(<?php echo $shop_id;?>, <?php echo $scollection_id ?>, <?php echo $lang_id;?>)">
        <?php echo Labels::getLabel('LBL_'.$langName, $adminLangId);?></a></li>
    <?php } ?>

    <li><a onclick="sellerCollectionProducts(<?php echo $scollection_id; ?>,<?php echo $shop_id; ?>);" href="javascript:void(0);"><?php echo Labels::getLabel('TXT_LINK', $adminLangId);?></a></li>
    <li>
        <a class="active" onclick="collectionMediaForm(<?php echo $shop_id; ?>,<?php echo $scollection_id ?>)" href="javascript:void(0);"> <?php echo Labels::getLabel('TXT_MEDIA', $adminLangId);?> </a>
    </li>
</ul>
<div class="tabs_panel_wrap">
    <div class="form__subcontent">
        <div class="preview" id="shopFormBlock">
            <small class="text--small"><?php echo sprintf(Labels::getLabel('MSG_Upload_shop_collection_image_text', $adminLangId), '610*343')?></small>
            <?php echo $frm->getFormHtml();?>
               <div id="imageListing" class="row" ></div>
        </div>
    </div>
</div>
