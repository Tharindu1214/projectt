<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php ($imageType!='banner')? $count = 1 : ''; foreach ($images as $img) {?>
<div class="<?php echo ($imageType!='banner')? 'col-md-12' : 'col-md-12';?>">
    <div class="profile__pic">
        <img src="<?php echo CommonHelper::generateUrl('Image', $imageFunction, array($img['afile_record_id'], $img['afile_lang_id'], 'PREVIEW', $img['afile_id']));?>" alt="<?php echo Labels::getLabel('LBL_Shop_Banner', $siteLangId);?>">
    </div>
    <small class="text--small"><?php echo $languages[$img['afile_lang_id']];?></small>

        <a class = "btn btn--primary-border btn--sm" href="javascript:void(0);" onClick="removeShopImage(<?php echo $img['afile_id']; ?>,<?php echo $img['afile_lang_id']; ?>,'<?php echo $imageType; ?>',<?php echo $img['afile_screen']; ?>)"><?php echo Labels::getLabel('LBL_Remove', $siteLangId);?></a>

</div>
    <?php if ($imageType != 'banner') {
        if ($count == 2) {
            $count = 1;
            echo "<span class='gap'></span>";
        }
    } else {
        echo "<span class='gap'></span>";
    } ?>
<?php } ?>
