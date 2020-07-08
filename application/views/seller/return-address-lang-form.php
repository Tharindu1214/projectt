<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'returnAddressLangFrm');
$frm->setFormTagAttribute('class', 'form form--horizontal layout--'.$formLayout);
$frm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-';
$frm->developerTags['fld_default_col'] = 6;
$frm->setFormTagAttribute('onsubmit', 'setReturnAddressLang(this); return(false);');
?>
<?php
$variables= array('language'=>$language,'siteLangId'=>$siteLangId,'shop_id'=>$shop_id,'action'=>$action);
$this->includeTemplate('seller/_partial/shop-navigation.php', $variables, false); ?>
<div class="cards">
    <div class="cards-content pt-3 pl-4 pr-4 ">
        <div class="tabs__content">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="">
                        <div class="tabs tabs-sm tabs--scroll clearfix">
                            <ul class="setactive-js">
                                <li><a href="javascript:void(0)" onClick="returnAddressForm()"><?php echo Labels::getLabel('LBL_General', $siteLangId); ?></a></li>
                                <?php foreach ($language as $langId => $langName) {?>
                                <li <?php echo ($formLangId == $langId)?'class="is-active"':'';?>><a href="javascript:void(0);" onclick="returnAddressLangForm(<?php echo $langId;?>);"><?php echo $langName;?></a></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <?php echo $frm->getFormHtml();?>
                </div>
            </div>
        </div>
    </div>
</div>
