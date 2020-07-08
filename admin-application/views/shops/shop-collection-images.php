<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($images)) { ?>
    <div class="logoWrap">
    <div class="logothumb"> <img src="<?php echo CommonHelper::generateUrl('Image', 'shopCollectionImage', array($images['afile_record_id'], $images['afile_lang_id'], 'THUMB'), CONF_WEBROOT_FRONT_URL);?>" alt="<?php echo Labels::getLabel('LBL_Collection_Image', $adminLangId);?>">
        <small><strong>Default Image</strong></small>
        <a class="deleteLink white" href="javascript:void(0);" title="Delete <?php echo $images['afile_name'];?>" onClick="removeCollectionImage(<?php echo $shop_id; ?>,<?php echo $scollection_id; ?>,<?php echo $lang_id; ?>)" class="delete"><i class="ion-close-round"></i></a>
    </div>
    <small class=""><strong> <?php echo Labels::getLabel('LBL_Language', $adminLangId); ?>:</strong> <?php echo $languages[$images['afile_lang_id']];?></small> </div>
<?php } ?>
