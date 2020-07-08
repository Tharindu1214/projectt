<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$optionValueFrm->setFormTagAttribute('class', 'form form--horizontal');
$optionValueFrm->setFormTagAttribute('onsubmit', 'setUpOptionValues(this); return(false);');
$optionValueFrm->developerTags['colClassPrefix'] = 'col-md-';
$optionValueFrm->developerTags['fld_default_col'] = 6;
?><div class="box__head">
<h4><?php echo Labels::getLabel('LBL_CONFIGURE_OPTION_VALUES', $langId); ?></h4>
</div>
<div class="box__body">
    <div class="form__subcontent">
        <?php
        echo $optionValueFrm->getFormHtml();
        ?>
    </div>
</div>
