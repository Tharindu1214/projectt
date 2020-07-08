<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$brandReqLangFrm->setFormTagAttribute('class', 'form form--horizontal layout--'.$formLayout);
$brandReqLangFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$brandReqLangFrm->developerTags['fld_default_col'] = 12;
$brandReqLangFrm->setFormTagAttribute('onsubmit', 'setupBrandReqLang(this); return(false);');
$brandFld = $brandReqLangFrm->getField('brand_name');
$brandFld->setFieldTagAttribute('onblur', 'checkUniqueBrandName(this,$("input[name=lang_id]").val(),'.$brandReqId.')');
?>
<div class="box__head">
    <h4><?php echo Labels::getLabel('LBL_Request_New_Brand', $siteLangId); ?></h4>
</div>

<div class="box__body">
    <div class="tabs tabs--small tabs--scroll clearfix">
        <ul>
            <li><a href="javascript:void(0)" onclick="addBrandReqForm(<?php echo $brandReqId ?>);"><?php echo Labels::getLabel('LBL_Basic', $siteLangId);?></a></li>
            <?php
            $inactive=($brandReqId==0)?'fat-inactive':'';
            foreach ($languages as $langId => $langName) { ?>
                <li class="<?php echo $inactive;?> <?php echo ($langId == $brandReqLangId) ? 'is-active' : ''; ?>"><a href="javascript:void(0);"
                <?php if ($brandReqId > 0) { ?>
                    onclick="addBrandReqLangForm(<?php echo $brandReqId ?>, <?php echo $langId;?>);"
                <?php }?>><?php echo $langName;?></a></li>
            <?php } ?>
            <li  class="<?php echo $inactive;?>" ><a href="javascript:void(0)"
                <?php if ($brandReqId > 0) {?>
                    onclick="brandMediaForm(<?php echo $brandReqId ?>);"
                <?php } ?>><?php echo Labels::getLabel('LBL_Media', $siteLangId); ?></a></li>
        </ul>
    </div>
    <div class="tabs__content form">
        <?php
        echo $brandReqLangFrm->getFormHtml();
        ?>
    </div>
</div>
