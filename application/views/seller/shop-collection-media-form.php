<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $frm->setFormTagAttribute('onsubmit', 'uploadCollectionImage(this); return(false);');
    $frm->developerTags['colClassPrefix'] = 'col-md-';
    $frm->developerTags['fld_default_col'] = 12;
    $fld = $frm->getField('collection_image');
    $fld->addFieldTagAttribute('class', 'btn btn--primary btn--sm');
?>
<div class="col-lg-12 col-md-12">
    <div class="content-header justify-content-between row mb-4">
        <div class="content-header-left col-md-auto"><h5 class="cards-title"><?php echo Labels::getLabel('LBL_Shop_Collections', $siteLangId); ?></h5></div>
        <div class="content-header-right col-auto">
            <div class="form__group">
                <a href="javascript:void(0)" onClick="shopCollections(this)" class="btn btn--primary-border btn--sm"><?php echo Labels::getLabel('LBL_Back_to_Collections', $siteLangId);?></a>
            </div>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="">
        <div class="tabs tabs-sm tabs--scroll clearfix">
            <ul>
                <li ><a onclick="getShopCollectionGeneralForm(<?php echo $scollection_id; ?>);" href="javascript:void(0)"><?php echo Labels::getLabel('TXT_Basic', $siteLangId);?></a></li>
                <?php
                foreach ($language as $lang_id => $langName) {?>
                <li class=""><a href="javascript:void(0)" onClick="editShopCollectionLangForm(<?php echo $scollection_id ?>, <?php echo $lang_id;?>)">
                    <?php echo $langName;?></a></li>
                <?php } ?>
                <li>
                    <a onclick="sellerCollectionProducts(<?php echo $scollection_id ?>)" href="javascript:void(0);"> <?php echo Labels::getLabel('TXT_LINK', $siteLangId);?> </a>
                </li>
                <li class="is-active"><a
                <?php if ($scollection_id > 0) {?>
                    onclick="collectionMediaForm(this, <?php echo $scollection_id; ?>);"
                <?php } ?> href="javascript:void(0);"><?php echo Labels::getLabel('TXT_Media', $siteLangId);?></a></li>
            </ul>
        </div>
    </div>
    <div class="form__subcontent">
        <div class="preview" id="shopFormBlock">
            <small class="text--small"><?php echo sprintf(Labels::getLabel('MSG_Upload_shop_collection_image_text', $siteLangId), '610*343')?></small>
            <?php echo $frm->getFormHtml();?>
               <div id="imageListing" class="row" ></div>
        </div>
    </div>
</div>
