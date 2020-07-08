<?php  defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="tabs tabs--small tabs--scroll clearfix">
    <ul class="arrowTabs">
        <li class="<?php echo !empty($action) && $action=='shopForm'?'is-active' : '';?>"><a href="javascript:void(0)" onClick="shopForm()"><?php echo Labels::getLabel('LBL_General', $siteLangId); ?></a></li>
        <?php
        $inactive=($shop_id==0)?'fat-inactive':'';
        foreach ($language as $langId => $langName) {?>
        <li class="<?php echo !empty($formLangId) && $formLangId == $langId ? 'is-active' : '';
        echo $inactive; ?>"><a href="javascript:void(0)" <?php if ($shop_id > 0) { ?>
             onClick="shopLangForm(<?php echo $shop_id ?>, <?php echo $langId;?>)"
        <?php } ?>> <?php echo $langName;?></a></li>
        <?php }?>
        <li class="<?php if ((!empty($action) && ($action=='returnAddressForm' || $action=='returnAddressLangForm' ))) {
             echo 'is-active';
                   } ?>"><a href="javascript:void(0);"
                onClick="returnAddressForm()"><?php echo Labels::getLabel('LBL_Return_Address', $siteLangId);?></a></li>
        <?php /* <li class="<?php echo !empty($action) && ($action=='shopTemplate' || $action=='shopThemeColor')?'is-active' : ''; echo $inactive?>"><a href="javascript:void(0)" <?php if($shop_id>0){?> onClick="shopTemplates(this)"
            <?php }?>><?php echo Labels::getLabel('LBL_Layout',$siteLangId); ?></a></li> */ ?>
        <li class="<?php echo !empty($action) && $action=='shopMediaForm'?'is-active' : ''; echo $inactive?>"><a href="javascript:void(0)"
            <?php if ($shop_id > 0) { ?>
            onClick="shopMediaForm(this)"
            <?php } ?>> <?php echo Labels::getLabel('LBL_Media', $siteLangId); ?></a></li>
        <li class="<?php echo !empty($action) && ($action=='shopCollections') ? 'is-active' : ''; ?>"><a href="javascript:void(0)"
                <?php if ($shop_id > 0) { ?>
                    onClick="shopCollections(this)"
                <?php } ?>><?php echo Labels::getLabel('LBL_COLLECTIONS', $siteLangId); ?></a></li>
    </ul>
</div>
