<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="cards-content pl-4 pr-4 ">
    <div class="">
        <div class="tabs tabs-sm tabs--scroll clearfix">
            <ul>
                <li><a href="javascript:void(0)" onClick="addForm(<?php echo $splatform_id;?>);"><?php echo Labels::getLabel('LBL_General', $siteLangId); ?></a></li>
                <?php
                foreach ($languages as $langId => $langName) {?>
                <li class="<?php echo ($splatform_lang_id == $langId)?'is-active':'' ; ?>"><a href="javascript:void(0)" <?php if ($splatform_id>0) {?> onClick="addLangForm(<?php echo $splatform_id;?> , <?php echo $langId;?>);" <?php }?>>
                        <?php echo $langName;?></a></li>
                <?php }?>
            </ul>
        </div>
    </div>
    <div class="form__subcontent">
        <?php
        $langFrm->setFormTagAttribute('onsubmit', 'setupLang(this); return(false);');
        $langFrm->setFormTagAttribute('class', 'form form--horizontal layout--'.$formLayout);
        $langFrm->developerTags['colClassPrefix'] = 'col-lg-8 col-md-8 col-sm-';
        $langFrm->developerTags['fld_default_col'] = 8;
        echo $langFrm->getFormHtml();
        ?>
    </div>
</div>
